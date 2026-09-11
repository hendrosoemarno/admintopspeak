<?php

namespace App\Models;

use App\Enums\LessonDifficulty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = ['unit_id', 'lesson_number', 'title', 'difficulty', 'is_active'];
    protected function casts(): array
    {
        return [
            'difficulty' => LessonDifficulty::class,
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function userLessonProgress(): HasMany
    {
        return $this->hasMany(UserLessonProgress::class);
    }
}