<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = ['lesson_id', 'question_text', 'model_answer', 'key_point'];
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}