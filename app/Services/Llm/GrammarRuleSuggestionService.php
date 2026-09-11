<?php

namespace App\Services\Llm;

use App\Enums\CefrLevel;
use App\Services\Grammar\GrammarRulePatternResolver;

/**
 * Menghasilkan usulan Grammar Rule dari sebuah kalimat user via LLM.
 * LLM (1) menulis ulang kalimat user menjadi versi yang BENAR (correct_sentence),
 * lalu (2) membangun regex sebagai POSITIVE rule yang match struktur benar tersebut.
 * Gagal / tidak terkonfigurasi => fallback ke saran heuristic (suggested_regex).
 *
 * Untuk detected_error generik (kalimat tidak tertangkap rule mana pun),
 * LLM tetap diminta menganalisis kalimat sendiri. Rule yang dihasilkan WAJIB
 * match correct_sentence (anti-halusinasi / anti-pola nyasar).
 */
class GrammarRuleSuggestionService
{
    public function __construct(
        private readonly LlmClient $client,
        private readonly GrammarRulePatternResolver $resolver,
    ) {
    }

    /**
     * @param string      $userInput       Kalimat asli user.
     * @param string|null $detectedError   Deskripsi error (jika ada).
     * @param string|null $suggestedRegex  Regex heuristic hasil diff.
     * @param bool        $isKnownError    True bila sudah ada rule error yang match.
     *
     * @return array{regex_pattern:string, rule_type:string, category:string, cefr_level:string, description:string, correct_sentence:string, source:string}|null
     *         Null bila tidak bisa dibuatkan rule yang valid.
     */
    public function suggest(string $userInput, ?string $detectedError, ?string $suggestedRegex, bool $isKnownError = false): ?array
    {
        $generic = $this->isGenericError($detectedError);

        $fromLlm = $this->fromLlm($userInput, $detectedError, $suggestedRegex, $isKnownError, $generic);

        if ($fromLlm !== null) {
            return $fromLlm;
        }

        // Error generik tanpa konteks: LLM gagal/tidak aktif -> jangan buat rule
        // dari seluruh kalimat (bukan pola nyata). Lebih baik ditolak manual.
        if ($generic) {
            return null;
        }

        return $this->fallback($userInput, $detectedError, $suggestedRegex, $isKnownError);
    }

