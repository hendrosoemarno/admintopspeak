<?php

namespace App\Services\Grammar;

use App\Models\VocabularyBank;
use App\Repositories\GrammarDataRepository;

/**
 * Resolver placeholder dalam regex_pattern grammar rules.
 *
 * Pola regex dapat memakai placeholder semantik yang di-resolve ke daftar kata
 * dari Data Transformation (file JSON) dan Vocabulary Bank (tabel DB), misalnya:
 *
 *   /\b{verb}\s+{adverb_of_frequency}\b/i
 *
 *   {v1} / {v2} / {v3}              -> irregular_verbs.json (base/past simple/past participle)
 *   {singular_noun} / {plural_noun} -> irregular_nouns.json
 *   {comparative} / {superlative} / {derived_adverb} -> irregular_adjectives.json
 *   {subject_pronoun} / {object_pronoun} / {possessive_adjective}
 *   {possessive_pronoun} / {reflexive} -> demonstratives_and_pronouns.json
 *   {demonstrative_singular} / {demonstrative_plural}
 *   {verb} / {noun} / {adjective} / {adverb}            -> vocabulary_bank (part_of_speech)
 *   {adverb_of_frequency}           -> vocabulary_bank (part_of_speech=adverb, topic=Adverbs of Frequency)
 */
class GrammarRulePatternResolver
{
    /** Cache hasil resolve per nama placeholder (per request). */
    protected array $cache = [];

    public function __construct(private readonly GrammarDataRepository $data)
    {
    }

    public function resolve(string $pattern): string
    {
        return preg_replace_callback('/\{([a-z0-9_]+)\}/i', function (array $m) {
            $words = $this->wordsFor(strtolower($m[1]));

            if ($words === []) {
                // Placeholder tidak dikenal / kosong -> pola tidak akan match apa pun.
                return '(?!)';
            }

            $escaped = array_map(fn (string $w) => preg_quote($w, '/'), $words);

            return '(?:'.implode('|', $escaped).')';
        }, $pattern);
    }

    public function supportedPlaceholders(): array
    {
        return [
            '{verb}' => 'Kata kerja dari Vocabulary Bank (part_of_speech = verb)',
            '{noun}' => 'Kata benda dari Vocabulary Bank',
            '{adjective}' => 'Kata sifat dari Vocabulary Bank',
            '{adverb}' => 'Kata keterangan dari Vocabulary Bank',
            '{adverb_of_frequency}' => 'Kata keterangan frekuensi (always, usually, never, dsb.)',
            '{modal}' => 'Modal verb dari Vocabulary Bank (can, could, should, must, dsb.)',
            '{auxiliary}' => 'Auxiliary verb dari Vocabulary Bank (am, is, was, has, did, dsb.)',
            '{article}' => 'Article dari Vocabulary Bank (a, an, the)',
            '{country}' => 'Nama negara dari Vocabulary Bank (Indonesia, Japan, dll.)',
            '{language}' => 'Nama bahasa dari Vocabulary Bank (Indonesian, English, dll.)',
            '{v1}' => 'Base verb (V1) dari irregular_verbs.json',
            '{v2}' => 'Past simple (V2) dari irregular_verbs.json',
            '{v3}' => 'Past participle (V3) dari irregular_verbs.json',
            '{singular_noun}' => 'Noun singular irregular dari irregular_nouns.json',
            '{plural_noun}' => 'Noun plural irregular dari irregular_nouns.json',
            '{comparative}' => 'Bentuk comparative dari irregular_adjectives.json',
            '{superlative}' => 'Bentuk superlative dari irregular_adjectives.json',
            '{derived_adverb}' => 'Adverb turunan dari irregular_adjectives.json',
            '{subject_pronoun}' => 'Pronoun subjek (i, you, he, ...)',
            '{subject_pronoun_no_i}' => 'Pronoun subjek selain "i" (you, he, she, it, we, they)',
            '{object_pronoun}' => 'Pronoun objek (me, him, ...)',
            '{possessive_adjective}' => 'Possessive adjective (my, his, ...)',
            '{possessive_pronoun}' => 'Possessive pronoun (mine, hers, ...)',
            '{reflexive}' => 'Reflexive pronoun (myself, himself, ...)',
            '{demonstrative_singular}' => 'Demonstrative singular (this, that)',
            '{demonstrative_plural}' => 'Demonstrative plural (these, those)',
        ];
    }

