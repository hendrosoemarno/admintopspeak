<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThematicTopic extends Model
{
    protected $fillable = [
    'topic_name', 'roleplay_persona', 'selected_level',
    'context_vocab_tags', 'is_active', 'created_by',
];
    protected function casts(): array
    {
        return [
            'context_vocab_tags' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ThematicQuestion::class, 'topic_id');
    }
}