<?php

namespace App\Livewire\Admin\TestChatbot;

use App\Enums\CefrLevel;
use App\Models\ThematicTopic;
use App\Models\User;
use App\Repositories\UnitRepository;
use App\Services\SessionService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Chatbot uji coba mesin adaptive leveling & thematic topics
 * tanpa perlu membuka aplikasi Android. Memakai SessionService yang sama
 * dengan API /sessions (start -> evaluate-turn -> verify-repetition -> complete).
 *
 * Mode IELTS_SPEAKING memakai kurikulum baru: admin memilih unit -> lesson
 * terlebih dahulu, lalu sesi dimulai dengan soal-soal dari lesson tersebut.
 */
class Index extends Component
{
    public string $mode = 'ADAPTIVE';

    public ?int $selectedUserId = null;

    public string $selectedLevel = 'A1';

    public ?int $selectedTopicId = null;

    public ?int $expandedUnitId = null;

    public array $messages = [];

    public ?string $sessionId = null;

    public ?array $currentQuestion = null;

    public string $step = 'idle';

    public string $transcript = '';

    public string $repetition = '';

    public ?string $expectedRepetition = null;

    public ?int $currentTurn = null;

    public ?array $summary = null;

    public bool $restoreLevelAfterTest = true;

    public ?string $originalLevel = null;

    private ?SessionService $service = null;

    public function boot(): void
    {
        $this->service = app(SessionService::class);
    }

    public function mount(): void
    {
        $this->selectedUserId = auth()->id();
        $this->selectedLevel = $this->currentUser()->current_cefr_level->value ?? 'A1';
    }

    #[Computed]
    public function levels(): array
    {
        return CefrLevel::cases();
    }

    #[Computed]
    public function users(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()->orderBy('name')->get();
    }

    #[Computed]
    public function topics(): \Illuminate\Database\Eloquent\Collection
    {
        return ThematicTopic::query()
            ->where('is_active', true)
            ->orderBy('topic_name')
            ->get();
    }

    #[Computed]
    public function ieltsUnits(): \Illuminate\Support\Collection
    {
        return app(UnitRepository::class)->curriculum();
    }

    public function setMode(string $mode): void
    {
        $this->restoreTemporaryLevel();

        $this->mode = in_array($mode, ['THEMATIC', 'IELTS_SPEAKING', 'TOEFL_IBT'], true)
            ? $mode
            : 'ADAPTIVE';

        $this->resetSessionState();
    }

    public function selectUser(int $userId): void
    {
        $this->restoreTemporaryLevel();
        $this->selectedUserId = $userId;
        $this->resetSessionState();
    }

    public function selectTopic(int $topicId): void
    {
        $this->restoreTemporaryLevel();
        $this->selectedTopicId = $topicId;
        $this->resetSessionState();
    }

    public function setLevel(string $level): void
    {
        $this->restoreTemporaryLevel();

        if (in_array($level, array_map(fn ($l) => $l->value, CefrLevel::cases()), true)) {
            $this->selectedLevel = $level;
        }

        $this->resetSessionState();
    }

    public function startSession(): void
    {
        // Mode IELTS_SPEAKING: pilih unit & lesson dulu dari kurikulum.
        if ($this->mode === 'IELTS_SPEAKING') {
            $this->openIeltsMenu();

            return;
        }

        $user = $this->resolveUser();
        $this->resetSessionState();

        try {
            $this->originalLevel = null;

            // Mode THEMATIC: wajib topik terpilih; default ke topik aktif pertama.
            if ($this->mode === 'THEMATIC' && $this->selectedTopicId === null) {
                $this->selectedTopicId = ThematicTopic::query()
                    ->where('is_active', true)
                    ->orderBy('topic_name')
                    ->value('id');
            }

            // Mode ADAPTIVE: set sementara level user ke level terpilih.
            if ($this->mode === 'ADAPTIVE' && $user->current_cefr_level->value !== $this->selectedLevel) {
                $this->originalLevel = $user->current_cefr_level->value;
                $user->update(['current_cefr_level' => $this->selectedLevel]);
                $user->refresh();
            }

            $payload = $this->service->startSession(
                $user,
                $this->mode,
                $this->mode === 'THEMATIC' ? $this->selectedTopicId : null,
            );

            $this->beginSession($payload);
        } catch (\Throwable $e) {
            $this->pushMessage('system', $e->getMessage());
        }
    }

