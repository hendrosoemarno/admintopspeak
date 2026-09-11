<?php

namespace App\Models;

use App\Enums\CefrLevel;
use App\Enums\TestType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'test_type', 'part_number', 'cefr_level',
    'question_text', 'standard_answer', 'required_vocab_tags', 'is_starter',
    'topic_category', 'metadata',
])]
class QuestionBank extends Model
{
    protected function casts(): array
    {
        return [
            'test_type' => TestType::class,
            'cefr_level' => CefrLevel::class,
            'required_vocab_tags' => 'array',
            'is_starter' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function conversationLogs(): HasMany
    {
        return $this->hasMany(ConversationLog::class, 'question_id');
    }

    public function getVocabWordsAttribute(): array
    {
        return $this->required_vocab_tags ?? [];
    }
}