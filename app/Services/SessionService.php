<?php

namespace App\Services;

use App\Enums\CefrLevel;
use App\Enums\SessionMode;
use App\Enums\SessionStepState;
use App\Exceptions\NoQuestionAvailableException;
use App\Exceptions\PaywallRequiredException;
use App\Exceptions\RepetitionNotPendingException;
use App\Exceptions\SessionNotFoundException;
use App\Models\ConversationSession;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\ThematicQuestion;
use App\Models\User;
use App\Repositories\ConversationLogRepository;
use App\Repositories\QuestionBankRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SessionRepository;
use App\Repositories\ThematicQuestionRepository;
use App\Repositories\UserRepository;
use App\Services\Engine\AdaptiveLevelingEngine;
use App\Services\Engine\QuotaService;
use App\Services\Engine\RepetitionService;
use App\Services\LessonEvaluator;

/**
 * Orkestrator alur latihan speaking (ADAPTIVE / THEMATIC):
 * start -> evaluate-turn -> (verify-repetition)* -> complete.
 * Menerapkan 4-Turn Rolling Window, No-Demotion, dan Free Tier quota.
 *
 * Simulasi IELTS Speaking (mode IELTS_SPEAKING) mengambil soal dari kurikulum
 * baru (units/lessons/questions); TOEFL tetap memakai QuestionBank lama.
 */
class SessionService
{
    public const TOTAL_TURNS_PLANNED = 5;

    /** Jumlah turn per part pada simulasi ujian (IELTS/TOEFL). */
    public const EXAM_TURNS_PER_PART = 2;

    public function __construct(
        private readonly SessionRepository $sessions,
        private readonly UserRepository $users,
        private readonly QuestionBankRepository $questions,
        private readonly QuestionRepository $curriculumQuestions,
        private readonly ThematicQuestionRepository $thematicQuestions,
        private readonly ConversationLogRepository $logs,
        private readonly AdaptiveLevelingEngine $engine,
        private readonly LessonEvaluator $lessonEvaluator,
        private readonly RepetitionService $repetition,
        private readonly QuotaService $quota,
    ) {
    }

    public function startSession(User $user, string $mode, ?int $topicId, ?int $lessonId = null): array
    {
        if (! $this->quota->canStartSession($user)) {
            throw PaywallRequiredException::forUser();
        }

        $mode = SessionMode::from($mode);
        $topic = $mode === SessionMode::THEMATIC && $topicId ? $topicId : null;
        $level = $this->resolveStartLevel($user, $mode, $topic);

        $totalTurns = $mode->isExam()
            ? $this->examTotalTurns($mode, $lessonId)
            : self::TOTAL_TURNS_PLANNED;

        $session = $this->sessions->create([
            'user_id' => $user->id,
            'mode' => $mode->value,
            'topic_id' => $topic,
            'lesson_id' => $lessonId,
            'start_level' => $level,
            'current_level' => $level,
            'total_turns_planned' => $totalTurns,
            'status' => 'ACTIVE',
        ]);

        $firstQuestion = $this->nextQuestionFor($session, $level, turn: 1, excludeIds: []);

        $payload = [
            'session_id' => $session->id,
            'mode' => $mode->value,
            'current_cefr_level' => $level,
            'total_turns_planned' => $totalTurns,
            'first_question' => $this->questionPayload($firstQuestion, 1, $level, $mode),
        ];

        if ($mode->isExam()) {
            $payload['exam'] = [
                'test_type' => $mode->testType(),
                'parts' => $this->examParts($mode, $lessonId),
            ];
        }

        return $payload;
    }

