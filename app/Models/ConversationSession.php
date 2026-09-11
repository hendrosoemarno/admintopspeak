<?php

namespace App\Models;

use App\Enums\SessionMode;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConversationSession extends Model
{
    protected $fillable = [
    'id', 'user_id', 'mode', 'topic_id', 'lesson_id',
    'start_level', 'current_level', 'total_turns_planned',
    'status', 'completed_at',
];
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'mode' => SessionMode::class,
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ThematicTopic::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ConversationLog::class, 'session_id', 'id');
    }
}