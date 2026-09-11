<?php

namespace App\Services\Grammar;

/**
 * Word Transformation & Local Inflection Mapping Engine.
 *
 * Menyediakan substitusi perubahan bentuk kata (inflection/declension)
 * secara deterministik dengan O(1) memory lookup dari file JSON di
 * `resources/data/grammar/`, sebelum atau berbarengan dengan pemanggilan
 * CorrectiveTextService (LLM).
 *
 * Data:
 *  - irregular_verbs.json
 *  - irregular_nouns.json
 *  - irregular_adjectives.json
 *  - demonstratives_and_pronouns.json
 */
class WordTransformationService
{
    protected array $verbs = [];

    protected array $nouns = [];

    protected array $adjectives = [];

    protected array $pronouns = [];

    protected array $demonstratives = [];

    public function __construct()
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $basePath = resource_path('data/grammar/');

        $verbsData = json_decode(file_get_contents($basePath.'irregular_verbs.json'), true);
        foreach ($verbsData as $item) {
            $this->verbs[strtolower($item['base_v1'])] = [
                'past_simple_v2' => $item['past_simple_v2'],
                'past_participle_v3' => $item['past_participle_v3'],
                'cefr_level' => $item['cefr_level'] ?? null,
            ];
        }

        $nounsData = json_decode(file_get_contents($basePath.'irregular_nouns.json'), true);
        foreach ($nounsData as $item) {
            $this->nouns[strtolower($item['singular'])] = $item['plural'];
        }

        $adjData = json_decode(file_get_contents($basePath.'irregular_adjectives.json'), true);
        foreach ($adjData as $item) {
            $this->adjectives[strtolower($item['base'])] = [
                'comparative' => $item['comparative'],
                'superlative' => $item['superlative'],
                'derived_adverb' => $item['derived_adverb'] ?? null,
                'cefr_level' => $item['cefr_level'] ?? null,
            ];
        }

        $pronounDemoData = json_decode(file_get_contents($basePath.'demonstratives_and_pronouns.json'), true);
        foreach ($pronounDemoData['pronouns'] as $item) {
            $this->pronouns[strtolower($item['subject'])] = $item;
        }
        foreach ($pronounDemoData['demonstratives'] as $item) {
            $this->demonstratives[strtolower($item['singular'])] = $item;
        }
    }

    /**
     * Ubah kata kerja V1 menjadi past simple (V2).
     * Tidak terdaftar => fallback regular (-ed/-d/-ied).
     */
    public function getPastSimple(string $v1Verb): string
    {
        $verb = strtolower($v1Verb);
        if (isset($this->verbs[$verb])) {
            return $this->verbs[$verb]['past_simple_v2'];
        }

        if (str_ends_with($verb, 'e')) {
            return $verb.'d';
        }
        if (str_ends_with($verb, 'y') && ! preg_match('/[aeiou]y$/', $verb)) {
            return substr($verb, 0, -1).'ied';
        }

        return $verb.'ed';
    }

    /**
     * Past simple (V2) untuk kata kerja IRREGULAR saja; null jika bukan
     * kata kerja irregular yang dikenal. Dipakai untuk substitusi aman
     * tanpa merusak kata lain (noun/adjective).
     */
    public function knownPastSimple(string $v1Verb): ?string
    {
        $verb = strtolower($v1Verb);

        return $this->verbs[$verb]['past_simple_v2'] ?? null;
    }

    public function getPastParticiple(string $v1Verb): string
    {
        $verb = strtolower($v1Verb);
        if (isset($this->verbs[$verb])) {
            return $this->verbs[$verb]['past_participle_v3'];
        }

        if (str_ends_with($verb, 'e')) {
            return $verb.'d';
        }
        if (str_ends_with($verb, 'y') && ! preg_match('/[aeiou]y$/', $verb)) {
            return substr($verb, 0, -1).'ied';
        }

        return $verb.'ed';
    }

    /**
     * Ubah noun singular menjadi plural.
     * Tidak terdaftar => fallback regular (-s/-es/-ies).
     */
    public function getPluralNoun(string $singularNoun): string
    {
        $noun = strtolower($singularNoun);
        if (isset($this->nouns[$noun])) {
            return $this->nouns[$noun];
        }

        if (preg_match('/(s|x|z|ch|sh)$/', $noun)) {
            return $noun.'es';
        }
        if (str_ends_with($noun, 'y') && ! preg_match('/[aeiou]y$/', $noun)) {
            return substr($noun, 0, -1).'ies';
        }

        return $noun.'s';
    }

    public function getComparative(string $base): ?string
    {
        return $this->adjectives[strtolower($base)]['comparative'] ?? null;
    }

    public function getSuperlative(string $base): ?string
    {
        return $this->adjectives[strtolower($base)]['superlative'] ?? null;
    }

    public function getDerivedAdverb(string $base): ?string
    {
        return $this->adjectives[strtolower($base)]['derived_adverb'] ?? null;
    }

    /** Pronoun object (object form) dari bentuk subject, mis. i -> me. */
    public function getObjectPronoun(string $subject): ?string
    {
        return $this->pronouns[strtolower($subject)]['object'] ?? null;
    }

    public function getPossessiveAdjective(string $subject): ?string
    {
        return $this->pronouns[strtolower($subject)]['possessive_adjective'] ?? null;
    }

    public function getPossessivePronoun(string $subject): ?string
    {
        return $this->pronouns[strtolower($subject)]['possessive_pronoun'] ?? null;
    }

    public function getReflexivePronoun(string $subject): ?string
    {
        return $this->pronouns[strtolower($subject)]['reflexive'] ?? null;
    }

    /** Plural demonstrative, mis. this -> these. */
    public function getPluralDemonstrative(string $singular): ?string
    {
        return $this->demonstratives[strtolower($singular)]['plural'] ?? null;
    }
}