    public function evaluateTurn(User $user, array $payload): array
    {
        $session = $this->activeSession($user, $payload['session_id']);

        $turnNumber = (int) $payload['turn_number'];
        $this->assertTurnRange($session, $turnNumber);

        $existing = $this->logs->findTurn($user->id, $session->id, $turnNumber);
        if ($existing && $existing->step_state === SessionStepState::WAITING_REPETITION) {
            throw RepetitionNotPendingException::forTurn($turnNumber);
        }

        $isIeltsCurriculum = $this->isIeltsCurriculum($session);
        $isThematic = $session->mode === SessionMode::THEMATIC;

        $question = match (true) {
            $isIeltsCurriculum => $this->curriculumQuestions->find($payload['question_id']),
            $isThematic => $this->thematicQuestions->find($payload['question_id'])
                ?? $this->questions->find($payload['question_id']),
            default => $this->questions->find($payload['question_id']),
        };

        if (! $question) {
            throw new \InvalidArgumentException('question_id tidak valid.', 422);
        }

        $isThematicQuestion = $question instanceof ThematicQuestion;

        $transcript = trim($payload['user_transcript']);

        // Mode IELTS_SPEAKING memakai penilaian kurikulum:
        // Key Point Checklist 50%, Grammatical Accuracy 25%, Lexical Resource 25%
        // tanpa fallback/word-count; tanpa tahap repetition (feedback langsung).
        if ($isIeltsCurriculum) {
            return $this->evaluateIeltsTurn($user, $session, $question, $turnNumber, $transcript);
        }

        $scores = $this->engine->score($transcript, $question);

        // Versi koreksi berasal dari LLM (evaluator grammar).
        $corrected = $scores['corrected_sentence'] ?? null;
        $correctedChanged = $corrected !== null && $corrected !== $transcript;

        // Bila ada kesalahan: tampilkan koreksi LLM bila tersedia; jika tidak,
        // arahkan ke jawaban standar soal (model_answer untuk kurikulum).
        $standardAnswer = $question->model_answer ?? $question->standard_answer;
        $correctWay = $scores['has_error']
            ? ($correctedChanged ? $corrected : ($standardAnswer ?: $corrected))
            : null;

        if ($scores['has_error'] && ! $correctWay) {
            $correctWay = 'Jawab pertanyaan dengan kalimat utuh dan perhatikan grammar.';
        }

        $this->logs->logTurn(
            userId: $user->id,
            sessionId: $session->id,
            turnNumber: $turnNumber,
            questionId: $isThematicQuestion ? null : $question->id,
            transcript: $transcript,
            correctWay: $correctWay,
            scores: $scores,
            thematicQuestionId: $isThematicQuestion ? $question->id : null,
        );

        // Self-learning berbasis regex tidak lagi dipakai: penilaian grammar
        // kini ditangani langsung oleh LLM, sehingga tidak ada pola regex baru
        // yang perlu ditangkap dari ketidakcocokan rule.
        if ($turnNumber === 1) {
            $this->quota->consumeTrialSession($user);
        }

        if ($scores['has_error']) {
            return $this->errorTurnPayload($session, $turnNumber, $transcript, $correctWay, $scores);
        }

        // Promosi level ditunda sampai sesi selesai (completeSession).
        $promotion = null;

        $next = $this->nextQuestionPayload($session, $turnNumber);

        return [
            'step_state' => 'NORMAL',
            'scores' => [
                'word_count_score' => $scores['word_count_score'],
                'grammar_score' => $scores['grammar_score'],
                'total_turn_score' => $scores['total_turn_score'],
            ],
            'has_error' => false,
            'promotion' => $promotion,
            'ai_speech_prompt' => $next
                ? 'Great! Now, '.rtrim($next['question_text'], " \t\n\r\0\x0B?").'?'
                : 'That\'s all the turns. You can complete the session now.',
            'next_question' => $next,
        ];
    }

