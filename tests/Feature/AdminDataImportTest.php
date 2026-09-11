<?php

namespace Tests\Feature;

use App\Livewire\Admin\DataImport\Index as DataImportIndex;
use App\Models\FillerWord;
use App\Models\GrammarRule;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\ThematicTopic;
use App\Models\Unit;
use App\Models\User;
use App\Models\VocabularyBank;
use App\Repositories\GrammarDataRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDataImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_import_page_renders(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        $this->get(route('admin.data-import.index'))->assertOk();
    }

    public function test_import_question_banks_from_json(): void
    {
        $json = json_encode([
            [
                'test_type' => 'ADAPTIVE',
                'part_number' => 1,
                'cefr_level' => 'A1',
                'question_text' => 'Could you tell me about your hometown?',
                'required_vocab_tags' => ['hometown', 'city'],
                'is_starter' => false,
                'topic_category' => 'Introduction',
                'metadata' => ['audio_url' => null],
            ],
            [
                'test_type' => 'IELTS_SPEAKING',
                'part_number' => 3,
                'cefr_level' => 'B1',
                'question_text' => 'Describe a journey you have taken.',
                'required_vocab_tags' => ['journey'],
                'topic_category' => 'Travel',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'question_banks')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->assertSet('preview.valid', 2)
            ->call('confirmImport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('question_banks', ['question_text' => 'Could you tell me about your hometown?']);
        $this->assertDatabaseHas('question_banks', ['question_text' => 'Describe a journey you have taken.']);

        $question = QuestionBank::where('question_text', 'Describe a journey you have taken.')->first();
        $this->assertSame('IELTS_SPEAKING', $question->test_type->value);
        $this->assertSame('B1', $question->cefr_level->value);
        $this->assertSame(['journey'], $question->required_vocab_tags);
    }

    public function test_import_grammar_rules_updates_existing_by_code(): void
    {
        GrammarRule::create([
            'rule_code' => 'SVA_01',
            'category' => 'Subject-Verb Agreement',
            'cefr_level' => 'A2',
            'regex_pattern' => '/old/i',
            'description' => 'Old description.',
        ]);

        $json = json_encode([
            [
                'rule_code' => 'sva_01',
                'category' => 'Subject-Verb Agreement',
                'cefr_level' => 'A1',
                'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
                'description' => 'Subjek tunggal harus diikuti verb -s.',
                'is_active' => true,
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'grammar_rules')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->call('confirmImport')
            ->assertHasNoErrors();

        $this->assertSame(1, GrammarRule::count());

        $rule = GrammarRule::where('rule_code', 'SVA_01')->first();
        $this->assertSame('A1', $rule->cefr_level->value);
        $this->assertSame('/\b(he|she|it)\s+go\b/i', $rule->regex_pattern);
    }

    public function test_import_thematic_topics_from_json(): void
    {
        $json = json_encode([
            [
                'topic_name' => 'Talking About Family',
                'roleplay_persona' => 'Friendly Neighbor',
                'selected_level' => 'Beginner',
                'context_vocab_tags' => ['family', 'home'],
                'is_active' => true,
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'thematic_topics')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->call('confirmImport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('thematic_topics', ['topic_name' => 'Talking About Family']);

        $topic = ThematicTopic::where('topic_name', 'Talking About Family')->first();
        $this->assertSame(['family', 'home'], $topic->context_vocab_tags);
    }

    public function test_import_vocabulary_from_json(): void
    {
        $json = json_encode([
            [
                'word' => 'Hometown',
                'part_of_speech' => 'noun',
                'cefr_level' => 'A1',
                'topic_category' => 'Introduction',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'vocabulary_bank')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->call('confirmImport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vocabulary_bank', ['word' => 'hometown']);
        $this->assertSame(1, VocabularyBank::count());
    }

    public function test_import_filler_words_from_json(): void
    {
        $json = json_encode([
            [
                'phrase' => 'Uh',
                'category' => 'hesitation',
                'is_active' => true,
            ],
            [
                'phrase' => 'you know',
                'category' => 'phrase',
                'is_active' => true,
            ],
            [
                'phrase' => 'weird category',
                'category' => 'unknown',
                'is_active' => false,
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'filler_words')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->assertSet('preview.valid', 3)
            ->call('confirmImport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('filler_words', ['phrase' => 'uh', 'category' => 'hesitation']);
        $this->assertDatabaseHas('filler_words', ['phrase' => 'you know', 'category' => 'phrase']);
        $this->assertDatabaseHas('filler_words', ['phrase' => 'weird category', 'category' => 'hesitation']);
        $this->assertSame(3, FillerWord::count());
    }

    public function test_import_preview_does_not_persist_until_confirmed(): void
    {
        $json = json_encode([
            [
                'question_text' => 'Preview only question?',
                'cefr_level' => 'A1',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'question_banks')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertSet('preview.valid', 1);

        $this->assertDatabaseMissing('question_banks', ['question_text' => 'Preview only question?']);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'question_banks')
            ->set('jsonContent', $json)
            ->call('import')
            ->call('confirmImport')
            ->assertSet('preview', null);

        $this->assertDatabaseHas('question_banks', ['question_text' => 'Preview only question?']);
    }

    public function test_confirm_import_requires_preview_first(): void
    {
        Livewire::test(DataImportIndex::class)
            ->set('importType', 'question_banks')
            ->set('jsonContent', json_encode([['question_text' => 'No preview?']]))
            ->call('confirmImport')
            ->assertHasErrors('jsonContent');
    }

    public function test_import_flags_duplicates_in_json_during_preview(): void
    {
        $json = json_encode([
            [
                'rule_code' => 'SVA_01',
                'category' => 'Subject-Verb Agreement',
                'cefr_level' => 'A1',
                'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
                'description' => 'Rule pertama.',
            ],
            [
                'rule_code' => 'sva_01',
                'category' => 'Subject-Verb Agreement',
                'cefr_level' => 'A2',
                'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
                'description' => 'Rule kedua, kode sama.',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'grammar_rules')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertSet('preview.valid', 2)
            ->assertSet('preview.duplicates_in_json', ['SVA_01'])
            ->call('confirmImport');

        $this->assertSame(1, GrammarRule::count());
    }

    public function test_import_reports_duplicates_against_database(): void
    {
        GrammarRule::create([
            'rule_code' => 'SVA_01',
            'category' => 'Subject-Verb Agreement',
            'cefr_level' => 'A2',
            'regex_pattern' => '/old/i',
            'description' => 'Old description.',
        ]);

        $json = json_encode([
            [
                'rule_code' => 'SVA_01',
                'category' => 'Subject-Verb Agreement',
                'cefr_level' => 'A1',
                'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
                'description' => 'Deskripsi baru.',
            ],
            [
                'rule_code' => 'SVA_02',
                'category' => 'Subject-Verb Agreement',
                'cefr_level' => 'A1',
                'regex_pattern' => '/\b(they|we|you)\s+goes\b/i',
                'description' => 'Rule baru.',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'grammar_rules')
            ->set('jsonContent', $json)
            ->call('import')
            ->call('confirmImport')
            ->assertSet('result.created', 1)
            ->assertSet('result.updated', 1)
            ->assertSet('result.duplicates', ['SVA_01']);

        $this->assertSame(2, GrammarRule::count());
        $this->assertSame('A1', GrammarRule::where('rule_code', 'SVA_01')->first()->cefr_level->value);
    }

    public function test_import_rejects_invalid_json(): void
    {
        Livewire::test(DataImportIndex::class)
            ->set('importType', 'question_banks')
            ->set('jsonContent', '{ not valid json')
            ->call('import')
            ->assertHasErrors('jsonContent');
    }

    public function test_import_reports_row_errors_without_rolling_back_others(): void
    {
        $json = json_encode([
            [
                'question_text' => 'Valid question number one?',
                'cefr_level' => 'A1',
            ],
            [
                'cefr_level' => 'A2',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'question_banks')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->assertSet('preview.valid', 1)
            ->assertCount('preview.errors', 1)
            ->call('confirmImport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('question_banks', ['question_text' => 'Valid question number one?']);
        $this->assertSame(1, QuestionBank::count());
    }

    public function test_export_question_banks_returns_json_of_all_rows(): void
    {
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'part_number' => 1,
            'cefr_level' => 'A1',
            'question_text' => 'Could you tell me about your hometown?',
            'standard_answer' => 'My hometown is a small and quiet city in the mountains.',
            'required_vocab_tags' => ['hometown', 'city'],
            'is_starter' => true,
            'topic_category' => 'Introduction',
            'metadata' => ['audio_url' => 'https://example.com/a.mp3'],
        ]);
        QuestionBank::create([
            'test_type' => 'IELTS_SPEAKING',
            'part_number' => 3,
            'cefr_level' => 'B1',
            'question_text' => 'Describe a journey you have taken.',
            'standard_answer' => 'I would like to describe a memorable journey I took to Bali last year with my family and friends.',
            'required_vocab_tags' => ['journey'],
            'topic_category' => 'Travel',
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'question_banks')
            ->call('exportData')
            ->assertSet('exportJson', json_encode([
                [
                    'test_type' => 'ADAPTIVE',
                    'part_number' => 1,
                    'cefr_level' => 'A1',
                    'question_text' => 'Could you tell me about your hometown?',
                    'standard_answer' => 'My hometown is a small and quiet city in the mountains.',
                    'required_vocab_tags' => ['hometown', 'city'],
                    'is_starter' => true,
                    'topic_category' => 'Introduction',
                    'metadata' => ['audio_url' => 'https://example.com/a.mp3'],
                ],
                [
                    'test_type' => 'IELTS_SPEAKING',
                    'part_number' => 3,
                    'cefr_level' => 'B1',
                    'question_text' => 'Describe a journey you have taken.',
                    'standard_answer' => 'I would like to describe a memorable journey I took to Bali last year with my family and friends.',
                    'required_vocab_tags' => ['journey'],
                    'is_starter' => false,
                    'topic_category' => 'Travel',
                    'metadata' => ['audio_url' => null],
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
            ->assertNotSet('exportedAt', null);
    }

    public function test_export_grammar_rules_returns_json_of_all_rows(): void
    {
        GrammarRule::create([
            'rule_code' => 'SVA_01',
            'category' => 'Subject-Verb Agreement',
            'cefr_level' => 'A2',
            'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
            'description' => 'Subjek tunggal harus diikuti verb -s.',
            'is_active' => true,
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'grammar_rules')
            ->call('exportData')
            ->assertSet('exportJson', json_encode([
                [
                    'rule_code' => 'SVA_01',
                    'category' => 'Subject-Verb Agreement',
                    'cefr_level' => 'A2',
                    'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
                    'description' => 'Subjek tunggal harus diikuti verb -s.',
                    'is_active' => true,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function test_export_thematic_topics_and_vocabulary(): void
    {
        ThematicTopic::create([
            'topic_name' => 'Talking About Family',
            'roleplay_persona' => 'Friendly Neighbor',
            'selected_level' => 'Beginner',
            'context_vocab_tags' => ['family', 'home'],
            'is_active' => true,
        ]);

        VocabularyBank::create([
            'word' => 'hometown',
            'part_of_speech' => 'noun',
            'cefr_level' => 'A1',
            'topic_category' => 'Introduction',
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'thematic_topics')
            ->call('exportData')
            ->assertSet('exportJson', json_encode([
                [
                    'topic_name' => 'Talking About Family',
                    'roleplay_persona' => 'Friendly Neighbor',
                    'selected_level' => 'Beginner',
                    'context_vocab_tags' => ['family', 'home'],
                    'is_active' => true,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'vocabulary_bank')
            ->call('exportData')
            ->assertSet('exportJson', json_encode([
                [
                    'word' => 'hometown',
                    'part_of_speech' => 'noun',
                    'cefr_level' => 'A1',
                    'topic_category' => 'Introduction',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function test_clear_export_resets_json(): void
    {
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'part_number' => 1,
            'cefr_level' => 'A1',
            'question_text' => 'A test question?',
            'required_vocab_tags' => [],
            'topic_category' => 'General',
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'question_banks')
            ->call('exportData')
            ->assertNotSet('exportJson', '')
            ->call('clearExport')
            ->assertSet('exportJson', '')
            ->assertSet('exportedAt', null);
    }

    public function test_export_type_sets_selected_type_and_exports(): void
    {
        GrammarRule::create([
            'rule_code' => 'SVA_01',
            'category' => 'Subject-Verb Agreement',
            'cefr_level' => 'A2',
            'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
            'description' => 'Subjek tunggal harus diikuti verb -s.',
            'is_active' => true,
        ]);

        Livewire::test(DataImportIndex::class)
            ->call('exportType', 'grammar_rules')
            ->assertSet('importType', 'grammar_rules')
            ->assertSet('exportJson', json_encode([
                [
                    'rule_code' => 'SVA_01',
                    'category' => 'Subject-Verb Agreement',
                    'cefr_level' => 'A2',
                    'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
                    'description' => 'Subjek tunggal harus diikuti verb -s.',
                    'is_active' => true,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function test_import_word_transformation_writes_json_files(): void
    {
        $tempDir = sys_get_temp_dir().'/topspeak_import_test_'.uniqid();
        File::makeDirectory($tempDir, 0755, true);
        $this->app->instance(GrammarDataRepository::class, new GrammarDataRepository($tempDir));

        try {
            $json = json_encode([
                [
                    'collection' => 'verbs',
                    'base_v1' => 'See',
                    'past_simple_v2' => 'Saw',
                    'past_participle_v3' => 'Seen',
                    'cefr_level' => 'a1',
                ],
                [
                    'collection' => 'nouns',
                    'singular' => 'Child',
                    'plural' => 'Children',
                    'cefr_level' => 'A1',
                ],
                [
                    'collection' => 'verbs',
                    'base_v1' => 'go',
                    'past_simple_v2' => 'went',
                    'past_participle_v3' => 'gone',
                    'cefr_level' => 'A1',
                ],
            ]);

            Livewire::test(DataImportIndex::class)
                ->set('importType', 'word_transformation')
                ->set('jsonContent', $json)
                ->call('import')
                ->assertHasNoErrors()
                ->assertSet('preview.valid', 3)
                ->call('confirmImport')
                ->assertHasNoErrors();

            $verbs = json_decode(File::get($tempDir.'/irregular_verbs.json'), true);
            $this->assertSame('see', $verbs[0]['base_v1']);
            $this->assertSame('saw', $verbs[0]['past_simple_v2']);
            $this->assertSame('A1', $verbs[0]['cefr_level']);
            $this->assertSame('go', $verbs[1]['base_v1']);

            $nouns = json_decode(File::get($tempDir.'/irregular_nouns.json'), true);
            $this->assertSame('child', $nouns[0]['singular']);
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    public function test_import_word_transformation_updates_existing_by_unique_key(): void
    {
        $tempDir = sys_get_temp_dir().'/topspeak_import_test_'.uniqid();
        File::makeDirectory($tempDir, 0755, true);
        $this->app->instance(GrammarDataRepository::class, new GrammarDataRepository($tempDir));

        try {
            File::put($tempDir.'/irregular_verbs.json', json_encode([
                ['base_v1' => 'go', 'past_simple_v2' => 'went', 'past_participle_v3' => 'gone', 'cefr_level' => 'A1'],
            ]));

            $json = json_encode([
                [
                    'collection' => 'verbs',
                    'base_v1' => 'go',
                    'past_simple_v2' => 'went',
                    'past_participle_v3' => 'gone',
                    'cefr_level' => 'A2',
                ],
            ]);

            Livewire::test(DataImportIndex::class)
                ->set('importType', 'word_transformation')
                ->set('jsonContent', $json)
                ->call('import')
                ->call('confirmImport')
                ->assertSet('result.updated', 1);

            $verbs = json_decode(File::get($tempDir.'/irregular_verbs.json'), true);
            $this->assertCount(1, $verbs);
            $this->assertSame('A2', $verbs[0]['cefr_level']);
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    public function test_export_word_transformation_reads_json_files(): void
    {
        $tempDir = sys_get_temp_dir().'/topspeak_import_test_'.uniqid();
        File::makeDirectory($tempDir, 0755, true);
        $this->app->instance(GrammarDataRepository::class, new GrammarDataRepository($tempDir));

        try {
            File::put($tempDir.'/irregular_verbs.json', json_encode([
                ['base_v1' => 'go', 'past_simple_v2' => 'went', 'past_participle_v3' => 'gone', 'cefr_level' => 'A1'],
            ]));
            File::put($tempDir.'/demonstratives_and_pronouns.json', json_encode([
                'pronouns' => [
                    ['subject' => 'he', 'object' => 'him', 'possessive_adjective' => 'his', 'possessive_pronoun' => 'his', 'reflexive' => 'himself'],
                ],
                'demonstratives' => [
                    ['singular' => 'this', 'plural' => 'these', 'distance' => 'near'],
                ],
            ]));

            Livewire::test(DataImportIndex::class)
                ->set('importType', 'word_transformation')
                ->call('exportData')
                ->assertSet('exportJson', json_encode([
                    ['collection' => 'verbs', 'base_v1' => 'go', 'past_simple_v2' => 'went', 'past_participle_v3' => 'gone', 'cefr_level' => 'A1'],
                    ['collection' => 'pronouns', 'subject' => 'he', 'object' => 'him', 'possessive_adjective' => 'his', 'possessive_pronoun' => 'his', 'reflexive' => 'himself'],
                    ['collection' => 'demonstratives', 'singular' => 'this', 'plural' => 'these', 'distance' => 'near'],
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    public function test_import_word_transformation_rejects_unknown_collection(): void
    {
        $json = json_encode([
            ['collection' => 'unknown', 'base_v1' => 'see', 'past_simple_v2' => 'saw', 'past_participle_v3' => 'seen', 'cefr_level' => 'A1'],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'word_transformation')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->assertSet('preview.valid', 0)
            ->assertSet('preview.errors', fn (array $errors) => count($errors) === 1);
    }

    public function test_word_transformation_sample_covers_all_collections(): void
    {
        $sample = Livewire::test(DataImportIndex::class)
            ->set('importType', 'word_transformation')
            ->call('fillExample')
            ->get('jsonContent');

        $rows = json_decode($sample, true);
        $collections = array_values(array_unique(array_column($rows, 'collection')));

        sort($collections);
        $this->assertSame(['adjectives', 'demonstratives', 'nouns', 'pronouns', 'verbs'], $collections);
    }

    private function seedCurriculumUnit(): array
    {
        $unit = Unit::create([
            'unit_number' => 13,
            'title' => 'Hobbies & Entertainment',
            'part' => 3,
        ]);

        $lesson1 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 1,
            'title' => 'Collocations',
            'difficulty' => 'Easy',
        ]);

        $lesson2 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 2,
            'title' => 'TV shows',
            'difficulty' => 'Medium',
        ]);

        return [$unit, $lesson1, $lesson2];
    }

    public function test_import_ielts_curriculum_adds_questions_to_existing_lessons(): void
    {
        [$unit, $lesson1, $lesson2] = $this->seedCurriculumUnit();

        $json = json_encode([
            [
                'lesson_number' => 1,
                'question_text' => 'Is going to a movie theater still a big part of weekend culture?',
                'model_answer' => 'Yes, it remains a big part of youth culture.',
                'key_point' => 'a big part of / a movie theater',
            ],
            [
                'lesson_number' => 2,
                'question_text' => 'What kinds of TV shows are popular in your country?',
                'model_answer' => 'Reality shows and dramas are popular.',
                'key_point' => 'TV shows',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'ielts_curriculum')
            ->set('curriculumUnitId', $unit->id)
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->assertSet('preview.valid', 2)
            ->call('confirmImport')
            ->assertSet('result.created', 2);

        $this->assertSame(1, $lesson1->questions()->where('question_text', 'LIKE', '%movie theater%')->count());
        $this->assertSame(1, $lesson2->questions()->where('question_text', 'LIKE', '%TV shows%')->count());

        $q = Question::where('lesson_id', $lesson1->id)->first();
        $this->assertSame('a big part of / a movie theater', $q->key_point);

        $unit->refresh();
        $this->assertSame(3, $unit->part);
    }

    public function test_import_ielts_curriculum_rejects_lesson_not_in_unit(): void
    {
        [$unit] = $this->seedCurriculumUnit();

        $json = json_encode([
            [
                'lesson_number' => 99,
                'question_text' => 'A question in a missing lesson?',
                'model_answer' => 'Answer.',
                'key_point' => 'key point',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'ielts_curriculum')
            ->set('curriculumUnitId', $unit->id)
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->assertSet('preview.valid', 0)
            ->assertSet('preview.errors', fn (array $errors) => count($errors) === 1 && str_contains($errors[0], '99'));

        $this->assertSame(0, Question::count());
    }

    public function test_import_ielts_curriculum_requires_selected_unit(): void
    {
        $json = json_encode([
            [
                'lesson_number' => 1,
                'question_text' => 'No unit selected?',
                'model_answer' => 'Answer.',
                'key_point' => 'key point',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'ielts_curriculum')
            ->set('jsonContent', $json)
            ->call('import')
            ->assertHasNoErrors()
            ->assertSet('preview.valid', 0)
            ->assertSet('preview.errors', fn (array $errors) => count($errors) === 1 && str_contains($errors[0], 'Pilih unit'));
    }

    public function test_import_ielts_curriculum_flags_duplicates_in_json(): void
    {
        [$unit] = $this->seedCurriculumUnit();

        $json = json_encode([
            [
                'lesson_number' => 1,
                'question_text' => 'Duplicate in JSOON?',
                'model_answer' => 'Answer one.',
                'key_point' => 'key point',
            ],
            [
                'lesson_number' => 1,
                'question_text' => 'Duplicate in JSOON?',
                'model_answer' => 'Answer two.',
                'key_point' => 'key point',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'ielts_curriculum')
            ->set('curriculumUnitId', $unit->id)
            ->set('jsonContent', $json)
            ->call('import')
            ->assertSet('preview.valid', 2)
            ->assertSet('preview.duplicates_in_json', ['L1 :: Duplicate in JSOON?'])
            ->call('confirmImport');

        $this->assertSame(1, Question::count());
    }

    public function test_import_ielts_curriculum_updates_existing_question_in_db(): void
    {
        [$unit, $lesson1] = $this->seedCurriculumUnit();

        Question::create([
            'lesson_id' => $lesson1->id,
            'question_text' => 'Which movie theater is your favorite?',
            'model_answer' => 'Old answer.',
            'key_point' => 'old',
        ]);

        $json = json_encode([
            [
                'lesson_number' => 1,
                'question_text' => 'Which movie theater is your favorite?',
                'model_answer' => 'The one downtown with IMAX screens.',
                'key_point' => 'a movie theater',
            ],
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'ielts_curriculum')
            ->set('curriculumUnitId', $unit->id)
            ->set('jsonContent', $json)
            ->call('import')
            ->call('confirmImport')
            ->assertSet('result.created', 0)
            ->assertSet('result.updated', 1)
            ->assertSet('result.duplicates', ['L1 :: Which movie theater is your favorite?']);

        $this->assertSame(1, Question::count());
        $q = Question::where('lesson_id', $lesson1->id)->first();
        $this->assertSame('a movie theater', $q->key_point);
    }

    public function test_export_ielts_curriculum_returns_questions_of_selected_unit(): void
    {
        [$unit] = $this->seedCurriculumUnit();

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'ielts_curriculum')
            ->set('curriculumUnitId', $unit->id)
            ->call('exportData')
            ->assertSet('exportJson', json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Question::create([
            'lesson_id' => $unit->lessons()->where('lesson_number', 1)->first()->id,
            'question_text' => 'A question?',
            'model_answer' => 'An answer.',
            'key_point' => 'key',
        ]);

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'ielts_curriculum')
            ->set('curriculumUnitId', $unit->id)
            ->call('exportData')
            ->assertSet('exportJson', json_encode([
                [
                    'lesson_number' => 1,
                    'question_text' => 'A question?',
                    'model_answer' => 'An answer.',
                    'key_point' => 'key',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function test_ielts_curriculum_sample_uses_lesson_number_structure(): void
    {
        $sample = Livewire::test(DataImportIndex::class)
            ->set('importType', 'ielts_curriculum')
            ->call('fillExample')
            ->get('jsonContent');

        $rows = json_decode($sample, true);
        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertArrayHasKey('lesson_number', $row);
            $this->assertArrayHasKey('question_text', $row);
        }
    }

    public function test_ielts_curriculum_selector_renders_unit_picker(): void
    {
        [$unit] = $this->seedCurriculumUnit();

        Livewire::test(DataImportIndex::class)
            ->set('importType', 'ielts_curriculum')
            ->set('curriculumPart', 3)
            ->set('curriculumUnitId', $unit->id)
            ->assertOk();
    }
}
