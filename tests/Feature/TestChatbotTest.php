<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Livewire\Admin\TestChatbot\Index as TestChatbotIndex;
use App\Models\GrammarRule;
use App\Models\Lesson;
use App\Models\LlmSetting;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\ThematicTopic;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TestChatbotTest extends TestCase
{
    use RefreshDatabase;

    private const GOOD_TRANSCRIPT = 'Hello, my name is Budi. I live in the city of Jakarta and I greet my neighbours every morning. My family and I enjoy cooking together on the weekend.';

    protected function setUp(): void
    {
        parent::setUp();

        // Aktifkan LLM dengan respons palsu agar evaluasi grammar berjalan.
        config(['llm.api_key' => 'test-key', 'llm.base_url' => 'https://api.openai.com/v1']);
        LlmSetting::current()->update([
            'is_enabled' => true,
            'api_key' => 'test-key',
            'base_url' => 'https://api.openai.com/v1',
        ]);
        Http::fake([
            '*' => function ($request) {
                $content = '';
                foreach (data_get($request->data(), 'messages', []) as $m) {
                    if (($m['role'] ?? '') === 'user') {
                        $content = $m['content'] ?? '';
                    }
                }
                preg_match('/Learner sentence: "([^"]*)"/', $content, $m);
                $sentence = $m[1] ?? $content;

                $wrong = 'Yesterday I go to the market.';
                $isError = str_contains($sentence, $wrong);

                return Http::response([
                    'choices' => [[
                        'message' => ['content' => json_encode($isError
                            ? [
                                'is_correct' => false,
                                'corrected_sentence' => 'Yesterday I went to the market.',
                                'error_description' => 'Verb past tense salah.',
                            ]
                            : [
                                'is_correct' => true,
                                'corrected_sentence' => '',
                                'error_description' => '',
                            ])],
                    ]],
                ], 200);
            },
        ]);

        GrammarRule::create([
            'rule_code' => 'TENSE_01',
            'category' => 'Past Tense',
            'cefr_level' => 'A2',
            'regex_pattern' => '/\byesterday\s+.+\b(go|buy|see|eat)\b/i',
            'description' => 'On past context the verb must change form (went, bought, saw, ate).',
            'is_active' => true,
        ]);

        foreach (range(1, 5) as $i) {
            QuestionBank::create([
                'test_type' => 'ADAPTIVE',
                'part_number' => 1,
                'cefr_level' => 'A1',
                'question_text' => "Daily routine question number {$i} please.",
                'required_vocab_tags' => ['name', 'greet', 'city'],
                'is_starter' => $i === 1,
                'topic_category' => 'Daily Routine',
                'metadata' => ['audio_url' => null],
            ]);
        }

        // Soal level berikutnya untuk turn setelah promosi (rolling 4-turn window).
        foreach (['A2', 'B1', 'B2'] as $level) {
            foreach (range(1, 3) as $i) {
                QuestionBank::create([
                    'test_type' => 'ADAPTIVE',
                    'part_number' => 1,
                    'cefr_level' => $level,
                    'question_text' => "Next level {$level} question number {$i} please.",
                    'required_vocab_tags' => ['name', 'greet', 'city'],
                    'is_starter' => false,
                    'topic_category' => 'Daily Routine',
                    'metadata' => ['audio_url' => null],
                ]);
            }
        }
    }

    private function premiumUser(): User
    {
        return User::factory()->admin()->create([
            'current_cefr_level' => 'A1',
            'subscription_status' => SubscriptionStatus::PREMIUM_MONTHLY,
        ]);
    }

    public function test_chatbot_starts_adaptive_session(): void
    {
        $admin = $this->premiumUser();
        $this->actingAs($admin);

        Livewire::test(TestChatbotIndex::class)
            ->set('selectedUserId', $admin->id)
            ->set('selectedLevel', 'A1')
            ->call('startSession')
            ->assertSet('step', 'awaiting_answer')
            ->assertSet('sessionId', fn ($v) => is_string($v) && strlen($v) > 0)
            ->assertSet('currentTurn', 1)
            ->assertSet('messages.0.topic', 'Daily Routine');
    }

    public function test_chatbot_adaptive_full_flow_with_repetition(): void
    {
        $admin = $this->premiumUser();
        $this->actingAs($admin);

        $component = Livewire::test(TestChatbotIndex::class)
            ->set('selectedUserId', $admin->id)
            ->set('selectedLevel', 'A1')
            ->call('startSession');

        // Turn 1 error (dideteksi LLM) -> repetition loop.
        $component->set('transcript', 'Yesterday I go to the market.')
            ->call('submitAnswer')
            ->assertSet('step', 'awaiting_repetition')
            ->assertSet('expectedRepetition', fn ($v) => str_contains($v, 'went'));

        // Repetition sukses -> lanjut ke turn 2.
        $component->set('repetition', 'Yesterday I went to the market.')
            ->call('submitRepetition')
            ->assertSet('step', 'awaiting_answer')
            ->assertSet('currentTurn', 2);

        // Turn 2 dinilai benar oleh LLM -> langsung awaiting_answer (turn 3).
        $component->set('transcript', self::GOOD_TRANSCRIPT)
            ->call('submitAnswer')
            ->assertSet('step', 'awaiting_answer')
            ->assertSet('currentTurn', 3);

        // Turn 3 -> awaiting_answer (turn 4).
        $component->set('transcript', self::GOOD_TRANSCRIPT)
            ->call('submitAnswer')
            ->assertSet('currentTurn', 4);

        // Turn 4 -> awaiting_answer (turn 5).
        $component->set('transcript', self::GOOD_TRANSCRIPT)
            ->call('submitAnswer')
            ->assertSet('currentTurn', 5);

        // Turn 5 -> tidak ada next question -> awaiting_complete.
        $component->set('transcript', self::GOOD_TRANSCRIPT)
            ->call('submitAnswer')
            ->assertSet('step', 'awaiting_complete');

        $component->call('completeSession')
            ->assertSet('step', 'completed')
            ->assertSet('summary.total_turns_completed', 5);
    }

    public function test_chatbot_adaptive_restores_user_level_after_session(): void
    {
        $admin = $this->premiumUser();
        $this->actingAs($admin);

        $component = Livewire::test(TestChatbotIndex::class)
            ->set('selectedUserId', $admin->id)
            ->set('selectedLevel', 'B1')
            ->call('startSession');

        $this->assertSame('B1', $admin->fresh()->current_cefr_level->value);

        $component->call('resetAll')
            ->assertSet('step', 'idle');

        $this->assertSame('A1', $admin->fresh()->current_cefr_level->value);
    }

    public function test_chatbot_thematic_defaults_to_first_active_topic_when_none_selected(): void
    {
        ThematicTopic::create([
            'topic_name' => 'Airport Check-in & Travel',
            'roleplay_persona' => 'Airline Staff',
            'selected_level' => 'Elementary',
            'context_vocab_tags' => ['airport', 'ticket', 'flight'],
            'is_active' => true,
        ]);

        foreach (range(1, 3) as $i) {
            QuestionBank::create([
                'test_type' => 'ADAPTIVE',
                'part_number' => 1,
                'cefr_level' => 'A2',
                'question_text' => "Airport question number {$i} please.",
                'required_vocab_tags' => ['airport'],
                'is_starter' => false,
                'topic_category' => 'airport',
                'metadata' => ['audio_url' => null],
            ]);
        }

        $admin = $this->premiumUser();
        $this->actingAs($admin);

        Livewire::test(TestChatbotIndex::class)
            ->set('mode', 'THEMATIC')
            ->set('selectedUserId', $admin->id)
            ->call('startSession')
            ->assertSet('step', 'awaiting_answer')
            ->assertSet('currentTurn', 1)
            ->assertSet('selectedTopicId', ThematicTopic::first()->id)
            ->assertSet('currentQuestion.topic_category', 'airport');
    }

    public function test_chatbot_thematic_flow(): void
    {
        ThematicTopic::create([
            'topic_name' => 'Ordering Food at a Restaurant',
            'roleplay_persona' => 'Waiter',
            'selected_level' => 'Beginner',
            'context_vocab_tags' => ['menu', 'order', 'drink'],
            'is_active' => true,
        ]);

        foreach (range(1, 3) as $i) {
            QuestionBank::create([
                'test_type' => 'ADAPTIVE',
                'part_number' => 1,
                'cefr_level' => 'A1',
                'question_text' => "Restaurant question number {$i} please.",
                'required_vocab_tags' => ['menu', 'order'],
                'is_starter' => false,
                'topic_category' => 'Ordering Food at a Restaurant',
                'metadata' => ['audio_url' => null],
            ]);
        }

        $admin = $this->premiumUser();
        $this->actingAs($admin);

        Livewire::test(TestChatbotIndex::class)
            ->set('mode', 'THEMATIC')
            ->set('selectedUserId', $admin->id)
            ->set('selectedTopicId', ThematicTopic::first()->id)
            ->call('startSession')
            ->assertSet('step', 'awaiting_answer')
            ->assertSet('currentTurn', 1);
    }

    public function test_chatbot_ielts_mode_shows_curriculum_menu_then_starts_lesson(): void
    {
        $unit = Unit::create([
            'unit_number' => 501,
            'title' => 'Work & Study',
            'part' => 1,
        ]);
        $lesson = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 1,
            'title' => 'Introducing Yourself',
            'difficulty' => 'Easy',
        ]);
        foreach ([1, 2] as $i) {
            Question::create([
                'lesson_id' => $lesson->id,
                'question_text' => "Tell me about your daily routine {$i}.",
                'model_answer' => 'I wake up early and go to work.',
                'key_point' => 'introduction',
            ]);
        }

        $admin = $this->premiumUser();
        $this->actingAs($admin);

        Livewire::test(TestChatbotIndex::class)
            ->set('mode', 'IELTS_SPEAKING')
            ->set('selectedUserId', $admin->id)
            ->call('startSession')
            ->assertSet('step', 'selecting_lessons')
            ->call('toggleUnit', $unit->id)
            ->assertSet('expandedUnitId', $unit->id)
            ->call('startIeltsLesson', $lesson->id)
            ->assertSet('step', 'awaiting_answer')
            ->assertSet('currentTurn', 1)
            ->assertSet('currentQuestion.part_number', 1)
            ->assertSet('currentQuestion.topic_category', 'Work & Study');
    }

    public function test_chatbot_toefl_mode_starts_exam_session(): void
    {
        QuestionBank::create([
            'test_type' => 'TOEFL_IBT',
            'part_number' => 1,
            'cefr_level' => 'A2',
            'question_text' => 'TOEFL task one question.',
            'required_vocab_tags' => ['independent'],
            'is_starter' => false,
            'topic_category' => 'Education',
            'metadata' => ['audio_url' => null],
        ]);

        $admin = $this->premiumUser();
        $this->actingAs($admin);

        Livewire::test(TestChatbotIndex::class)
            ->set('mode', 'TOEFL_IBT')
            ->set('selectedUserId', $admin->id)
            ->call('startSession')
            ->assertSet('step', 'awaiting_answer')
            ->assertSet('currentTurn', 1)
            ->assertSet('currentQuestion.part_number', 1);
    }
}