    /**
     * Evaluasi turn IELTS Speaking memakai penilaian kurikulum (LessonEvaluator):
     * Key Point 50%, Grammar 25%, Lexical 25%. Tidak ada tahap repetition;
     * feedback langsung diikuti soal berikutnya.
     */
    private function evaluateIeltsTurn(
        User $user,
        ConversationSession $session,
        Question $question,
        int $turnNumber,
        string $transcript,
    ): array {
        $evaluation = $this->lessonEvaluator->evaluateMany($question, [$transcript])[0];

        $score = (int) ($evaluation['score'] ?? 0);
        $correct = (bool) ($evaluation['is_correct'] ?? false);
        $suggestedAnswer = $correct ? '' : (string) ($evaluation['suggested_answer'] ?? '');

        $correctWay = $correct
            ? null
            : ($question->model_answer ?: 'Jawab dengan kalimat utuh sesuai key point lesson.');

        $this->logs->logTurn(
            userId: $user->id,
            sessionId: $session->id,
            turnNumber: $turnNumber,
            questionId: null,
            transcript: $transcript,
            correctWay: $correctWay,
            suggestedAnswer: $suggestedAnswer,
            scores: [
                'word_count_score' => 0,
                'grammar_score' => 0,
                'total_turn_score' => $score,
                'has_error' => ! $correct,
            ],
            curriculumQuestionId: $question->id,
            curriculumScore: $score,
            keyPointDetected: (bool) ($evaluation['key_point_detected'] ?? false),
            keyPointTarget: $evaluation['key_point_target'] ?? $question->key_point,
            grammarFeedback: $evaluation['grammar_feedback'] ?? '',
            vocabularyFeedback: $evaluation['vocabulary_feedback'] ?? '',
            normalOnError: true,
        );

        if ($turnNumber === 1) {
            $this->quota->consumeTrialSession($user);
        }

        $next = $this->nextQuestionPayload($session, $turnNumber);

        return [
            'step_state' => 'NORMAL',
            'ielts_curriculum' => true,
            'advance_on_error' => true,
            'scores' => [
                'score' => $score,
                'key_point_detected' => (bool) ($evaluation['key_point_detected'] ?? false),
                'key_point_target' => $evaluation['key_point_target'] ?? $question->key_point,
                'grammar_feedback' => $evaluation['grammar_feedback'] ?? '',
                'vocabulary_feedback' => $evaluation['vocabulary_feedback'] ?? '',
                'suggested_answer' => $suggestedAnswer,
            ],
            'has_error' => ! $correct,
            'promotion' => null,
            'ai_speech_prompt' => $next
                ? 'Great! Now, '.rtrim($next['question_text'], " \t\n\r\0\x0B?").'?'
                : 'That\'s all the turns. You can complete the session now.',
            'next_question' => $next,
        ];
    }

    public function verifyRepetition(User $user, array $payload): array
    {
        $session = $this->activeSession($user, $payload['session_id']);
        $turnNumber = (int) $payload['turn_number'];

        $log = $this->logs->findTurn($user->id, $session->id, $turnNumber)
            ?? throw new \InvalidArgumentException('Turn belum direkam pada sesi ini.', 422);

        if ($log->step_state !== SessionStepState::WAITING_REPETITION) {
            throw RepetitionNotPendingException::forTurn($turnNumber);
        }

        $expected = $log->expected_repetition_text ?? '';
        $success = $this->repetition->isSuccess($payload['user_repetition_transcript'], $expected);

        $this->logs->markRepetitionResult($log, $success, (int) $log->repetition_attempts + 1);

        // Promosi level ditunda sampai sesi selesai (completeSession).

        $next = $this->nextQuestionPayload($session, $turnNumber);

        return [
            'repetition_success' => $success,
            'repetition_attempts' => (int) $log->repetition_attempts,
            'step_state' => 'NORMAL',
            'ai_transition_speech' => $this->repetition->transitionSpeech(
                $success,
                $next ? $next['question_text'] : null,
            ),
            'next_question' => $next,
        ];
    }

    public function completeSession(User $user, string $sessionId): array
    {
        $session = $this->activeSession($user, $sessionId);
        $this->sessions->complete($session);

        $logs = $this->logs->sessionLogs($user->id, $session->id);
        $completed = $logs->count();

        $grammarWins = $logs->where('score_grammar', 1)->count();

        if ($session->mode->isExam()) {
            return $this->examCompletionPayload($session, $user, $logs, $completed, $grammarWins);
        }

        $accumulated = $this->bestFourConsecutiveScore($user, $session->id);

        // Promosi level hanya dievaluasi saat sesi berakhir.
        $this->maybePromote($session, $user, $completed);

        $promotion = $this->promotionRecord($user, $session->id);

        return [
            'session_id' => $session->id,
            'start_level' => $session->start_level,
            'cefr_level_current' => $user->current_cefr_level->value,
            'total_turns_completed' => $completed,
            'accumulated_score' => (int) $accumulated,
            'is_promoted' => $promotion !== null,
            'previous_cefr_level' => $promotion['previous_level'] ?? null,
            'new_cefr_level' => $promotion['new_level'] ?? null,
            'remaining_trial_sessions' => $user->remaining_trial_sessions,
            'diagnostic_report' => [
                'grammar_accuracy' => $completed > 0 ? round($grammarWins / $completed * 100).'%' : '0%',
                'frequent_errors' => $this->frequentErrors($logs),
                'tutor_notes' => $this->tutorNotes($promotion, (int) $accumulated, $user->current_cefr_level->value),
            ],
        ];
    }

