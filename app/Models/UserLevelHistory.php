<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'session_id', 'previous_level',
    'new_level', 'trigger_score', 'promotion_reason',
])]
class UserLevelHistory extends Model
{
    protected function casts(): array
    {
        return [
            'session_id' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}