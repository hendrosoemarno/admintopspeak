<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'completion_score', 'mastery_score', 'accuracy_score', 'overall_index',
    'predicted_band', 'status_label', 'color_code',
    'passed_lessons_count', 'is_ready', 'last_calculated_at',
])]
class UserProgressPredictor extends Model
{
    protected function casts(): array
    {
        return [
            'completion_score' => 'decimal:2',
            'mastery_score' => 'decimal:2',
            'accuracy_score' => 'decimal:2',
            'overall_index' => 'decimal:2',
            'is_ready' => 'boolean',
            'last_calculated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}