    private function examCompletionPayload(
        ConversationSession $session,
        User $user,
        $logs,
        int $completed,
        int $grammarWins,
    ): array {
        $isIelts = $session->mode === SessionMode::IELTS_SPEAKING;

        // IELTS memakai skor kurikulum 0-100 per turn; TOEFL skor 0-2 per turn.
        $totalScore = $isIelts
            ? (int) $logs->sum('curriculum_score')
            : (int) $logs->sum('total_turn_score');
        $maxScore = $isIelts ? $completed * 100 : $completed * 2;

        $partByQuestion = \App\Models\QuestionBank::whereIn(
            'id',
            $logs->pluck('question_id')->reject(fn ($id) => $id === null)->all(),
        )->pluck('part_number', 'id');

        $curriculumIds = $logs->pluck('curriculum_question_id')
            ->reject(fn ($id) => $id === null)
            ->all();

        if ($curriculumIds) {
            $partByQuestion = $partByQuestion->union(
                Question::query()
                    ->with('lesson.unit')
                    ->whereIn('id', $curriculumIds)
                    ->get()
                    ->mapWithKeys(fn ($q) => [$q->id => $q->lesson?->unit?->part])
            );
        }

        $parts = [];
        foreach ($this->examParts($session->mode, $session->lesson_id) as $part) {
            $partLogs = $logs->filter(function ($log) use ($partByQuestion, $part) {
                $questionId = $log->curriculum_question_id ?? $log->question_id;

                return ($partByQuestion[$questionId] ?? null) === $part;
            });
            $partCount = $partLogs->count();

            if ($partCount === 0) {
                continue;
            }

            $partScore = $isIelts
                ? (int) $partLogs->sum('curriculum_score')
                : (int) $partLogs->sum('total_turn_score');
            $partMax = $isIelts ? $partCount * 100 : $partCount * 2;
            $parts[] = [
                'part_number' => $part,
                'turns' => $partCount,
                'total_score' => $partScore,
                'max_score' => $partMax,
                'score_pct' => (int) round($partScore / $partMax * 100),
            ];
        }

        // Accuracy untuk IELTS = rasio turn dengan key point terpenuhi;
        // TOEFL memakai rasio turn grammar benar.
        $accuracyTurns = $isIelts
            ? (int) $logs->where('key_point_detected', true)->count()
            : $grammarWins;

        return [
            'session_id' => $session->id,
            'start_level' => $session->start_level,
            'cefr_level_current' => $user->current_cefr_level->value,
            'total_turns_completed' => $completed,
            'accumulated_score' => $totalScore,
            'is_promoted' => false,
            'previous_cefr_level' => null,
            'new_cefr_level' => null,
            'remaining_trial_sessions' => $user->remaining_trial_sessions,
            'exam_report' => [
                'test_type' => $session->mode->testType(),
                'total_score' => $totalScore,
                'max_score' => $maxScore,
                'score_pct' => $maxScore > 0 ? (int) round($totalScore / $maxScore * 100) : 0,
                'grammar_accuracy' => $completed > 0 ? round($accuracyTurns / $completed * 100).'%' : '0%',
                'parts' => $parts,
            ],
            'diagnostic_report' => [
                'grammar_accuracy' => $completed > 0 ? round($accuracyTurns / $completed * 100).'%' : '0%',
                'frequent_errors' => $this->frequentErrors($logs),
                'tutor_notes' => $this->examTutorNotes($totalScore, $maxScore, $completed),
            ],
        ];
    }

    private function examTotalTurns(SessionMode $mode, ?int $lessonId = null): int
    {
        return count($this->examParts($mode, $lessonId)) * self::EXAM_TURNS_PER_PART;
    }

    /**
     * Daftar part untuk simulasi ujian. IELTS Speaking mengambil part dari
     * unit kurikulum (yang sudah punya soal); bila lesson ditentukan, part
     * dibatasi pada unit lesson tersebut. TOEFL memakai part QuestionBank.
     */
    private function examParts(SessionMode $mode, ?int $lessonId = null): array
    {
        if ($mode === SessionMode::IELTS_SPEAKING) {
            return $this->curriculumQuestions->partsForIelts($lessonId);
        }

        return $this->questions->partsForTest($mode->testType());
    }

