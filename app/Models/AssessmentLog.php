<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentLog extends Model
{
    protected $fillable = [
        'user_id', 'test_type', 'task_type', 'prompt_question',
        'user_transcript', 'duration_seconds', 'overall_score',
        's_total', 'final_fluency', 'is_on_topic', 'raw_response_json',
    ];

    protected $hidden = ['user_id', 'raw_response_json'];

    protected function casts(): array
    {
        return [
            'test_type' => 'string',
            'duration_seconds' => 'integer',
            'overall_score' => 'float',
            's_total' => 'float',
            'final_fluency' => 'integer',
            'is_on_topic' => 'boolean',
            'raw_response_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