    private function wordsFor(string $placeholder): array
    {
        if (isset($this->cache[$placeholder])) {
            return $this->cache[$placeholder];
        }

        $words = match ($placeholder) {
            'v1' => $this->column($this->verbs(), 'base_v1'),
            'v2' => $this->column($this->verbs(), 'past_simple_v2'),
            'v3' => $this->column($this->verbs(), 'past_participle_v3'),
            'singular_noun' => $this->column($this->nouns(), 'singular'),
            'plural_noun' => $this->column($this->nouns(), 'plural'),
            'comparative' => $this->column($this->adjectives(), 'comparative'),
            'superlative' => $this->column($this->adjectives(), 'superlative'),
            'derived_adverb' => $this->column($this->adjectives(), 'derived_adverb'),
            'subject_pronoun' => $this->pronounColumn('subject'),
            'subject_pronoun_no_i' => $this->pronounColumn('subject', exclude: ['i']),
            'object_pronoun' => $this->pronounColumn('object'),
            'possessive_adjective' => $this->pronounColumn('possessive_adjective'),
            'possessive_pronoun' => $this->pronounColumn('possessive_pronoun'),
            'reflexive' => $this->pronounColumn('reflexive'),
            'demonstrative_singular' => $this->demonstrativeColumn('singular'),
            'demonstrative_plural' => $this->demonstrativeColumn('plural'),
            'verb' => $this->vocab('verb'),
            'noun' => $this->vocab('noun'),
            'adjective' => $this->vocab('adjective'),
            'adverb' => $this->vocab('adverb'),
            'adverb_of_frequency' => $this->vocab('adverb', 'Adverbs of Frequency'),
            'modal' => $this->vocab('modal'),
            'auxiliary' => $this->vocab('auxiliary'),
            'article' => $this->vocab('article'),
            'country' => $this->vocab('country'),
            'language' => $this->vocab('language'),
            default => [],
        };

        return $this->cache[$placeholder] = array_values(array_unique(array_filter(array_map(
            fn ($w) => is_string($w) ? mb_strtolower(trim($w)) : '',
            $words,
        ))));
    }

    private function verbs(): array
    {
        return $this->data->read('irregular_verbs.json');
    }

    private function nouns(): array
    {
        return $this->data->read('irregular_nouns.json');
    }

    private function adjectives(): array
    {
        return $this->data->read('irregular_adjectives.json');
    }

    private function pronounData(): array
    {
        $file = $this->data->read('demonstratives_and_pronouns.json');

        return $file['pronouns'] ?? [];
    }

    private function demonstrativeData(): array
    {
        $file = $this->data->read('demonstratives_and_pronouns.json');

        return $file['demonstratives'] ?? [];
    }

    private function column(array $rows, string $key): array
    {
        return array_values(array_column($rows, $key));
    }

    private function pronounColumn(string $key, array $exclude = []): array
    {
        return array_values(array_filter(
            array_column($this->pronounData(), $key),
            fn ($v) => ! in_array(mb_strtolower((string) $v), $exclude, true),
        ));
    }

    private function demonstrativeColumn(string $key): array
    {
        return array_values(array_column($this->demonstrativeData(), $key));
    }

    private function vocab(string $pos, ?string $topic = null): array
    {
        $query = VocabularyBank::query()->where('part_of_speech', $pos);

        if ($topic !== null) {
            $query->where('topic_category', $topic);
        }

        return $query->pluck('word')->all();
    }
}