    private function isIeltsCurriculum(ConversationSession $session): bool
    {
        return $session->mode === SessionMode::IELTS_SPEAKING;
    }

    private function resolveStartLevel(User $user, SessionMode $mode, ?int $topicId): string
    {
        if ($mode === SessionMode::ADAPTIVE || $mode->isExam() || $topicId === null) {
            return $user->current_cefr_level->value;
        }

        $topic = \App\Models\ThematicTopic::find($topicId);
        if (! $topic) {
            throw new \InvalidArgumentException('topic_id tidak valid.', 422);
        }

        return $this->mapTopicLevel($topic->selected_level) ?? $user->current_cefr_level->value;
    }

    private function mapTopicLevel(string $selectedLevel): ?string
    {
        return match (mb_strtolower($selectedLevel)) {
            'beginner' => 'A1',
            'elementary' => 'A2',
            'intermediate' => 'B1',
            'upper intermediate', 'upper-intermediate' => 'B2',
            'advanced' => 'C1',
            'proficient', 'proficiency' => 'C2',
            default => null,
        };
    }

    /**
     * Skor terbaik dari 4 turn BERURUTAN di mana pun dalam sesi.
     * Jendela digeser per turn (turn 1-4, 2-5, dst); diambil nilai
     * maksimumnya. Dasar promosi: ada 4 turn berurutan dengan skor >= 6
     * (maks per turn 2, sehingga 4 turn maksimal = 8).
     */
    private function bestFourConsecutiveScore(User $user, string $sessionId): int
    {
        $scores = $this->logs->sessionLogs($user->id, $sessionId)
            ->sortBy('turn_number')
            ->pluck('total_turn_score')
            ->values();

        $best = 0;
        $count = $scores->count();

        for ($i = 0; $i + 3 < $count; $i++) {
            $window = $scores->slice($i, 4)->sum();

            if ($window > $best) {
                $best = $window;
            }
        }

        return (int) $best;
    }

    private function activeSession(User $user, string $sessionId): ConversationSession
    {
        return $this->sessions->activeForUser($user->id, $sessionId)
            ?? throw SessionNotFoundException::forUser();
    }

    private function assertTurnRange(ConversationSession $session, int $turnNumber): void
    {
        if ($turnNumber < 1 || $turnNumber > $session->total_turns_planned) {
            throw new \InvalidArgumentException(
                "turn_number harus antara 1 dan {$session->total_turns_planned}.",
                422,
            );
        }
    }

    private function nextQuestionPayload(ConversationSession $session, int $turnNumber): ?array
    {
        if ($turnNumber >= $session->total_turns_planned) {
            return null;
        }

        $excludeIds = $this->logs->sessionLogs($session->user_id, $session->id)
            ->pluck('curriculum_question_id', 'question_id')
            ->flatMap(fn ($curriculumId, $questionId) => [$questionId, $curriculumId])
            ->merge(
                $this->logs->sessionLogs($session->user_id, $session->id)
                    ->pluck('thematic_question_id'),
            )
            ->reject(fn ($id) => $id === null)
            ->unique()
            ->values()
            ->all();

        $question = $this->nextQuestionFor($session, $session->current_level, $turnNumber + 1, $excludeIds);

        return $this->questionPayload($question, $turnNumber + 1, $session->current_level, $session->mode);
    }

    /**
     * Serialisasi payload soal untuk API/chatbot. Soal kurikulum disusun dari
     * unit/lesson (part_number, topic/theme) dan CEFR memakai level sesi.
     */
    private function questionPayload(
        QuestionBank|Question|ThematicQuestion $question,
        int $turn,
        string $level,
        SessionMode $mode,
    ): array {
        $isCurriculum = $question instanceof Question;
        $isThematic = $question instanceof ThematicQuestion;
        $unit = $isCurriculum ? $question->lesson?->unit : null;

        return [
            'question_id' => $question->id,
            'turn_number' => $turn,
            'question_text' => $question->question_text,
            'key_point' => ($isCurriculum || $isThematic) ? $question->key_point : null,
            'topic_category' => $isThematic
                ? ($question->topic?->topic_name ?? null)
                : ($isCurriculum ? ($unit?->title ?? null) : $question->topic_category),
            'cefr_level' => $isThematic ? $level : ($isCurriculum ? $level : $question->cefr_level->value),
            'part_number' => $isCurriculum ? ($unit?->part ?? null) : $question->part_number,
            'audio_url' => $isThematic || $isCurriculum ? null : data_get($question->metadata, 'audio_url'),
        ];
    }

