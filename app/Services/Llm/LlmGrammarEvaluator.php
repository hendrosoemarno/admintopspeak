<?php

namespace App\Services\Llm;

use App\Exceptions\GrammarEvaluationUnavailableException;

/**
 * Evaluasi grammar transkrip user secara langsung via LLM.
 * LLM menilai apakah kalimat benar, dan bila salah memberikan versi koreksinya.
 *
 * Mengembalikan:
 *   - is_correct: bool (benar => grammar 1, salah => grammar 0)
 *   - corrected_sentence: ?string (versi benar bila ada kesalahan)
 *   - error_description: ?string (penjelasan kesalahan)
 *
 * Bila LLM tidak dikonfigurasi / gagal => lempar GrammarEvaluationUnavailableException
 * (diterjemahkan menjadi HTTP 422, evaluasi diblokir).
 */
class LlmGrammarEvaluator
{
    public function __construct(private readonly LlmClient $client)
    {
    }

    /**
     * @return array{is_correct: bool, corrected_sentence: ?string, error_description: ?string}
     */
    public function evaluate(string $transcript, ?string $question = null): array
    {
        $raw = $this->client->chatJson($this->systemPrompt(), $this->userPrompt($transcript, $question));

        if (! is_array($raw)) {
            throw GrammarEvaluationUnavailableException::forUnavailable();
        }

        return $this->fromRaw($raw);
    }

    /**
     * Bangun pasangan prompt system/user untuk evaluasi grammar tanpa memanggil LLM.
     *
     * @return array{0: string, 1: string}
     */
    public function prompt(string $transcript, ?string $question = null): array
    {
        return [$this->systemPrompt(), $this->userPrompt($transcript, $question)];
    }

    /**
     * Evaluasi beberapa transkrip secara paralel.
     *
     * @param  array<int, array{0: string, 1: ?string}>  $items  pasangan [transcript, question]
     * @return array<int, array{is_correct: bool, corrected_sentence: ?string, error_description: ?string}>
     */
    public function evaluateMany(array $items): array
    {
        if (! $this->client->enabled()) {
            throw GrammarEvaluationUnavailableException::forUnavailable();
        }

        $calls = array_map(
            fn (array $item) => $this->prompt($item[0], $item[1]),
            $items,
        );

        $raws = $this->client->chatJsonMany($calls);

        $results = [];

        foreach ($raws as $raw) {
            if (! is_array($raw)) {
                throw GrammarEvaluationUnavailableException::forUnavailable();
            }

            $results[] = $this->fromRaw($raw);
        }

        return $results;
    }

    public function systemPrompt(): string
    {
        return 'You are an expert English grammar checker for an AI speaking tutor app. '
            .'Given a learner sentence and the question it answers, determine whether the learner '
            .'produced a grammatically correct, complete, and coherent sentence. '
            .'Respond with ONLY valid JSON with these keys: '
            .'{"is_correct": true|false, "corrected_sentence": "...", "error_description": "..."}. '
            .'is_correct: true if the sentence is a complete, coherent sentence with NO grammar mistakes. '
            .'is_correct: false if the sentence has at least one grammar mistake OR is NOT a complete, coherent sentence '
            .'(for example a sentence fragment, a truncated utterance, an incoherent combination of words, '
            .'or words that do not form a meaningful sentence on their own), even if there is no "classic" '
            .'grammar error such as wrong tense. '
            .'corrected_sentence: if is_correct is false, rewrite the learner sentence into the correct, complete version '
            .'(fix ALL mistakes, complete the fragment into a proper sentence if possible, keep the same intended meaning '
            .'and context, capitalize the first letter and end with a period). '
            .'If is_correct is true or the intended meaning cannot be reconstructed, set corrected_sentence to "". '
            .'error_description: short explanation of the mistake(s) in the learner\'s language if is_correct is false, '
            .'otherwise "". '
            .'Use the question only to understand context and intent. '
            .'Do NOT judge vocabulary richness, length, or style — a short but complete sentence like "My hobby is reading." '
            .'is correct. '
            .'IMPORTANT — IGNORE formatting issues: do NOT flag capitalization (including lowercase "i"), '
            .'missing punctuation, missing period, or missing capital letter as a grammar error. '
            .'These are typing/STT artifacts, not grammar mistakes. '
            .'If the sentence is grammatically correct apart from capitalization/punctuation, set is_correct to true '
            .'and corrected_sentence to "". '
            .'Only mark is_correct false for REAL issues such as wrong tense, subject-verb disagreement, '
            .'wrong word order, wrong preposition, incorrect pluralization, missing/incorrect articles, '
            .'missing auxiliary/main verb, or an incomplete/incoherent fragment that does not express a complete thought '
            .'(for example "my mind will be").';
    }

    public function userPrompt(string $transcript, ?string $question = null): string
    {
        return 'Question: "'.($question ?: '')."\"\n"
            ."Learner sentence: \"{$transcript}\"\n"
            .'Determine grammar correctness and produce the JSON.';
    }

    /**
     * Normalisasi respons mentah LLM menjadi struktur evaluasi grammar.
     *
     * @return array{is_correct: bool, corrected_sentence: ?string, error_description: ?string}
     */
    public function fromRaw(array $raw): array
    {
        $isCorrect = (bool) ($raw['is_correct'] ?? false);
        $correctedSentence = trim((string) ($raw['corrected_sentence'] ?? ''));
        $errorDescription = trim((string) ($raw['error_description'] ?? ''));

        return [
            'is_correct' => $isCorrect,
            'corrected_sentence' => $isCorrect ? null : ($correctedSentence !== '' ? $correctedSentence : null),
            'error_description' => $isCorrect ? null : ($errorDescription !== '' ? $errorDescription : null),
        ];
    }
}
