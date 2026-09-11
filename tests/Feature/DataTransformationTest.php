<?php

namespace Tests\Feature;

use App\Livewire\Admin\DataTransformation\Index as DataTransformationIndex;
use App\Models\User;
use App\Repositories\GrammarDataRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class DataTransformationTest extends TestCase
{
    use RefreshDatabase;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/topspeak_grammar_test_'.uniqid();
        File::makeDirectory($this->tempDir, 0755, true);

        $this->seedSampleFiles();

        $this->app->instance(
            GrammarDataRepository::class,
            new GrammarDataRepository($this->tempDir),
        );
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempDir);
        parent::tearDown();
    }

    private function seedSampleFiles(): void
    {
        File::put($this->tempDir.'/irregular_verbs.json', json_encode([
            ['base_v1' => 'go', 'past_simple_v2' => 'went', 'past_participle_v3' => 'gone', 'cefr_level' => 'A1'],
        ]));
        File::put($this->tempDir.'/irregular_nouns.json', json_encode([
            ['singular' => 'child', 'plural' => 'children', 'cefr_level' => 'A1'],
        ]));
        File::put($this->tempDir.'/irregular_adjectives.json', json_encode([
            ['base' => 'good', 'comparative' => 'better', 'superlative' => 'best', 'derived_adverb' => 'well', 'cefr_level' => 'A1'],
        ]));
        File::put($this->tempDir.'/demonstratives_and_pronouns.json', json_encode([
            'pronouns' => [
                ['subject' => 'he', 'object' => 'him', 'possessive_adjective' => 'his', 'possessive_pronoun' => 'his', 'reflexive' => 'himself'],
            ],
            'demonstratives' => [
                ['singular' => 'this', 'plural' => 'these', 'distance' => 'near'],
            ],
        ]));
    }

    private function admin(): User
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        return $admin;
    }

    public function test_page_renders(): void
    {
        $this->admin();
        $this->get(route('admin.data-transformation.index'))->assertOk();
    }

    public function test_verb_can_be_added(): void
    {
        $this->admin();

        Livewire::test(DataTransformationIndex::class)
            ->call('openCreate', 'verbs')
            ->set('form.base_v1', 'Eat')
            ->set('form.past_simple_v2', 'Ate')
            ->set('form.past_participle_v3', 'Eaten')
            ->set('form.cefr_level', 'A1')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('eat');

        $data = json_decode(File::get($this->tempDir.'/irregular_verbs.json'), true);
        $this->assertCount(2, $data);
        $this->assertSame([
            'base_v1' => 'eat',
            'past_simple_v2' => 'ate',
            'past_participle_v3' => 'eaten',
            'cefr_level' => 'A1',
        ], $data[1]);
    }

    public function test_verb_validation_requires_all_fields(): void
    {
        $this->admin();

        Livewire::test(DataTransformationIndex::class)
            ->call('openCreate', 'verbs')
            ->set('form.base_v1', 'take')
            ->call('save')
            ->assertHasErrors([
                'form.past_simple_v2' => 'required',
                'form.past_participle_v3' => 'required',
                'form.cefr_level' => 'required',
            ]);
    }

    public function test_verb_can_be_edited(): void
    {
        $this->admin();

        Livewire::test(DataTransformationIndex::class)
            ->call('openEdit', 0, 'verbs')
            ->assertSet('form.base_v1', 'go')
            ->set('form.cefr_level', 'A2')
            ->call('save')
            ->assertHasNoErrors();

        $data = json_decode(File::get($this->tempDir.'/irregular_verbs.json'), true);
        $this->assertSame('A2', $data[0]['cefr_level']);
    }

    public function test_verb_can_be_deleted(): void
    {
        $this->admin();

        Livewire::test(DataTransformationIndex::class)
            ->call('delete', 0, 'verbs');

        $data = json_decode(File::get($this->tempDir.'/irregular_verbs.json'), true);
        $this->assertSame([], $data);
    }

    public function test_noun_can_be_added(): void
    {
        $this->admin();

        Livewire::test(DataTransformationIndex::class)
            ->call('openCreate', 'nouns')
            ->set('form.singular', 'Person')
            ->set('form.plural', 'People')
            ->set('form.cefr_level', 'A1')
            ->call('save')
            ->assertHasNoErrors();

        $data = json_decode(File::get($this->tempDir.'/irregular_nouns.json'), true);
        $this->assertCount(2, $data);
        $this->assertSame('people', $data[1]['plural']);
    }

    public function test_adjective_can_be_added(): void
    {
        $this->admin();

        Livewire::test(DataTransformationIndex::class)
            ->call('openCreate', 'adjectives')
            ->set('form.base', 'Bad')
            ->set('form.comparative', 'Worse')
            ->set('form.superlative', 'Worst')
            ->set('form.derived_adverb', 'Badly')
            ->set('form.cefr_level', 'A1')
            ->call('save')
            ->assertHasNoErrors();

        $data = json_decode(File::get($this->tempDir.'/irregular_adjectives.json'), true);
        $this->assertCount(2, $data);
        $this->assertSame('bad', $data[1]['base']);
    }

    public function test_pronoun_and_demonstrative_preserved_in_same_file(): void
    {
        $this->admin();

        // Tambah pronoun -> demonstratives tidak hilang.
        Livewire::test(DataTransformationIndex::class)
            ->call('openCreate', 'pronouns')
            ->set('form.subject', 'She')
            ->set('form.object', 'Her')
            ->set('form.possessive_adjective', 'Her')
            ->set('form.possessive_pronoun', 'Hers')
            ->set('form.reflexive', 'Herself')
            ->call('save')
            ->assertHasNoErrors();

        $file = json_decode(File::get($this->tempDir.'/demonstratives_and_pronouns.json'), true);
        $this->assertCount(2, $file['pronouns']);
        $this->assertCount(1, $file['demonstratives']);
        $this->assertSame('she', $file['pronouns'][1]['subject']);

        // Tambah demonstrative -> pronouns tidak hilang.
        Livewire::test(DataTransformationIndex::class)
            ->call('openCreate', 'demonstratives')
            ->set('form.singular', 'That')
            ->set('form.plural', 'Those')
            ->set('form.distance', 'Far')
            ->call('save')
            ->assertHasNoErrors();

        $file = json_decode(File::get($this->tempDir.'/demonstratives_and_pronouns.json'), true);
        $this->assertCount(2, $file['pronouns']);
        $this->assertCount(2, $file['demonstratives']);
        $this->assertSame('those', $file['demonstratives'][1]['plural']);
    }
}
