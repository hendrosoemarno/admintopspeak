<?php

namespace Tests\Unit;

use App\Models\GrammarRule;
use App\Models\VocabularyBank;
use App\Repositories\GrammarRuleRepository;
use App\Services\Grammar\GrammarRulePatternResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrammarRulePatternResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_verb_placeholder_resolves_from_vocabulary_bank(): void
    {
        VocabularyBank::create(['word' => 'go', 'part_of_speech' => 'verb', 'cefr_level' => 'A1', 'topic_category' => 'General']);
        VocabularyBank::create(['word' => 'eat', 'part_of_speech' => 'verb', 'cefr_level' => 'A1', 'topic_category' => 'General']);
        VocabularyBank::create(['word' => 'table', 'part_of_speech' => 'noun', 'cefr_level' => 'A1', 'topic_category' => 'General']);

        $resolver = app(GrammarRulePatternResolver::class);
        $resolved = $resolver->resolve('/\b{verb}\b/i');

        $this->assertStringContainsString('(?:go|eat)', $resolved);
        $this->assertStringNotContainsString('table', $resolved);
    }

    public function test_adverb_of_frequency_placeholder_resolves_only_frequency_adverbs(): void
    {
        VocabularyBank::create(['word' => 'always', 'part_of_speech' => 'adverb', 'cefr_level' => 'A1', 'topic_category' => 'Adverbs of Frequency']);
        VocabularyBank::create(['word' => 'never', 'part_of_speech' => 'adverb', 'cefr_level' => 'A1', 'topic_category' => 'Adverbs of Frequency']);
        VocabularyBank::create(['word' => 'quickly', 'part_of_speech' => 'adverb', 'cefr_level' => 'A2', 'topic_category' => 'Manner']);

        $resolver = app(GrammarRulePatternResolver::class);
        $resolved = $resolver->resolve('/\b{adverb_of_frequency}\b/i');

        $this->assertStringContainsString('always', $resolved);
        $this->assertStringContainsString('never', $resolved);
        $this->assertStringNotContainsString('quickly', $resolved);
    }

    public function test_v1_placeholder_resolves_from_irregular_verbs_json(): void
    {
        $resolver = app(GrammarRulePatternResolver::class);
        $resolved = $resolver->resolve('/\b{v1}\b/i');

        // Data transformation berisi see & go (dari SPEC / seeder).
        $this->assertStringContainsString('see', $resolved);
        $this->assertStringContainsString('go', $resolved);
    }

    public function test_plural_noun_placeholder_resolves_from_irregular_nouns_json(): void
    {
        $resolver = app(GrammarRulePatternResolver::class);
        $resolved = $resolver->resolve('/\b{plural_noun}\b/i');

        $this->assertStringContainsString('children', $resolved);
        $this->assertStringContainsString('people', $resolved);
    }

    public function test_subject_pronoun_placeholder_resolves_from_json(): void
    {
        $resolver = app(GrammarRulePatternResolver::class);
        $resolved = $resolver->resolve('/\b{subject_pronoun}\b/i');

        $this->assertStringContainsString('he', $resolved);
        $this->assertStringContainsString('they', $resolved);
    }

    public function test_subject_pronoun_no_i_excludes_i(): void
    {
        $resolver = app(GrammarRulePatternResolver::class);
        $resolved = $resolver->resolve('/\b{subject_pronoun_no_i}\s+am\b/i');

        $this->assertStringContainsString('he', $resolved);
        $this->assertStringNotContainsString('(?:\b)?i\b', $resolved);
        $this->assertStringNotContainsString('i)', $resolved);

        // "I am" (benar) tidak boleh match; "he am" (salah) harus match.
        $this->assertSame(0, @preg_match($resolved, 'I am happy'));
        $this->assertSame(1, @preg_match($resolved, 'he am happy'));
    }

    public function test_detect_violations_resolves_placeholder_before_matching(): void
    {
        VocabularyBank::create(['word' => 'go', 'part_of_speech' => 'verb', 'cefr_level' => 'A1', 'topic_category' => 'General']);
        VocabularyBank::create(['word' => 'always', 'part_of_speech' => 'adverb', 'cefr_level' => 'A1', 'topic_category' => 'Adverbs of Frequency']);

        GrammarRule::create([
            'rule_code' => 'ADV_TEST',
            'category' => 'Adverb Position',
            'cefr_level' => 'A1',
            'regex_pattern' => '/\b{verb}\s+{adverb_of_frequency}\b/i',
            'description' => 'Adverb harus sebelum verb.',
            'is_active' => true,
        ]);

        $repo = app(GrammarRuleRepository::class);

        // "I go always" -> verb lalu adverb -> violation.
        $violations = $repo->detectViolations('I go always');
        $this->assertCount(1, $violations);
        $this->assertSame('ADV_TEST', $violations[0]['rule_code']);

        // "I always go" -> benar, tidak ada violation.
        $this->assertSame([], $repo->detectViolations('I always go'));
    }

    public function test_unknown_placeholder_never_matches(): void
    {
        $resolver = app(GrammarRulePatternResolver::class);
        $resolved = $resolver->resolve('/\b{unknown_thing}\b/i');

        $this->assertSame(0, @preg_match($resolved, 'anything at all'));
    }

    public function test_matches_positive_rule_with_placeholder(): void
    {
        VocabularyBank::create(['word' => 'wake', 'part_of_speech' => 'verb', 'cefr_level' => 'A1', 'topic_category' => 'General']);

        GrammarRule::create([
            'rule_code' => 'POS_OK',
            'category' => 'Correct',
            'rule_type' => 'positive',
            'source' => 'manual',
            'cefr_level' => 'A1',
            'regex_pattern' => '/\b{verb}\s+up\b/i',
            'description' => 'Wake up = benar.',
            'is_active' => true,
        ]);

        $repo = app(GrammarRuleRepository::class);

        $this->assertTrue($repo->matchesPositiveRule('I wake up at five'));
        $this->assertFalse($repo->matchesPositiveRule('I sleep at night'));
    }
}
