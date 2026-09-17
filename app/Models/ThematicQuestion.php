<?php

namespace App\Models;

use App\Enums\CefrLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThematicQuestion extends Model
{
    protected $fillable = ['topic_id', 'question_text', 'standard_answer', 'cefr_level', 'key_point'];

    protected function casts(): array
    {
        return [
            'cefr_level' => CefrLevel::class,
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ThematicTopic::class, 'topic_id');
    }
}