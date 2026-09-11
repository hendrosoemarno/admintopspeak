# Specification: Word Transformation & Local Inflection Mapping Engine

## 1. Overview
Dokumen ini mendefinisikan arsitektur dan spesifikasi data untuk penanganan **Word Transformation Engine** pada platform **TopSpeak**. Engine ini bertugas melacak dan melakukan substitusi perubahan bentuk kata (*inflection / declension*) secara deterministik pada Laravel Service Layer sebelum atau berbarengan dengan pemanggilan `CorrectiveTextService` (LLM).

Mesin ini mencakup 4 kelas kata utama yang mengalami perubahan bentuk:
1. **Verbs** (Tense & Conjugation)
2. **Nouns** (Singular $\rightarrow$ Plural)
3. **Adjectives & Adverbs** (Comparative, Superlative, Derived Adverb)
4. **Pronouns & Demonstratives** (Subject, Object, Possessive, Singular/Plural Pairs)

---

## 2. Directory & Storage Structure

File JSON mapping disimpan pada direktori berikut di lingkungan Laravel:
`resources/data/grammar/`

List file data:
* `irregular_verbs.json`
* `irregular_nouns.json`
* `irregular_adjectives.json`
* `demonstratives_and_pronouns.json`

---

## 3. Data Schemas

### 3.1 `irregular_verbs.json`
Digunakan untuk koreksi kesalahan bentuk kata kerja (misal: `GRAMMAR_A1_PAST_TENSE_MISMATCH`).

```json
[
  {
    "base_v1": "see",
    "past_simple_v2": "saw",
    "past_participle_v3": "seen",
    "cefr_level": "A1"
  },
  {
    "base_v1": "go",
    "past_simple_v2": "went",
    "past_participle_v3": "gone",
    "cefr_level": "A1"
  }
]

3.2 irregular_nouns.json
Digunakan untuk koreksi kesesuaian jumlah benda (misal: GRAMMAR_A1_PLURAL_MISMATCH).

JSON
[
  {
    "singular": "child",
    "plural": "children",
    "cefr_level": "A1"
  },
  {
    "singular": "person",
    "plural": "people",
    "cefr_level": "A1"
  }
]
3.3 irregular_adjectives.json
Digunakan untuk koreksi perbandingan dan bentukan adverbs (misal: GRAMMAR_A2_COMPARATIVE_MISMATCH).

JSON
[
  {
    "base": "good",
    "comparative": "better",
    "superlative": "best",
    "derived_adverb": "well",
    "cefr_level": "A1"
  },
  {
    "base": "bad",
    "comparative": "worse",
    "superlative": "worst",
    "derived_adverb": "badly",
    "cefr_level": "A1"
  }
]
3.4 demonstratives_and_pronouns.json
Digunakan untuk koreksi posisi kata ganti dan penunjuk (misal: GRAMMAR_A1_PRONOUN_OBJECT_MISMATCH & GRAMMAR_A1_DEMONSTRATIVE_MISMATCH).

JSON
{
  "pronouns": [
    {
      "subject": "i",
      "object": "me",
      "possessive_adjective": "my",
      "possessive_pronoun": "mine",
      "reflexive": "myself"
    },
    {
      "subject": "he",
      "object": "him",
      "possessive_adjective": "his",
      "possessive_pronoun": "his",
      "reflexive": "himself"
    }
  ],
  "demonstratives": [
    {
      "singular": "this",
      "plural": "these",
      "distance": "near"
    },
    {
      "singular": "that",
      "plural": "those",
      "distance": "far"
    }
  ]
}
4. Execution Pipeline & Service Layer Integration
[User Input String] 
       │
       ▼
[STT Transcription] ──► [Regex Grammar Evaluator] 
                               │ (Detects Rule Violation, e.g., V1 + Yesterday)
                               ▼
                 [WordTransformationRepository] 
                               │ (O(1) Memory Lookup from JSON)
                               ▼
               [Resolved Correct Token (e.g., "saw")]
                               │
                               ▼
                   [CorrectiveTextService / LLM] ──► [Final Evaluated Payload]
5. Laravel Service Implementation Example
PHP
namespace App\Services\Grammar;

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

        $verbsData = json_decode(file_get_contents($basePath . 'irregular_verbs.json'), true);
        foreach ($verbsData as $item) {$this->verbs[$item['base_v1']] =$item;
        }

        $nounsData = json_decode(file_get_contents($basePath . 'irregular_nouns.json'), true);
        foreach ($nounsData as $item) {$this->nouns[$item['singular']] =$item['plural'];
        }

        $adjData = json_decode(file_get_contents($basePath . 'irregular_adjectives.json'), true);
        foreach ($adjData as $item) {$this->adjectives[$item['base']] =$item;
        }

        $pronounDemoData = json_decode(file_get_contents($basePath . 'demonstratives_and_pronouns.json'), true);
        $this->pronouns =$pronounDemoData['pronouns'];
        $this->demonstratives =$pronounDemoData['demonstratives'];
    }

    public function getPastSimple(string $v1Verb): string
    {
        $verb = strtolower($v1Verb);
        if (isset($this->verbs[$verb])) {
            return $this->verbs[$verb]['past_simple_v2'];
        }

        // Fallback Regular Verb Rule (-ed/-d)
        if (str_ends_with($verb, 'e')) {
            return $verb . 'd';
        }
        if (str_ends_with($verb, 'y') && !preg_match('/[aeiou]y$/',$verb)) {
            return substr($verb, 0, -1) . 'ied';
        }
        return $verb . 'ed';
    }

    public function getPluralNoun(string $singularNoun): string
    {
        $noun = strtolower($singularNoun);
        if (isset($this->nouns[$noun])) {
            return $this->nouns[$noun];
        }

        // Fallback Regular Plural Rule (-s/-es)
        if (preg_match('/(s|x|z|ch|sh)$/',$noun)) {
            return $noun . 'es';
        }
        if (str_ends_with($noun, 'y') && !preg_match('/[aeiou]y$/',$noun)) {
            return substr($noun, 0, -1) . 'ies';
        }
        return $noun . 's';
    }
}