    public function openIeltsMenu(): void
    {
        $this->restoreTemporaryLevel();
        $this->resetSessionState();
        $this->expandedUnitId = null;
        $this->step = 'selecting_lessons';
    }

    public function toggleUnit(int $unitId): void
    {
        $this->expandedUnitId = $this->expandedUnitId === $unitId ? null : $unitId;
    }

    public function startIeltsLesson(int $lessonId): void
    {
        $user = $this->resolveUser();
        $this->resetSessionState();

        try {
            $payload = $this->service->startSession($user, 'IELTS_SPEAKING', null, $lessonId);
            $this->beginSession($payload);
        } catch (\Throwable $e) {
            $this->pushMessage('system', $e->getMessage());
        }
    }

    private function beginSession(array $payload): void
    {
        $this->sessionId = $payload['session_id'];
        $this->currentQuestion = $payload['first_question'];
        $this->currentTurn = $payload['first_question']['turn_number'];
        $this->step = 'awaiting_answer';
        $this->summary = null;

        $part = $payload['first_question']['part_number'] ?? null;
        $label = 'Pertanyaan '.$payload['first_question']['turn_number'];
        if ($this->mode !== 'IELTS_SPEAKING') {
            $label .= ' (Level '.$payload['current_cefr_level'].')';
        }
        if ($part !== null) {
            $label .= ' • Part '.$part;
        }

        $this->pushMessage('ai', $label, [
            'text' => $payload['first_question']['question_text'],
            'key_point' => $payload['first_question']['key_point'] ?? null,
            'topic' => $payload['first_question']['topic_category'] ?? null,
            'level' => $this->mode !== 'IELTS_SPEAKING' ? ($payload['first_question']['cefr_level'] ?? $payload['current_cefr_level']) : null,
            'part' => $part,
        ]);
    }

    public function submitAnswer(): void
    {
        if ($this->step !== 'awaiting_answer' || ! $this->sessionId || ! $this->currentQuestion) {
            return;
        }

        $user = $this->resolveUser();

        if (trim($this->transcript) === '') {
            $this->pushMessage('system', 'Tulis jawaban transkrip dulu.');

            return;
        }

        try {
            $payload = $this->service->evaluateTurn($user, [
                'session_id' => $this->sessionId,
                'turn_number' => $this->currentTurn,
                'question_id' => $this->currentQuestion['question_id'],
                'user_transcript' => $this->transcript,
            ]);

            $this->pushMessage('user', $this->transcript);
            $this->transcript = '';

            $this->pushScoreMessage($payload);

            // Mode IELTS curriculum: feedback langsung, selalu lanjut ke soal berikutnya.
            if ($payload['has_error'] && ! ($payload['advance_on_error'] ?? false)) {
                $this->step = 'awaiting_repetition';
                $this->expectedRepetition = $payload['expected_repetition_text'] ?? null;
                $this->pushMessage('ai', 'Belum tepat. Ulangi kalimat berikut:', [
                    'text' => $this->expectedRepetition,
                    'repetition' => true,
                ]);

                return;
            }

            $this->advanceToNext($payload);
        } catch (\Throwable $e) {
            $this->pushMessage('system', $e->getMessage());
        }
    }

    public function submitRepetition(): void
    {
        if ($this->step !== 'awaiting_repetition' || ! $this->sessionId) {
            return;
        }

        $user = $this->resolveUser();

        if (trim($this->repetition) === '') {
            $this->pushMessage('system', 'Ulangi kalimat yang diminta.');

            return;
        }

        try {
            $payload = $this->service->verifyRepetition($user, [
                'session_id' => $this->sessionId,
                'turn_number' => $this->currentTurn,
                'user_repetition_transcript' => $this->repetition,
            ]);

            $this->pushMessage('user', $this->repetition);
            $this->repetition = '';
            $this->expectedRepetition = null;

            $status = $payload['repetition_success'] ? '✔ Pengulangan benar' : '✘ Pengulangan kurang tepat, lanjut';
            $this->pushMessage('ai', $status, [
                'text' => $payload['ai_transition_speech'] ?? '',
                'small' => true,
            ]);

            $this->advanceToNext($payload);
        } catch (\Throwable $e) {
            $this->pushMessage('system', $e->getMessage());
        }
    }

    public function completeSession(): void
    {
        if (! $this->sessionId || $this->step === 'completed') {
            return;
        }

        $user = $this->resolveUser();

        try {
            $this->summary = $this->service->completeSession($user, $this->sessionId);
            $this->step = 'completed';
            $this->pushMessage('ai', 'Sesi selesai.', [
                'summary' => true,
            ]);

            $this->restoreLevelIfNeeded($user);
        } catch (\Throwable $e) {
            $this->pushMessage('system', $e->getMessage());
        }
    }

