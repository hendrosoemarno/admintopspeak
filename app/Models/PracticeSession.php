<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeSession extends Model
{
    protected $fillable = [
    'user_id', 'lesson_id', 'session_id',
    'total_questions', 'correct_count',
    'score', 'is_passed',
    'total_keypoints', 'correct_keypoints',
    'completed_at',
];
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'is_passed' => 'boolean',
            'completed_at' => 'datetime',
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
}