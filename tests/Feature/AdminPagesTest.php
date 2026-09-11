<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Livewire\Admin\AppConfig\Edit as AppConfigEdit;
use App\Livewire\Admin\ConversationLogs\Index as ConversationLogsIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\FillerWords\Index as FillerWordsIndex;
use App\Livewire\Admin\GrammarRules\Index as GrammarRulesIndex;
use App\Livewire\Admin\IeltsCurriculum\Index as IeltsCurriculumIndex;
use App\Livewire\Admin\PendingRules\Index as PendingRulesIndex;
use App\Livewire\Admin\PaymentGateway\Settings as PaymentGatewaySettings;
use App\Livewire\Admin\QuestionBanks\Index as QuestionBanksIndex;
use App\Livewire\Admin\Subscriptions\Index as SubscriptionsIndex;
use App\Livewire\Admin\TestChatbot\Index as TestChatbotIndex;
use App\Livewire\Admin\ThematicTopics\Index as ThematicTopicsIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\VocabularyBank\Index as VocabularyBankIndex;
use App\Models\AppConfiguration;
use App\Models\GrammarRule;
use App\Models\Lesson;
use App\Models\PendingGrammarRule;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\ThematicTopic;
use App\Models\Unit;
use App\Models\User;
use App\Models\VocabularyBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.question-banks.index'))->assertOk();
        $this->get(route('admin.grammar-rules.index'))->assertOk();
        $this->get(route('admin.ielts-curriculum.index'))->assertOk();
        $this->get(route('admin.pending-rules.index'))->assertOk();
        $this->get(route('admin.thematic-topics.index'))->assertOk();
        $this->get(route('admin.vocabulary.index'))->assertOk();
        $this->get(route('admin.filler-words.index'))->assertOk();
        $this->get(route('admin.data-transformation.index'))->assertOk();
        $this->get(route('admin.conversation-logs.index'))->assertOk();
        $this->get(route('admin.app-config.index'))->assertOk();
        $this->get(route('admin.payment-gateway.settings'))->assertOk();
        $this->get(route('admin.payment-test.index'))->assertOk();
        $this->get(route('admin.subscriptions.index'))->assertOk();
        $this->get(route('admin.test-chatbot.index'))->assertOk();
    }

    public function test_user_detail_page_render(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin);
        $this->get(route('admin.users.show', $user))->assertOk();
    }

    public function test_question_bank_can_be_created(): void
    {
        Livewire::test(QuestionBanksIndex::class)
            ->call('openCreate')
            ->set('test_type', 'ADAPTIVE')
            ->set('cefr_level', 'B1')
            ->set('question_text', 'Describe your weekend routine in detail.')
            ->set('standard_answer', 'My weekend routine usually starts with waking up late and having a relaxing breakfast with my family. I spend my afternoon playing sports with my friends at the park, and my evening watching movies or listening to music before I go to sleep.')
            ->set('vocabTags', 'weekend, relax, family')
            ->set('topic_category', 'Daily Routine')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('question_banks', [
            'test_type' => 'ADAPTIVE',
            'cefr_level' => 'B1',
            'topic_category' => 'Daily Routine',
        ]);
    }

    public function test_question_bank_rejects_duplicate_question_text(): void
    {
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'cefr_level' => 'A1',
            'question_text' => 'Duplicate question text?',
            'required_vocab_tags' => [],
            'topic_category' => 'General',
        ]);

        Livewire::test(QuestionBanksIndex::class)
            ->call('openCreate')
            ->set('question_text', 'Duplicate question text?')
            ->call('save')
            ->assertHasErrors('question_text');

        $this->assertSame(1, QuestionBank::count());
    }

    public function test_question_bank_can_be_edited_keeping_same_text(): void
    {
        $question = QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'cefr_level' => 'A1',
            'question_text' => 'Editable question text?',
            'standard_answer' => 'I like to relax on weekends by playing sports and spending time with my family at home.',
            'required_vocab_tags' => [],
            'topic_category' => 'General',
        ]);

        Livewire::test(QuestionBanksIndex::class)
            ->call('openEdit', $question->id)
            ->set('topic_category', 'Updated Topic')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Updated Topic', $question->fresh()->topic_category);
    }

    public function test_grammar_rule_can_be_created(): void
    {
        Livewire::test(GrammarRulesIndex::class)
            ->call('openCreate')
            ->set('rule_code', 'PREP_03')
            ->set('category', 'Preposition Error')
            ->set('cefr_level', 'B1')
            ->set('regex_pattern', '/\\bgo\\s+to\\b/i')
            ->set('description', 'Test rule')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('grammar_rules', ['rule_code' => 'PREP_03']);
    }

    public function test_pending_rule_approval_creates_grammar_rule(): void
    {
        $pending = PendingGrammarRule::create([
            'raw_user_input' => 'I am agree with you',
            'detected_error' => 'Agree bukan adjective.',
            'status' => 'PENDING',
        ]);

        Livewire::test(PendingRulesIndex::class)
            ->call('approve', $pending->id)
            ->assertSet('previewPendingId', $pending->id)
            ->call('saveRule');

        $this->assertDatabaseHas('pending_grammar_rules', ['id' => $pending->id, 'status' => 'APPROVED']);
        $this->assertDatabaseHas('grammar_rules', ['rule_code' => 'USER_001']);
        $this->assertNotNull($pending->fresh()->grammar_rule_id);
    }

    public function test_app_config_can_be_saved(): void
    {
        AppConfiguration::current();

        Livewire::test(AppConfigEdit::class)
            ->set('latest_app_version', '1.3.0')
            ->set('min_required_version', '1.2.0')
            ->set('is_force_update', true)
            ->set('play_store_url', 'https://play.google.com/store/apps/details?id=com.topspeak.app')
            ->set('update_message', 'Perbarui sekarang.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('app_configurations', ['latest_app_version' => '1.3.0', 'is_force_update' => true]);
    }

    public function test_app_config_can_save_free_tier_settings(): void
    {
        AppConfiguration::current();

        Livewire::test(AppConfigEdit::class)
            ->set('free_tier_initial_sessions', 3)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('app_configurations', [
            'free_tier_initial_sessions' => 3,
        ]);

        $this->assertSame(3, AppConfiguration::initialFreeSessions());
    }

    public function test_app_config_can_save_llm_settings(): void
    {
        AppConfiguration::current();

        Livewire::test(AppConfigEdit::class)
            ->set('llm_enabled', true)
            ->set('llm_provider', 'deepseek')
            ->set('llm_base_url', 'https://api.deepseek.com/v1')
            ->set('llm_api_key', 'sk-test-abc')
            ->set('llm_model', 'deepseek-chat')
            ->set('llm_timeout', 45)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('llm_settings', [
            'is_enabled' => true,
            'provider' => 'deepseek',
            'base_url' => 'https://api.deepseek.com/v1',
            'api_key' => 'sk-test-abc',
            'model' => 'deepseek-chat',
            'timeout' => 45,
        ]);

        $this->assertTrue(app(\App\Services\Llm\LlmClient::class)->enabled());
    }

    public function test_app_config_custom_model_is_saved(): void
    {
        AppConfiguration::current();

        Livewire::test(AppConfigEdit::class)
            ->set('llm_enabled', true)
            ->set('llm_provider', 'custom')
            ->set('llm_model', '__custom__')
            ->set('llm_model_custom', 'my-awesome-model-v2')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('llm_settings', ['model' => 'my-awesome-model-v2']);
    }

    public function test_app_config_set_model_uses_custom_when_not_in_options(): void
    {
        $component = Livewire::test(AppConfigEdit::class)
            ->set('llm_provider', 'deepseek')
            ->call('setModel', 'my-private-model');

        $this->assertSame('__custom__', $component->get('llm_model'));
        $this->assertSame('my-private-model', $component->get('llm_model_custom'));
    }

    public function test_app_config_provider_change_autofills_base_url(): void
    {
        $component = Livewire::test(AppConfigEdit::class)
            ->set('llm_provider', 'openai');

        // Ganti provider -> base_url otomatis terisi default provider.
        $component->set('llm_provider', 'deepseek');
        $this->assertSame('https://api.deepseek.com/v1', $component->get('llm_base_url'));

        $component->set('llm_provider', 'gemini');
        $this->assertSame('https://generativelanguage.googleapis.com/v1beta/openai', $component->get('llm_base_url'));

        $component->set('llm_provider', 'groq');
        $this->assertSame('https://api.groq.com/openai/v1', $component->get('llm_base_url'));
    }

    public function test_app_config_provider_change_keeps_custom_base_url(): void
    {
        $component = Livewire::test(AppConfigEdit::class)
            ->set('llm_provider', 'custom')
            ->set('llm_base_url', 'https://my-proxy.example.com/v1');

        // Pindah provider, tapi base_url custom tidak ditimpa.
        $component->set('llm_provider', 'openai');
        $this->assertSame('https://my-proxy.example.com/v1', $component->get('llm_base_url'));
    }

    public function test_thematic_topic_can_be_created(): void
    {
        Livewire::test(ThematicTopicsIndex::class)
            ->call('openCreate')
            ->set('topic_name', 'Weekend Plan')
            ->set('roleplay_persona', 'Friend')
            ->set('selected_level', 'Beginner')
            ->set('vocabTags', 'weekend, movie')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('thematic_topics', ['topic_name' => 'Weekend Plan']);
    }

    public function test_thematic_topic_rejects_duplicate_topic_name(): void
    {
        ThematicTopic::create([
            'topic_name' => 'Weekend Plan',
            'roleplay_persona' => 'Friend',
            'selected_level' => 'Beginner',
            'context_vocab_tags' => ['weekend'],
        ]);

        Livewire::test(ThematicTopicsIndex::class)
            ->call('openCreate')
            ->set('topic_name', 'Weekend Plan')
            ->set('roleplay_persona', 'Another Friend')
            ->call('save')
            ->assertHasErrors('topic_name');

        $this->assertSame(1, ThematicTopic::count());
    }

    public function test_vocabulary_can_be_created(): void
    {
        Livewire::test(VocabularyBankIndex::class)
            ->call('openCreate')
            ->set('word', 'strength')
            ->set('part_of_speech', 'noun')
            ->set('cefr_level', 'B1')
            ->set('topic_category', 'Job Interview')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vocabulary_bank', ['word' => 'strength']);
    }

    public function test_vocabulary_filters_filter_data(): void
    {
        VocabularyBank::create(['word' => 'alpha', 'part_of_speech' => 'noun', 'cefr_level' => 'A1', 'topic_category' => 'General']);
        VocabularyBank::create(['word' => 'beta', 'part_of_speech' => 'verb', 'cefr_level' => 'B2', 'topic_category' => 'Travel']);
        VocabularyBank::create(['word' => 'gamma', 'part_of_speech' => 'noun', 'cefr_level' => 'A1', 'topic_category' => 'Travel']);

        // Filter level.
        Livewire::test(VocabularyBankIndex::class)
            ->set('levelFilter', 'A1')
            ->assertSee('alpha')
            ->assertSee('gamma')
            ->assertDontSee('beta');

        // Filter jenis kata.
        Livewire::test(VocabularyBankIndex::class)
            ->set('posFilter', 'verb')
            ->assertSee('beta')
            ->assertDontSee('alpha')
            ->assertDontSee('gamma');

        // Filter topik.
        Livewire::test(VocabularyBankIndex::class)
            ->set('topicFilter', 'Travel')
            ->assertSee('beta')
            ->assertSee('gamma')
            ->assertDontSee('alpha');

        // Kombinasi level + topik.
        Livewire::test(VocabularyBankIndex::class)
            ->set('levelFilter', 'A1')
            ->set('topicFilter', 'Travel')
            ->assertSee('gamma')
            ->assertDontSee('alpha')
            ->assertDontSee('beta');

        // Cari kata.
        Livewire::test(VocabularyBankIndex::class)
            ->set('search', 'bet')
            ->assertSee('beta')
            ->assertDontSee('alpha');

        // Reset filter.
        Livewire::test(VocabularyBankIndex::class)
            ->set('levelFilter', 'A1')
            ->set('topicFilter', 'Travel')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('levelFilter', '')
            ->assertSet('posFilter', '')
            ->assertSet('topicFilter', '')
            ->assertSee('alpha')
            ->assertSee('beta')
            ->assertSee('gamma');
    }

    public function test_vocabulary_columns_can_be_sorted(): void
    {
        VocabularyBank::create(['word' => 'zebra', 'part_of_speech' => 'noun', 'cefr_level' => 'A1', 'topic_category' => 'Travel']);
        VocabularyBank::create(['word' => 'apple', 'part_of_speech' => 'verb', 'cefr_level' => 'A1', 'topic_category' => 'Food']);

        // Default sort word asc.
        Livewire::test(VocabularyBankIndex::class)
            ->assertSet('sortColumn', 'word')
            ->assertSet('sortDirection', 'asc')
            ->assertSeeInOrder(['apple', 'zebra']);

        // Klik kolom word (sudah asc) -> toggle desc.
        Livewire::test(VocabularyBankIndex::class)
            ->call('sortBy', 'word')
            ->assertSet('sortDirection', 'desc')
            ->assertSeeInOrder(['zebra', 'apple']);

        // Ganti kolom lain -> reset ke asc.
        Livewire::test(VocabularyBankIndex::class)
            ->call('sortBy', 'topic_category')
            ->assertSet('sortColumn', 'topic_category')
            ->assertSet('sortDirection', 'asc')
            ->assertSeeInOrder(['Food', 'Travel']);

        // Klik kolom baru lagi -> toggle desc.
        Livewire::test(VocabularyBankIndex::class)
            ->call('sortBy', 'topic_category')
            ->call('sortBy', 'topic_category')
            ->assertSet('sortDirection', 'desc')
            ->assertSeeInOrder(['Travel', 'Food']);
    }

    public function test_user_quota_can_be_updated(): void
    {
        $user = User::factory()->create(['remaining_trial_sessions' => 1]);

        Livewire::test(UsersIndex::class)
            ->call('startEdit', $user->id)
            ->set('remainingSessions', 4)
            ->call('saveQuota')
            ->assertHasNoErrors();

        $this->assertEquals(4, $user->fresh()->remaining_trial_sessions);
    }

    public function test_question_bank_tabs_filter_by_level(): void
    {
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'cefr_level' => 'A2',
            'question_text' => 'Level A2 question sample',
            'required_vocab_tags' => ['travel'],
            'topic_category' => 'Travel',
        ]);
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'cefr_level' => 'B2',
            'question_text' => 'Level B2 question sample',
            'required_vocab_tags' => ['opinion'],
            'topic_category' => 'Abstract',
        ]);

        Livewire::test(QuestionBanksIndex::class)
            ->call('setLevel', 'B2')
            ->assertSet('activeLevel', 'B2')
            ->assertSee('Abstract')
            ->assertDontSee('Travel');
    }

    public function test_question_bank_shows_topics_for_level_then_questions(): void
    {
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'cefr_level' => 'A2',
            'question_text' => 'Travel A2 question',
            'required_vocab_tags' => ['travel'],
            'topic_category' => 'Travel',
        ]);
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'cefr_level' => 'A2',
            'question_text' => 'Food A2 question',
            'required_vocab_tags' => ['menu'],
            'topic_category' => 'Food',
        ]);
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'cefr_level' => 'B2',
            'question_text' => 'Travel B2 question',
            'required_vocab_tags' => ['opinion'],
            'topic_category' => 'Travel',
        ]);

        // Tab level menampilkan daftar topik level tersebut (bukan soal).
        Livewire::test(QuestionBanksIndex::class)
            ->call('setLevel', 'A2')
            ->assertSet('activeLevel', 'A2')
            ->assertSet('activeTopic', '')
            ->assertSee('Travel')
            ->assertSee('Food')
            ->assertDontSee('Travel A2 question');

        // Klik topik -> soal topik+level yang cocok tampil.
        Livewire::test(QuestionBanksIndex::class)
            ->call('setLevel', 'A2')
            ->call('setTopic', 'Travel')
            ->assertSet('activeTopic', 'Travel')
            ->assertSee('Travel A2 question')
            ->assertDontSee('Food A2 question');

        // Ganti level mereset topik terpilih.
        Livewire::test(QuestionBanksIndex::class)
            ->call('setLevel', 'A2')
            ->call('setTopic', 'Travel')
            ->call('setLevel', 'B2')
            ->assertSet('activeLevel', 'B2')
            ->assertSet('activeTopic', '')
            ->assertSee('Travel');

        // Klik topik pada level B2 -> soal B2.
        Livewire::test(QuestionBanksIndex::class)
            ->call('setLevel', 'B2')
            ->call('setTopic', 'Travel')
            ->assertSee('Travel B2 question')
            ->assertDontSee('Travel A2 question');
    }

    public function test_ielts_curriculum_part_tab_filters_units(): void
    {
        Unit::create(['unit_number' => 1, 'title' => 'Home & Family', 'part' => 1]);
        Unit::create(['unit_number' => 5, 'title' => 'Technology & Society', 'part' => 2]);
        Unit::create(['unit_number' => 9, 'title' => 'Culture & Traditions', 'part' => 3]);

        Livewire::test(IeltsCurriculumIndex::class)
            ->assertSee('Home & Family')
            ->assertSee('Technology & Society')
            ->assertSee('Culture & Traditions');

        // Tab Part 1 -> hanya unit part 1.
        Livewire::test(IeltsCurriculumIndex::class)
            ->call('setPart', 1)
            ->assertSet('activePart', 1)
            ->assertSee('Home & Family')
            ->assertDontSee('Technology & Society')
            ->assertDontSee('Culture & Traditions');

        // Tab Part 2.
        Livewire::test(IeltsCurriculumIndex::class)
            ->call('setPart', 2)
            ->assertSet('activePart', 2)
            ->assertSee('Technology & Society')
            ->assertDontSee('Home & Family');

        // Tab Part 3.
        Livewire::test(IeltsCurriculumIndex::class)
            ->call('setPart', 3)
            ->assertSet('activePart', 3)
            ->assertSee('Culture & Traditions')
            ->assertDontSee('Technology & Society');

        // Tab Semua menghilangkan filter.
        Livewire::test(IeltsCurriculumIndex::class)
            ->call('setPart', null)
            ->assertSet('activePart', null)
            ->assertSee('Home & Family')
            ->assertSee('Technology & Society')
            ->assertSee('Culture & Traditions');
    }

    public function test_ielts_curriculum_navigation_drilldown(): void
    {
        $unit = Unit::create(['unit_number' => 1, 'title' => 'Home & Family', 'part' => 1]);
        $lesson = Lesson::create(['unit_id' => $unit->id, 'lesson_number' => 1, 'title' => 'Hometown & Living Place', 'difficulty' => 'Easy']);
        Question::create(['lesson_id' => $lesson->id, 'question_text' => 'Do you live in a house or an apartment?', 'model_answer' => 'I live in a spacious house with my close-knit family in a residential area.', 'key_point' => 'close-knit family']);

        // Klik unit -> tampil lessons.
        Livewire::test(IeltsCurriculumIndex::class)
            ->call('openUnit', $unit->id)
            ->assertSet('activeUnitId', $unit->id)
            ->assertSee('Hometown & Living Place')
            ->assertDontSee('Do you live in a house or an apartment?');

        // Klik lesson -> tampil soal.
        Livewire::test(IeltsCurriculumIndex::class)
            ->call('openUnit', $unit->id)
            ->call('openLesson', $lesson->id)
            ->assertSet('activeLessonId', $lesson->id)
            ->assertSee('Do you live in a house or an apartment?');

        // Kembali ke units.
        Livewire::test(IeltsCurriculumIndex::class)
            ->call('openUnit', $unit->id)
            ->call('openLesson', $lesson->id)
            ->call('backToLessons')
            ->call('backToUnits')
            ->assertSet('activeUnitId', null)
            ->assertSet('activeLessonId', null)
            ->assertSee('Home & Family');
    }

    public function test_dashboard_shows_stats(): void
    {
        User::factory()->create(['current_cefr_level' => 'A1']);
        User::factory()->create(['current_cefr_level' => 'B1']);

        Livewire::test(Dashboard::class)
            ->assertSee('Total Users');
    }

    public function test_filler_word_can_be_created_and_edited(): void
    {
        Livewire::test(FillerWordsIndex::class)
            ->call('openCreate')
            ->set('phrase', 'you know')
            ->set('category', 'phrase')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('filler_words', [
            'phrase' => 'you know',
            'category' => 'phrase',
            'is_active' => 1,
        ]);

        $word = \App\Models\FillerWord::where('phrase', 'you know')->first();

        Livewire::test(FillerWordsIndex::class)
            ->call('openEdit', $word->id)
            ->set('phrase', 'kind of')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('filler_words', [
            'phrase' => 'kind of',
            'category' => 'phrase',
        ]);
        $this->assertDatabaseMissing('filler_words', ['phrase' => 'you know']);
    }

    public function test_filler_word_can_be_deleted(): void
    {
        \App\Models\FillerWord::create([
            'phrase' => 'hmm',
            'category' => 'hesitation',
            'is_active' => true,
        ]);

        $word = \App\Models\FillerWord::where('phrase', 'hmm')->first();

        Livewire::test(FillerWordsIndex::class)
            ->call('delete', $word->id);

        $this->assertDatabaseMissing('filler_words', ['phrase' => 'hmm']);
    }

    public function test_question_bank_test_type_tab_filters_exam_questions(): void
    {
        QuestionBank::create([
            'test_type' => 'IELTS_SPEAKING',
            'part_number' => 3,
            'cefr_level' => 'C1',
            'question_text' => 'IELTS part three question',
            'required_vocab_tags' => ['exam'],
            'topic_category' => 'Abstract',
        ]);
        QuestionBank::create([
            'test_type' => 'TOEFL_IBT',
            'part_number' => 2,
            'cefr_level' => 'C1',
            'question_text' => 'TOEFL part two question',
            'required_vocab_tags' => ['exam'],
            'topic_category' => 'Education',
        ]);
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'cefr_level' => 'A1',
            'question_text' => 'Adaptive starter question',
            'required_vocab_tags' => ['name'],
            'is_starter' => true,
            'topic_category' => 'Introduction',
        ]);

        // Tab IELTS menampilkan soal IELTS langsung (tanpa kartu topik), bukan ADAPTIVE.
        Livewire::test(QuestionBanksIndex::class)
            ->call('setTestType', 'IELTS_SPEAKING')
            ->assertSet('activeTestType', 'IELTS_SPEAKING')
            ->assertSet('activeLevel', '')
            ->assertSee('IELTS part three question')
            ->assertDontSee('Adaptive starter question')
            ->assertDontSee('TOEFL part two question');

        // Tab TOEFL menampilkan soal TOEFL.
        Livewire::test(QuestionBanksIndex::class)
            ->call('setTestType', 'TOEFL_IBT')
            ->assertSee('TOEFL part two question')
            ->assertDontSee('IELTS part three question')
            ->assertDontSee('Adaptive starter question');

        // Tab ADAPTIVE kembali ke alur level -> topik (kartu topik, bukan soal langsung).
        Livewire::test(QuestionBanksIndex::class)
            ->call('setTestType', 'ADAPTIVE')
            ->assertSee('Introduction');
    }

    public function test_question_bank_part_filter_on_exam_tab(): void
    {
        QuestionBank::create([
            'test_type' => 'IELTS_SPEAKING',
            'part_number' => 3,
            'cefr_level' => 'C1',
            'question_text' => 'IELTS part three question',
            'required_vocab_tags' => ['exam'],
            'topic_category' => 'Abstract',
        ]);
        QuestionBank::create([
            'test_type' => 'IELTS_SPEAKING',
            'part_number' => 2,
            'cefr_level' => 'B2',
            'question_text' => 'IELTS part two question',
            'required_vocab_tags' => ['exam'],
            'topic_category' => 'Education',
        ]);

        // Filter part 3 -> hanya soal part 3 yang tampil.
        Livewire::test(QuestionBanksIndex::class)
            ->call('setTestType', 'IELTS_SPEAKING')
            ->call('setPart', 3)
            ->assertSet('activePart', 3)
            ->assertSee('IELTS part three question')
            ->assertDontSee('IELTS part two question');

        // Tanpa filter part -> semua part tampil.
        Livewire::test(QuestionBanksIndex::class)
            ->call('setTestType', 'IELTS_SPEAKING')
            ->call('setPart', null)
            ->assertSet('activePart', null)
            ->assertSee('IELTS part three question')
            ->assertSee('IELTS part two question');
    }

    public function test_open_create_preselects_test_type_from_active_tab(): void
    {
        Livewire::test(QuestionBanksIndex::class)
            ->call('setTestType', 'TOEFL_IBT')
            ->call('openCreate')
            ->assertSet('test_type', 'TOEFL_IBT');

        Livewire::test(QuestionBanksIndex::class)
            ->call('setTestType', 'ADAPTIVE')
            ->call('openCreate')
            ->assertSet('test_type', 'ADAPTIVE');
    }
}