    private function nextQuestionFor(
        ConversationSession $session,
        string $level,
        int $turn,
        array $excludeIds,
    ): QuestionBank|Question|ThematicQuestion {
        $question = null;

        // Mode ujian (IELTS/TOEFL): soal dipilih per part berurutan, tanpa promosi.
        if ($session->mode->isExam()) {
            $parts = $this->examParts($session->mode, $session->lesson_id);
            $preferredPart = $parts[floor(($turn - 1) / self::EXAM_TURNS_PER_PART)] ?? 1;

            $question = $this->isIeltsCurriculum($session)
                ? $this->curriculumQuestions->randomForPart($preferredPart, $excludeIds, $session->lesson_id)
                : $this->questions->nextForExam($session->mode->testType(), $excludeIds, $preferredPart);

            // Mode ujian tidak boleh jatuh ke soal ADAPTIVE bila konten part kosong.
            if ($question === null) {
                throw NoQuestionAvailableException::forLevel($level);
            }
        }

        // Mode THEMATIC: soal diambil dari thematic_questions khusus topik.
        if ($session->mode === SessionMode::THEMATIC && $session->topic_id) {
            $question = $this->thematicQuestions->randomForTopic($session->topic_id, $excludeIds);
        }

        $question ??= $this->questions->nextForTurn($level, $excludeIds, preferStarter: $turn === 1);

        return $question ?? throw NoQuestionAvailableException::forLevel($level);
    }

    private function maybePromote(ConversationSession $session, User $user, int $turnNumber): ?array
    {
        if ($turnNumber < 4) {
            return null;
        }

        $accumulated = $this->bestFourConsecutiveScore($user, $session->id);

        $current = CefrLevel::from($session->current_level);
        $next = $current->next();

        if ($next === null || $accumulated < AdaptiveLevelingEngine::PROMOTION_THRESHOLD) {
            return null;
        }

        $this->users->recordLevelPromotion($user, $next->value, $session->id, $accumulated);
        $this->sessions->setCurrentLevel($session, $next->value);

        return [
            'is_promoted' => true,
            'previous_level' => $current->value,
            'new_level' => $next->value,
            'trigger_score' => $accumulated,
        ];
    }

    private function promotionRecord(User $user, string $sessionId): ?array
    {
        $record = \App\Models\UserLevelHistory::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->latest()
            ->first();

        return $record ? [
            'previous_level' => $record->previous_level,
            'new_level' => $record->new_level,
        ] : null;
    }

    private function errorTurnPayload(
        ConversationSession $session,
        int $turnNumber,
        string $transcript,
        string $correctWay,
        array $scores,
    ): array {
        return [
            'step_state' => 'WAITING_REPETITION',
            'scores' => [
                'word_count_score' => $scores['word_count_score'],
                'grammar_score' => $scores['grammar_score'],
                'total_turn_score' => $scores['total_turn_score'],
            ],
            'has_error' => true,
            'correction_data' => [
                'user_said' => $transcript,
                'correct_way' => $correctWay,
            ],
            'expected_repetition_text' => $correctWay,
            'ai_speech_prompt' => "Not quite! Listen and repeat: \"{$correctWay}\"",
            'ai_audio_url' => null,
            'next_question' => null,
        ];
    }

