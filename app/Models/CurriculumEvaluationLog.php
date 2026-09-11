<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumEvaluationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'lesson_id',
        'question_id',
        'user_transcript',
        'score',
        'is_correct',
        'key_point_detected',
        'key_point_target',
        'grammar_feedback',
        'vocabulary_feedback',
        'suggested_answer',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'key_point_detected' => 'boolean',
            'score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}