    /**
     * Deteksi error deskriptif yang tidak berguna (generik / kosong) sehingga
     * LLM tidak perlu dimintai pendapat karena tidak ada konteks kesalahannya.
     */
    private function isGenericError(?string $detectedError): bool
    {
        $value = mb_strtolower(trim((string) $detectedError));

        if ($value === '') {
            return true;
        }

        $genericMarkers = [
            'tidak tertangkap',
            'belum ada pola',
            'tidak ada error',
            'none',
            'unknown',
        ];

        foreach ($genericMarkers as $marker) {
            if (str_contains($value, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function fromLlm(string $userInput, ?string $detectedError, ?string $suggestedRegex, bool $isKnownError, bool $generic): ?array
    {
        // LLM hanya dipakai bila diaktifkan di App Configuration (toggle + api key).
        if (! $this->client->enabled()) {
            return null;
        }

        $system = 'You are an expert English grammar rule engineer for an AI speaking tutor app. '
            .'Given a learner sentence, your job is to (1) rewrite it into the grammatically CORRECT version, '
            .'and (2) build a POSITIVE grammar rule pattern from that correct version. '
            .'Respond with ONLY valid JSON with these keys: '
            .'{"correct_sentence": "...", "regex_pattern": "...", "category": "...", "cefr_level": "A1..C2", "description": "..."}. '
            .'correct_sentence: rewrite the LEARNER SENTENCE into the grammatically correct version, keeping the same '
            .'meaning and context. Fix ONLY the mistake (if any); if the sentence is already correct, give it back '
            .'unchanged. Capitalize the first letter and end with a period. '
            .'regex_pattern: a POSITIVE pattern that matches the CORRECT structure used in correct_sentence '
            .'(the correct form, e.g. /\\b(speak|learn|study)\\s+{language}\\b/i for "I can speak Indonesian"). '
            .'It will be stored as a rule_type "positive" rule: when a learner produces this correct structure, '
            .'their grammar is considered correct. '
            .'regex_pattern must be a valid PHP/PCRE regex wrapped in delimiters (e.g. /\\bpattern\\b/i). '
            .'Keep the pattern general enough to catch similar correct sentences, not just the exact words. '
            .'cefr_level must be one of A1,A2,B1,B2,C1,C2. '
            .PHP_EOL
            .'PLACEHOLDER SYSTEM (very important): the app auto-expands semantic placeholders into real word lists at runtime, '
            .'so instead of hardcoding specific words you MUST prefer placeholders wherever they fit. Available placeholders: '
            .'{verb} {noun} {adjective} {adverb} {adverb_of_frequency} {v1} {v2} {v3} {singular_noun} {plural_noun} '
            .'{comparative} {superlative} {derived_adverb} {subject_pronoun} {subject_pronoun_no_i} {object_pronoun} '
            .'{possessive_adjective} {possessive_pronoun} {reflexive} {demonstrative_singular} {demonstrative_plural} '
            .'{modal} {auxiliary} {article} {country} {language}. '
            .'Rules for placeholders: '
            .'{v1} = base verb, {v2} = past simple, {v3} = past participle (from irregular verbs list), '
            .'{singular_noun}/{plural_noun} = irregular noun singular/plural, '
            .'{subject_pronoun_no_i} = subject pronoun excluding "i", '
            .'{country} = country name (Indonesia, Japan), {language} = language name (Indonesian, English). '
            .'Examples: '
            .'For "I can speak Indonesian" use /\\b(speak|learn|study)\\s+{language}\\b/i — do NOT hardcode "Indonesian". '
            .'For "I went to school yesterday" use /yesterday\\s+{v2}/i. '
            .'For "I have two children" use /\\b(?:two|three|many|some|several)\\s+{plural_noun}\\b/i. '
            .'For "She usually goes to school" use /\\b(she|he|it)\\s+{adverb_of_frequency}\\s+{verb}\\b/i. '
            .'If a placeholder fits a word category, USE IT instead of listing literal words. '
            .'Only fall back to literal words when no placeholder covers the needed category. '
            .'Placeholder must stay inside the regex pattern exactly as written (e.g. {v2}, not a literal past word). '
            .PHP_EOL
            .'CRITICAL VALIDATION: the final regex_pattern MUST actually MATCH correct_sentence '
            .'(after placeholders are expanded to real words). Build the pattern from the CORRECT form, '
            .'not from the learner\'s wrong form. Do NOT invent a pattern about words that are not in correct_sentence.';

        $genericContext = $generic
            ? "The rule engine did NOT identify a specific error for this sentence; it simply did not match any active rule. "
                .'Analyze the learner sentence yourself, produce the correct version, and build a positive pattern from it. '
                .'If the sentence is already grammatically correct, correct_sentence equals the input and the positive '
                .'pattern matches it as-is.'
            : 'A specific error was detected; correct it, then build a positive pattern from the corrected version.';

        $user = "Learner sentence: \"{$userInput}\"\n"
            .'Detected error description: '.($detectedError ?: '(none)')."\n"
            .'Heuristic suggested regex: '.($suggestedRegex ?: '(none)')."\n"
            .'Already matches an error rule: '.($isKnownError ? 'yes' : 'no')."\n"
            .$genericContext."\n"
            .'Produce the JSON. First write correct_sentence, then build regex_pattern as a positive pattern '
            .'matching correct_sentence, using placeholders like {v2}, {plural_noun}, {language} whenever applicable.';

        $raw = $this->client->chatJson($system, $user);

        if (! is_array($raw)) {
            return null;
        }

        $correctSentence = trim((string) ($raw['correct_sentence'] ?? ''));

        if ($correctSentence === '') {
            return null;
        }

        $regex = $this->cleanRegex((string) ($raw['regex_pattern'] ?? ''));

        if ($regex === null) {
            return null;
        }

        // Rule berbasis kalimat benar -> selalu positive.
        $ruleType = 'positive';

        // Guard anti-halusinasi: pola WAJIB match kalimat benar setelah placeholder di-resolve.
        if (! $this->matchesInput($regex, $correctSentence)) {
            return null;
        }

        $cefr = strtoupper((string) ($raw['cefr_level'] ?? 'A1'));

        return [
            'regex_pattern' => $regex,
            'rule_type' => $ruleType,
            'category' => trim((string) ($raw['category'] ?? 'AI Generated')),
            'cefr_level' => in_array($cefr, array_map(fn ($l) => $l->value, CefrLevel::cases()), true) ? $cefr : 'A1',
            'description' => trim((string) ($raw['description'] ?? '')), // keep original text
            'correct_sentence' => $correctSentence,
            'source' => 'llm',
            'llm_meta' => json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    private function matchesInput(string $regex, string $input): bool
    {
        try {
            $resolved = $this->resolver->resolve($regex);

            return preg_match($resolved, $input) === 1;
        } catch (\Throwable) {
            return false;
        }
    }

    private function fallback(string $userInput, ?string $detectedError, ?string $suggestedRegex, bool $isKnownError): array
    {
        $regex = $suggestedRegex ?: $this->escapeToRegex($userInput);

        return [
            'regex_pattern' => $regex,
            'rule_type' => 'error',
            'category' => $isKnownError ? 'AI Generated' : 'User Suggested',
            'cefr_level' => 'A1',
            'description' => $detectedError ?: "Pola dari: \"{$userInput}\"",
            'correct_sentence' => '',
            'source' => $suggestedRegex ? 'user' : 'manual',
            'llm_meta' => null,
        ];
    }

    private function cleanRegex(string $regex): ?string
    {
        $regex = trim($regex);

        if ($regex === '') {
            return null;
        }

        // Deteksi delimiter: pola diawali karakter delimiter dan memiliki
        // penutup yang sama (boleh diikuti flag seperti i, m, s, u).
        $first = $regex[0] ?? '';
        $delimiters = ['/', '~', '#', '%', '`'];
        $hasDelimiter = in_array($first, $delimiters, true)
            && $this->hasClosingDelimiter($regex, $first);

        if (! $hasDelimiter) {
            $regex = '/'.$regex.'/i';
        }

        // Validasi regex benar-benar bisa dikompilasi.
        if (@preg_match($regex, '') === false) {
            return null;
        }

        return $regex;
    }

    private function hasClosingDelimiter(string $regex, string $delimiter): bool
    {
        $last = strrpos($regex, $delimiter);

        if ($last === false || $last === 0) {
            return false;
        }

        $flags = substr($regex, $last + 1);

        return $flags === '' || preg_match('/^[imsxuADSUXJ]*$/', $flags) === 1;
    }

    private function escapeToRegex(string $text): string
    {
        return '/'.preg_quote(trim($text), '/').'/i';
    }
}
