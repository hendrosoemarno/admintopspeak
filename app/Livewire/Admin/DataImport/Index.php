<?php

namespace App\Livewire\Admin\DataImport;

use App\Enums\CefrLevel;
use App\Models\FillerWord;
use App\Models\GrammarRule;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\ThematicTopic;
use App\Models\Unit;
use App\Models\VocabularyBank;
use App\Repositories\GrammarDataRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $importType = 'question_banks';

    public string $jsonContent = '';

    public ?array $result = null;

    public ?array $preview = null;

    public string $exportJson = '';

    public ?string $exportedAt = null;

    /** Target unit untuk import/export IELTS Curriculum (filter part -> unit). */
    public ?int $curriculumPart = null;

    public ?int $curriculumUnitId = null;

    public array $templates = [
        'question_banks' => [
            'title' => 'Question Bank',
        ],
        'grammar_rules' => [
            'title' => 'Grammar Rules',
        ],
        'thematic_topics' => [
            'title' => 'Thematic Topics',
        ],
        'vocabulary_bank' => [
            'title' => 'Vocabulary Bank',
        ],
        'filler_words' => [
            'title' => 'Filler Words',
        ],
        'word_transformation' => [
            'title' => 'Word Transformation',
        ],
        'ielts_curriculum' => [
            'title' => 'IELTS Curriculum',
        ],
    ];

    public function rules(): array
    {
        return [
            'importType' => ['required', Rule::in(array_keys($this->templates))],
            'jsonContent' => ['required', 'string'],
        ];
    }

    public function updatedJsonContent(): void
    {
        $this->result = null;
    }

    public function updatedImportType(): void
    {
        $this->resetValidation();
        $this->result = null;
        $this->curriculumPart = null;
        $this->curriculumUnitId = null;
    }

    public function setType(string $type): void
    {
        if (isset($this->templates[$type])) {
            $this->importType = $type;
            $this->result = null;
            $this->exportJson = '';
            $this->exportedAt = null;
            $this->curriculumPart = null;
            $this->curriculumUnitId = null;
            $this->resetValidation();
        }
    }

    public function setCurriculumPart(?int $part): void
    {
        $this->curriculumPart = $part;
        $this->curriculumUnitId = null;
        $this->result = null;
        $this->preview = null;
    }

    public function setCurriculumUnit(?int $unitId): void
    {
        $this->curriculumUnitId = $unitId;
        $this->result = null;
        $this->preview = null;
    }

    #[Computed]
    public function curriculumUnits(): \Illuminate\Support\Collection
    {
        return Unit::query()
            ->when($this->curriculumPart !== null, fn ($q) => $q->where('part', $this->curriculumPart))
            ->orderBy('part')
            ->orderBy('unit_number')
            ->get(['id', 'unit_number', 'title', 'part']);
    }

    #[Computed]
    public function curriculumLessons(): \Illuminate\Support\Collection
    {
        if ($this->curriculumUnitId === null) {
            return collect();
        }

        return Lesson::query()
            ->where('unit_id', $this->curriculumUnitId)
            ->orderBy('lesson_number')
            ->get(['id', 'lesson_number', 'title']);
    }

    public function exportType(string $type): void
    {
        if (! isset($this->templates[$type])) {
            return;
        }

        $this->importType = $type;
        $this->result = null;
        $this->exportData();
    }

    public function fillExample(): void
    {
        $this->jsonContent = $this->exampleJson();
        $this->result = null;
    }

    public function import(): void
    {
        $this->validate();

        $content = trim($this->jsonContent);

        if ($content === '') {
            $this->addError('jsonContent', 'Isi JSON terlebih dahulu.');

            return;
        }

        $rows = $this->decodeRows($content);

        if ($rows === null) {
            return;
        }

        $this->preview = [
            'total' => 0,
            'valid' => 0,
            'errors' => [],
            'rows' => [],
            'duplicates_in_json' => [],
        ];

        $seenKeys = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                $this->preview['errors'][] = 'Baris '.($index + 1).': bukan objek.';

                continue;
            }

            try {
                $normalized = $this->normalizeRow($row);

                $key = $this->uniqueKeyForRow($normalized);
                if ($key !== null) {
                    if (isset($seenKeys[$key])) {
                        $this->preview['duplicates_in_json'][] = $key;
                    } else {
                        $seenKeys[$key] = true;
                    }
                }

                $this->preview['rows'][] = $normalized;
            } catch (\Throwable $e) {
                $this->preview['errors'][] = 'Baris '.($index + 1).': '.$e->getMessage();
            }
        }

        $this->preview['total'] = count($rows);
        $this->preview['valid'] = count($this->preview['rows']);
        $this->preview['duplicates_in_json'] = array_values(array_unique($this->preview['duplicates_in_json']));

        $this->result = null;
    }

    public function confirmImport(): void
    {
        if (! $this->preview) {
            $this->addError('jsonContent', 'Jalankan preview terlebih dahulu.');

            return;
        }

        $rows = $this->preview['rows'] ?? null;

        if ($rows === null) {
            $this->addError('jsonContent', 'Sesi preview kedaluwarsa, jalankan preview ulang.');

            return;
        }

        $this->result = DB::transaction(function () use ($rows) {
            $created = 0;
            $updated = 0;
            $errors = [];
            $duplicates = [];

            foreach ($rows as $index => $row) {
                try {
                    $isExisting = $this->rowExists($row);
                    $this->persistRow($row);

                    if ($isExisting) {
                        $updated++;
                        $duplicates[] = $this->uniqueKeyForRow($row);
                    } else {
                        $created++;
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Baris '.($index + 1).': '.$e->getMessage();

                    continue;
                }
            }

            return [
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors,
                'duplicates' => array_values(array_unique($duplicates)),
                'duplicates_in_json' => $this->preview['duplicates_in_json'] ?? [],
            ];
        });

        $this->preview = null;
        $this->dispatch('flash', message: 'Import selesai.');
    }

    public function cancelPreview(): void
    {
        $this->preview = null;
        $this->result = null;
    }

    private function decodeRows(string $content): ?array
    {
        try {
            $rows = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->addError('jsonContent', 'JSON tidak valid: '.$e->getMessage());

            return null;
        }

        if (! is_array($rows)) {
            $this->addError('jsonContent', 'Format JSON harus berupa array objek.');

            return null;
        }

        return $rows;
    }

    private function normalizeRow(array $row): array
    {
        return match ($this->importType) {
            'question_banks' => $this->normalizeQuestionBank($row),
            'grammar_rules' => $this->normalizeGrammarRule($row),
            'thematic_topics' => $this->normalizeThematicTopic($row),
            'vocabulary_bank' => $this->normalizeVocabulary($row),
            'filler_words' => $this->normalizeFillerWord($row),
            'word_transformation' => $this->normalizeWordTransformation($row),
            'ielts_curriculum' => $this->normalizeCurriculumQuestion($row),
            default => throw new \InvalidArgumentException('Jenis data tidak dikenal.'),
        };
    }

    private function persistRow(array $row): bool
    {
        return match ($this->importType) {
            'question_banks' => $this->persistQuestionBank($row),
            'grammar_rules' => $this->persistGrammarRule($row),
            'thematic_topics' => $this->persistThematicTopic($row),
            'vocabulary_bank' => $this->persistVocabulary($row),
            'filler_words' => $this->persistFillerWord($row),
            'word_transformation' => $this->persistWordTransformation($row),
            'ielts_curriculum' => $this->persistCurriculumQuestion($row),
            default => false,
        };
    }

    private function uniqueKeyForRow(array $row): ?string
    {
        return match ($this->importType) {
            'question_banks' => $row['question_text'] ?? null,
            'grammar_rules' => $row['rule_code'] ?? null,
            'thematic_topics' => $row['topic_name'] ?? null,
            'vocabulary_bank' => $row['word'] ?? null,
            'filler_words' => $row['phrase'] ?? null,
            'word_transformation' => $this->uniqueKeyForTransformation($row),
            'ielts_curriculum' => $this->curriculumUniqueKey($row),
            default => null,
        };
    }

    private function rowExists(array $row): bool
    {
        return match ($this->importType) {
            'question_banks' => QuestionBank::where('question_text', $row['question_text'])->exists(),
            'grammar_rules' => GrammarRule::where('rule_code', $row['rule_code'])->exists(),
            'thematic_topics' => ThematicTopic::where('topic_name', $row['topic_name'])->exists(),
            'vocabulary_bank' => VocabularyBank::where('word', $row['word'])->exists(),
            'filler_words' => FillerWord::where('phrase', $row['phrase'])->exists(),
            'word_transformation' => $this->transformationRowExists($row),
            'ielts_curriculum' => Question::where('lesson_id', $row['lesson_id'])
                ->where('question_text', $row['question_text'])
                ->exists(),
            default => false,
        };
    }

    public function exportData(): void
    {
        $rows = match ($this->importType) {
            'question_banks' => QuestionBank::query()
                ->orderBy('cefr_level')
                ->orderBy('id')
                ->get()
                ->map(fn (QuestionBank $q) => [
                    'test_type' => $q->test_type->value,
                    'part_number' => $q->part_number,
                    'cefr_level' => $q->cefr_level->value,
                    'question_text' => $q->question_text,
                    'standard_answer' => $q->standard_answer ?? '',
                    'required_vocab_tags' => $q->required_vocab_tags ?? [],
                    'is_starter' => (bool) $q->is_starter,
                    'topic_category' => $q->topic_category,
                    'metadata' => $q->metadata ?? ['audio_url' => null],
                ])
                ->values()
                ->all(),
            'grammar_rules' => GrammarRule::query()
                ->orderBy('cefr_level')
                ->orderBy('rule_code')
                ->get()
                ->map(fn (GrammarRule $rule) => [
                    'rule_code' => $rule->rule_code,
                    'category' => $rule->category,
                    'cefr_level' => $rule->cefr_level->value,
                    'regex_pattern' => $rule->regex_pattern,
                    'description' => $rule->description,
                    'is_active' => (bool) $rule->is_active,
                ])
                ->values()
                ->all(),
            'thematic_topics' => ThematicTopic::query()
                ->orderBy('topic_name')
                ->get()
                ->map(fn (ThematicTopic $topic) => [
                    'topic_name' => $topic->topic_name,
                    'roleplay_persona' => $topic->roleplay_persona,
                    'selected_level' => $topic->selected_level,
                    'context_vocab_tags' => $topic->context_vocab_tags ?? [],
                    'is_active' => (bool) $topic->is_active,
                ])
                ->values()
                ->all(),
            'vocabulary_bank' => VocabularyBank::query()
                ->orderBy('word')
                ->get()
                ->map(fn (VocabularyBank $vocab) => [
                    'word' => $vocab->word,
                    'part_of_speech' => $vocab->part_of_speech,
                    'cefr_level' => $vocab->cefr_level,
                    'topic_category' => $vocab->topic_category,
                ])
                ->values()
                ->all(),
            'filler_words' => FillerWord::query()
                ->orderBy('category')
                ->orderBy('phrase')
                ->get()
                ->map(fn (FillerWord $word) => [
                    'phrase' => $word->phrase,
                    'category' => $word->category,
                    'is_active' => (bool) $word->is_active,
                ])
                ->values()
                ->all(),
            'word_transformation' => $this->exportWordTransformation(),
            'ielts_curriculum' => $this->exportCurriculum(),
            default => [],
        };

        $this->exportJson = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->exportedAt = now()->format('d M Y, H:i');
        $this->dispatch('flash', message: 'Export selesai. Salin JSON di bawah.');
    }

    private function exportCurriculum(): array
    {
        if ($this->curriculumUnitId === null) {
            $this->dispatch('flash', message: 'Pilih unit target terlebih dahulu untuk export.');

            return [];
        }

        $unit = Unit::find($this->curriculumUnitId);

        if ($unit === null) {
            return [];
        }

        return Question::query()
            ->whereIn('lesson_id', $unit->lessons()->pluck('id'))
            ->with('lesson')
            ->orderBy('lesson_id')
            ->orderBy('id')
            ->get()
            ->map(fn (Question $q) => [
                'lesson_number' => $q->lesson->lesson_number,
                'question_text' => $q->question_text,
                'model_answer' => $q->model_answer ?? '',
                'key_point' => $q->key_point ?? '',
            ])
            ->values()
            ->all();
    }

    public function clearExport(): void
    {
        $this->exportJson = '';
        $this->exportedAt = null;
    }

    private function normalizeQuestionBank(array $row): array
    {
        if (empty($row['question_text'])) {
            throw new \InvalidArgumentException('question_text wajib diisi.');
        }

        $cefr = strtoupper((string) ($row['cefr_level'] ?? 'A1'));
        $testType = strtoupper((string) ($row['test_type'] ?? 'ADAPTIVE'));

        return [
            'test_type' => in_array($testType, ['ADAPTIVE', 'IELTS_SPEAKING', 'TOEFL_IBT'], true) ? $testType : 'ADAPTIVE',
            'part_number' => (int) ($row['part_number'] ?? 1),
            'cefr_level' => in_array($cefr, array_map(fn ($l) => $l->value, CefrLevel::cases()), true) ? $cefr : 'A1',
            'question_text' => trim((string) ($row['question_text'] ?? '')),
            'standard_answer' => trim((string) ($row['standard_answer'] ?? '')),
            'required_vocab_tags' => $this->normalizeTags($row['required_vocab_tags'] ?? []),
            'is_starter' => filter_var($row['is_starter'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'topic_category' => trim((string) ($row['topic_category'] ?? 'General Conversation')),
            'metadata' => $this->normalizeMetadata($row['metadata'] ?? null),
        ];
    }

    private function persistQuestionBank(array $row): bool
    {
        $existing = QuestionBank::where('question_text', $row['question_text'])->first();

        if ($existing) {
            $existing->update($row);

            return true;
        }

        QuestionBank::create($row);

        return true;
    }

    private function normalizeGrammarRule(array $row): array
    {
        if (empty($row['rule_code'])) {
            throw new \InvalidArgumentException('rule_code wajib diisi.');
        }

        $row = [
            'rule_code' => strtoupper(trim((string) $row['rule_code'])),
            'category' => trim((string) ($row['category'] ?? 'General')),
            'cefr_level' => strtoupper((string) ($row['cefr_level'] ?? 'A1')),
            'regex_pattern' => (string) ($row['regex_pattern'] ?? ''),
            'description' => trim((string) ($row['description'] ?? '')),
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];

        if ($row['regex_pattern'] === '') {
            throw new \InvalidArgumentException('regex_pattern wajib diisi.');
        }

        return $row;
    }

    private function persistGrammarRule(array $row): bool
    {
        GrammarRule::updateOrCreate(['rule_code' => $row['rule_code']], $row);

        return true;
    }

    private function normalizeThematicTopic(array $row): array
    {
        if (empty($row['topic_name'])) {
            throw new \InvalidArgumentException('topic_name wajib diisi.');
        }

        $row = [
            'topic_name' => trim((string) $row['topic_name']),
            'roleplay_persona' => trim((string) ($row['roleplay_persona'] ?? '')),
            'selected_level' => trim((string) ($row['selected_level'] ?? 'Beginner')),
            'context_vocab_tags' => $this->normalizeTags($row['context_vocab_tags'] ?? []),
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'created_by' => null,
        ];

        if ($row['roleplay_persona'] === '') {
            throw new \InvalidArgumentException('roleplay_persona wajib diisi.');
        }

        return $row;
    }

    private function persistThematicTopic(array $row): bool
    {
        ThematicTopic::updateOrCreate(['topic_name' => $row['topic_name']], $row);

        return true;
    }

    private function normalizeVocabulary(array $row): array
    {
        if (empty($row['word'])) {
            throw new \InvalidArgumentException('word wajib diisi.');
        }

        return [
            'word' => strtolower(trim((string) $row['word'])),
            'part_of_speech' => strtolower(trim((string) ($row['part_of_speech'] ?? 'noun'))),
            'cefr_level' => strtoupper((string) ($row['cefr_level'] ?? 'A1')),
            'topic_category' => trim((string) ($row['topic_category'] ?? 'General')),
        ];
    }

    private function persistVocabulary(array $row): bool
    {
        VocabularyBank::updateOrCreate(['word' => $row['word']], $row);

        return true;
    }

    private function normalizeFillerWord(array $row): array
    {
        if (empty($row['phrase'])) {
            throw new \InvalidArgumentException('phrase wajib diisi.');
        }

        $category = strtolower(trim((string) ($row['category'] ?? 'hesitation')));

        return [
            'phrase' => strtolower(trim((string) $row['phrase'])),
            'category' => in_array($category, ['hesitation', 'discourse', 'phrase'], true) ? $category : 'hesitation',
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function persistFillerWord(array $row): bool
    {
        FillerWord::updateOrCreate(['phrase' => $row['phrase']], $row);

        return true;
    }

    /**
     * Koleksi Word Transformation beserta metadata file JSON-nya.
     */
    private function transformationCollections(): array
    {
        return [
            'verbs' => [
                'file' => 'irregular_verbs.json',
                'rootKey' => null,
                'uniqueKey' => 'base_v1',
                'fields' => ['base_v1', 'past_simple_v2', 'past_participle_v3', 'cefr_level'],
            ],
            'nouns' => [
                'file' => 'irregular_nouns.json',
                'rootKey' => null,
                'uniqueKey' => 'singular',
                'fields' => ['singular', 'plural', 'cefr_level'],
            ],
            'adjectives' => [
                'file' => 'irregular_adjectives.json',
                'rootKey' => null,
                'uniqueKey' => 'base',
                'fields' => ['base', 'comparative', 'superlative', 'derived_adverb', 'cefr_level'],
            ],
            'pronouns' => [
                'file' => 'demonstratives_and_pronouns.json',
                'rootKey' => 'pronouns',
                'uniqueKey' => 'subject',
                'fields' => ['subject', 'object', 'possessive_adjective', 'possessive_pronoun', 'reflexive'],
            ],
            'demonstratives' => [
                'file' => 'demonstratives_and_pronouns.json',
                'rootKey' => 'demonstratives',
                'uniqueKey' => 'singular',
                'fields' => ['singular', 'plural', 'distance'],
            ],
        ];
    }

    private function normalizeWordTransformation(array $row): array
    {
        $collection = strtolower(trim((string) ($row['collection'] ?? '')));
        $config = $this->transformationCollections()[$collection] ?? null;

        if ($config === null) {
            throw new \InvalidArgumentException(
                'collection wajib salah satu dari: '.implode(', ', array_keys($this->transformationCollections())).'.',
            );
        }

        $normalized = [];

        foreach ($config['fields'] as $field) {
            $value = trim((string) ($row[$field] ?? ''));

            if ($value === '') {
                throw new \InvalidArgumentException("{$field} wajib diisi.");
            }

            $normalized[$field] = $field === 'cefr_level' ? strtoupper($value) : strtolower($value);
        }

        return ['collection' => $collection] + $normalized;
    }

    private function persistWordTransformation(array $row): bool
    {
        $config = $this->transformationCollections()[$this->collectionForRow($row)];
        $repo = app(GrammarDataRepository::class);
        $file = $repo->read($config['file']);
        $list = $config['rootKey'] ? ($file[$config['rootKey']] ?? []) : $file;

        $uniqueKey = $config['uniqueKey'];
        $replaced = false;

        foreach ($list as $index => $item) {
            if (($item[$uniqueKey] ?? null) === $row[$uniqueKey]) {
                $list[$index] = $row;
                $replaced = true;
                break;
            }
        }

        if (! $replaced) {
            $list[] = $row;
        }

        if ($config['rootKey']) {
            $file[$config['rootKey']] = array_values($list);
        } else {
            $file = array_values($list);
        }

        $repo->write($config['file'], $file);

        return true;
    }

    private function uniqueKeyForTransformation(array $row): ?string
    {
        $config = $this->transformationCollections()[$this->collectionForRow($row)] ?? null;

        return $config ? ($row[$config['uniqueKey']] ?? null) : null;
    }

    private function transformationRowExists(array $row): bool
    {
        $config = $this->transformationCollections()[$this->collectionForRow($row)] ?? null;

        if ($config === null) {
            return false;
        }

        $repo = app(GrammarDataRepository::class);
        $file = $repo->read($config['file']);
        $list = $config['rootKey'] ? ($file[$config['rootKey']] ?? []) : $file;
        $uniqueKey = $config['uniqueKey'];

        foreach ($list as $item) {
            if (($item[$uniqueKey] ?? null) === $row[$uniqueKey]) {
                return true;
            }
        }

        return false;
    }

    private function exportWordTransformation(): array
    {
        $rows = [];
        $repo = app(GrammarDataRepository::class);

        foreach ($this->transformationCollections() as $collection => $config) {
            $file = $repo->read($config['file']);
            $list = $config['rootKey'] ? ($file[$config['rootKey']] ?? []) : $file;

            foreach ($list as $item) {
                $rows[] = ['collection' => $collection] + $item;
            }
        }

        return $rows;
    }

    private function collectionForRow(array $row): string
    {
        return strtolower(trim((string) ($row['collection'] ?? '')));
    }

    /**
     * Normalisasi satu baris soal IELTS Curriculum.
     * Struktur per baris: { lesson_number, question_text, model_answer, key_point }.
     * Unit target diambil dari $this->curriculumUnitId; lesson harus sudah ada,
     * unit_number & part tidak pernah diubah.
     */
    private function normalizeCurriculumQuestion(array $row): array
    {
        if ($this->curriculumUnitId === null) {
            throw new \InvalidArgumentException('Pilih unit target terlebih dahulu (Part -> Unit).');
        }

        $lessonNumber = (int) ($row['lesson_number'] ?? 0);

        if ($lessonNumber < 1) {
            throw new \InvalidArgumentException('lesson_number wajib diisi (bilangan bulat >= 1).');
        }

        $lesson = Lesson::query()
            ->where('unit_id', $this->curriculumUnitId)
            ->where('lesson_number', $lessonNumber)
            ->first();

        if ($lesson === null) {
            throw new \InvalidArgumentException("Lesson {$lessonNumber} tidak ditemukan di unit terpilih.");
        }

        if (empty($row['question_text'])) {
            throw new \InvalidArgumentException('question_text wajib diisi.');
        }

        return [
            'lesson_id' => $lesson->id,
            'lesson_number' => $lessonNumber,
            'question_text' => trim((string) $row['question_text']),
            'model_answer' => trim((string) ($row['model_answer'] ?? '')),
            'key_point' => trim((string) ($row['key_point'] ?? '')),
        ];
    }

    private function persistCurriculumQuestion(array $row): bool
    {
        $lesson = Lesson::find($row['lesson_id']);

        if ($lesson === null || $lesson->unit_id !== $this->curriculumUnitId) {
            return false;
        }

        Question::updateOrCreate(
            ['lesson_id' => $row['lesson_id'], 'question_text' => $row['question_text']],
            [
                'model_answer' => $row['model_answer'],
                'key_point' => $row['key_point'],
            ]
        );

        return true;
    }

    private function curriculumUniqueKey(array $row): string
    {
        return 'L'.$row['lesson_number'].' :: '.$row['question_text'];
    }

    private function normalizeTags(mixed $tags): array
    {
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }

        if (! is_array($tags)) {
            return [];
        }

        return collect($tags)->map(fn ($t) => trim((string) $t))->filter()->values()->all();
    }

    private function normalizeMetadata(mixed $metadata): array
    {
        if (! is_array($metadata)) {
            return ['audio_url' => null];
        }

        return $metadata;
    }

    public function sampleJson(string $type): string
    {
        return match ($type) {
            'question_banks' => json_encode([
                [
                    'test_type' => 'ADAPTIVE',
                    'part_number' => 1,
                    'cefr_level' => 'A1',
                    'question_text' => 'Could you tell me about your hometown?',
                    'standard_answer' => 'My hometown is a small and quiet city in the mountains. I have lived there since I was a child and I love the fresh air.',
                    'required_vocab_tags' => ['hometown', 'city', 'live'],
                    'is_starter' => false,
                    'topic_category' => 'Introduction',
                    'metadata' => ['audio_url' => null],
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'grammar_rules' => json_encode([
                [
                    'rule_code' => 'SVA_99',
                    'category' => 'Subject-Verb Agreement',
                    'cefr_level' => 'A2',
                    'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
                    'description' => 'Subjek tunggal harus diikuti verb -s.',
                    'is_active' => true,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'thematic_topics' => json_encode([
                [
                    'topic_name' => 'Talking About Family',
                    'roleplay_persona' => 'Friendly Neighbor',
                    'selected_level' => 'Beginner',
                    'context_vocab_tags' => ['family', 'home', 'together'],
                    'is_active' => true,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'vocabulary_bank' => json_encode([
                [
                    'word' => 'hometown',
                    'part_of_speech' => 'noun',
                    'cefr_level' => 'A1',
                    'topic_category' => 'Introduction',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'filler_words' => json_encode([
                [
                    'phrase' => 'uh',
                    'category' => 'hesitation',
                    'is_active' => true,
                ],
                [
                    'phrase' => 'you know',
                    'category' => 'phrase',
                    'is_active' => true,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'word_transformation' => json_encode([
                [
                    'collection' => 'verbs',
                    'base_v1' => 'see',
                    'past_simple_v2' => 'saw',
                    'past_participle_v3' => 'seen',
                    'cefr_level' => 'A1',
                ],
                [
                    'collection' => 'verbs',
                    'base_v1' => 'go',
                    'past_simple_v2' => 'went',
                    'past_participle_v3' => 'gone',
                    'cefr_level' => 'A1',
                ],
                [
                    'collection' => 'nouns',
                    'singular' => 'child',
                    'plural' => 'children',
                    'cefr_level' => 'A1',
                ],
                [
                    'collection' => 'nouns',
                    'singular' => 'person',
                    'plural' => 'people',
                    'cefr_level' => 'A1',
                ],
                [
                    'collection' => 'adjectives',
                    'base' => 'good',
                    'comparative' => 'better',
                    'superlative' => 'best',
                    'derived_adverb' => 'well',
                    'cefr_level' => 'A1',
                ],
                [
                    'collection' => 'adjectives',
                    'base' => 'bad',
                    'comparative' => 'worse',
                    'superlative' => 'worst',
                    'derived_adverb' => 'badly',
                    'cefr_level' => 'A1',
                ],
                [
                    'collection' => 'pronouns',
                    'subject' => 'he',
                    'object' => 'him',
                    'possessive_adjective' => 'his',
                    'possessive_pronoun' => 'his',
                    'reflexive' => 'himself',
                ],
                [
                    'collection' => 'pronouns',
                    'subject' => 'she',
                    'object' => 'her',
                    'possessive_adjective' => 'her',
                    'possessive_pronoun' => 'hers',
                    'reflexive' => 'herself',
                ],
                [
                    'collection' => 'demonstratives',
                    'singular' => 'this',
                    'plural' => 'these',
                    'distance' => 'near',
                ],
                [
                    'collection' => 'demonstratives',
                    'singular' => 'that',
                    'plural' => 'those',
                    'distance' => 'far',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'ielts_curriculum' => json_encode([
                [
                    'lesson_number' => 1,
                    'question_text' => 'Is going to a movie theater still a big part of weekend culture for young people?',
                    'model_answer' => 'Yes, visiting a movie theater remains a big part of youth culture because it offers a shared social experience.',
                    'key_point' => 'a big part of / a movie theater',
                ],
                [
                    'lesson_number' => 2,
                    'question_text' => 'What kinds of TV shows are popular in your country?',
                    'model_answer' => 'Reality talent competitions and drama series are currently very popular.',
                    'key_point' => 'TV shows',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            default => '[]',
        };
    }

    public function exampleJson(): string
    {
        return $this->sampleJson($this->importType);
    }

    public function render()
    {
        $this->result ??= null;

        return view('livewire.admin.data-import.index')
            ->layout('layouts.app', ['title' => 'Import JSON']);
    }
}