    public function resetAll(): void
    {
        if ($this->originalLevel && $this->selectedUserId) {
            $this->restoreLevelIfNeeded($this->resolveUser());
        }

        $this->resetSessionState();
        $this->reset('summary');
    }

    public function pushScoreMessage(array $payload): void
    {
        $scores = $payload['scores'] ?? [];

        if (! empty($payload['ielts_curriculum'])) {
            $keyPoint = $scores['key_point'] ?? $scores['key_point_target'] ?? '';
            $lines = [
                'Key Point: '.($scores['key_point_detected'] ? 'Terpenuhi' : 'Belum'),
                'Skor Kurikulum: '.($scores['score'] ?? 0).'/100',
            ];
            if (! empty($scores['grammar_feedback'])) {
                $lines[] = 'Grammar: '.$scores['grammar_feedback'];
            }
            if (! empty($scores['vocabulary_feedback'])) {
                $lines[] = 'Lexical: '.$scores['vocabulary_feedback'];
            }

            $this->pushMessage('ai', 'Evaluasi kurikulum turn '.$this->currentTurn, [
                'score' => $lines,
                'small' => true,
                'key_point_target' => $keyPoint,
                'suggested_answer' => $scores['suggested_answer'] ?? '',
            ]);

            return;
        }

        $lines = [
            'Word Count: '.($scores['word_count_score'] ?? 0).'/1',
            'Grammar: '.($scores['grammar_score'] ?? 0).'/1',
            'Total Turn: '.($scores['total_turn_score'] ?? 0).'/2',
        ];

        $this->pushMessage('ai', 'Hasil evaluasi turn '.$this->currentTurn, [
            'score' => $lines,
            'small' => true,
        ]);
    }

    public function advanceToNext(array $payload): void
    {
        $next = $payload['next_question'] ?? null;

        if ($next) {
            $this->currentQuestion = $next;
            $this->currentTurn = $next['turn_number'];
            $this->step = 'awaiting_answer';

            $part = $next['part_number'] ?? null;
            $label = 'Pertanyaan '.$next['turn_number'];
            if ($part !== null) {
                $label .= ' • Part '.$part;
            }

            $this->pushMessage('ai', $label, [
                'text' => $next['question_text'],
                'key_point' => $next['key_point'] ?? null,
                'topic' => $next['topic_category'] ?? null,
                'level' => $this->mode !== 'IELTS_SPEAKING' ? ($next['cefr_level'] ?? null) : null,
                'part' => $part,
            ]);

            return;
        }

        $this->currentQuestion = null;
        $this->step = 'awaiting_complete';
        $this->pushMessage('ai', 'Semua turn selesai. Klik "Selesaikan Sesi".', [
            'text' => 'Sesi dapat diselesaikan dan dilihat hasil diagnosa promosi.',
            'small' => true,
        ]);
    }

    private function restoreTemporaryLevel(): void
    {
        if ($this->originalLevel && $this->selectedUserId) {
            $this->restoreLevelIfNeeded($this->resolveUser());
        }
    }

    private function resolveUser(): User
    {
        return User::findOrFail($this->selectedUserId);
    }

    private function currentUser(): User
    {
        return auth()->user();
    }

    private function restoreLevelIfNeeded(User $user): void
    {
        if ($this->restoreLevelAfterTest && $this->originalLevel && $user->current_cefr_level->value !== $this->originalLevel) {
            $user->update(['current_cefr_level' => $this->originalLevel]);
        }

        $this->originalLevel = null;
    }

    private function resetSessionState(): void
    {
        $this->messages = [];
        $this->sessionId = null;
        $this->currentQuestion = null;
        $this->currentTurn = null;
        $this->expectedRepetition = null;
        $this->summary = null;
        $this->transcript = '';
        $this->repetition = '';
        $this->originalLevel = null;
        $this->expandedUnitId = null;
        $this->step = 'idle';
        $this->resetValidation();
    }

    private function pushMessage(string $role, string $label, array $meta = []): void
    {
        $this->messages[] = array_merge(['role' => $role, 'label' => $label], $meta);
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        return view('livewire.admin.test-chatbot.index')
            ->layout('layouts.app', ['title' => 'Test Chatbot']);
    }
}