    public function history(User $user, int $perPage = 10): array
    {
        $paginator = $this->sessions->forUserPaginated($user->id, $perPage);

        $items = $paginator->map(fn (ConversationSession $session) => $this->summaryFor($session))->all();

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function show(User $user, string $sessionId): array
    {
        $session = $this->sessions->findForUser($user->id, $sessionId);

        if (! $session) {
            throw new SessionNotFoundException('Sesi tidak ditemukan.');
        }

        $turns = $session->logs()
            ->orderBy('turn_number')
            ->get()
            ->map(fn ($log) => [
                'turn_number' => $log->turn_number,
                'question_text' => $log->curriculumQuestion?->question_text ?? $log->question?->question_text,
                'part_number' => $log->curriculumQuestion?->lesson?->unit?->part ?? $log->question?->part_number,
                'user_said_text' => $log->user_said_text,
                'correct_way_text' => $log->correct_way_text,
                'score_word_count' => $log->score_word_count,
                'score_grammar' => $log->score_grammar,
                'total_turn_score' => $log->total_turn_score,
                'curriculum_score' => $log->curriculum_score,
                'key_point_detected' => $log->key_point_detected,
                'key_point_target' => $log->key_point_target,
                'grammar_feedback' => $log->grammar_feedback,
                'vocabulary_feedback' => $log->vocabulary_feedback,
                'suggested_answer' => $log->suggested_answer,
                'has_error' => $log->has_error,
                'step_state' => $log->step_state->value,
                'repetition_success' => $log->repetition_success,
                'created_at' => $log->created_at?->toIso8601String(),
            ])
            ->all();

        return [
            'session' => $this->summaryFor($session),
            'turns' => $turns,
        ];
    }

    private function summaryFor(ConversationSession $session): array
    {
        $turns = (int) $session->logs_count;
        $isIelts = $session->mode === SessionMode::IELTS_SPEAKING;
        $totalScore = $isIelts
            ? (int) $session->curriculum_score_sum
            : (int) $session->total_score;
        $maxScore = $isIelts ? $turns * 100 : $turns * 2;

        return [
            'id' => $session->id,
            'mode' => $session->mode->value,
            'test_type' => $session->mode->isExam() ? $session->mode->testType() : null,
            'theme' => $session->topic?->title,
            'lesson_id' => $session->lesson_id,
            'start_level' => $session->start_level,
            'current_level' => $session->current_level,
            'status' => $session->status,
            'turns' => $turns,
            'total_score' => $totalScore,
            'score_pct' => $maxScore > 0 ? (int) round($totalScore / $maxScore * 100) : 0,
            'avg_grammar_accuracy' => $turns > 0 ? (int) round((float) $session->grammar_score_sum / $turns * 100) : 0,
            'started_at' => $session->created_at?->toIso8601String(),
            'completed_at' => $session->completed_at?->toIso8601String(),
        ];
    }

    private function frequentErrors($logs): array
    {
        $categories = [];

        foreach ($logs as $log) {
            if (! $log->has_error || ! $log->user_response_text) {
                continue;
            }

            foreach (app(\App\Repositories\GrammarRuleRepository::class)->detectViolations($log->user_response_text) as $violation) {
                $key = $violation['category'] ?? 'Grammar';
                $categories[$key] = ($categories[$key] ?? 0) + 1;
            }
        }

        arsort($categories);

        return array_keys(array_slice($categories, 0, 3));
    }

    private function tutorNotes(?array $promotion, int $accumulated, string $level): string
    {
        $minWords = \App\Services\Engine\AdaptiveLevelingEngine::WORD_COUNT_THRESHOLDS[$level] ?? 20;

        if ($promotion) {
            return "Selamat! Kamu naik level dari {$promotion['previous_level']} ke {$promotion['new_level']} "
                . "berkat skor {$accumulated}/8 pada 4-turn pertama. Terus berlatih untuk mencapai level berikutnya.";
        }

        if ($accumulated >= 5) {
            return 'Konsistensimu bagus! Pertahankan dan berani ambil topik baru untuk memperkaya kosakata.';
        }

        return "Terus berlatih! Usahakan jawaban minimal $minWords kata sesuai level $level dan perhatikan pola grammar tiap turn.";
    }

    private function examTutorNotes(int $totalScore, int $maxScore, int $completed): string
    {
        $pct = $maxScore > 0 ? (int) round($totalScore / $maxScore * 100) : 0;

        if ($pct >= 80) {
            return 'Performa sangat baik! Kamu menjawab dengan kalimat panjang dan grammar yang benar. '
                .'Pertahankan konsistensi ini untuk meraih band tinggi.';
        }

        if ($pct >= 60) {
            return 'Performa cukup baik. Beberapa turn masih perlu perbaikan grammar atau panjang jawaban. '
                .'Fokus pada keakuratan kalimat di tiap part.';
        }

        return "Tantangannya nyata! Dari {$completed} turn yang diselesaikan, perbanyak latihan jawaban "
            .'panjang dengan grammar benar agar siap menghadapi ujian.';
    }
}