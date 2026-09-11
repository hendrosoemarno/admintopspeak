<?php

namespace App\Models;

use App\Enums\PendingRuleStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'grammar_rule_id', 'raw_user_input', 'detected_error',
    'suggested_regex', 'suggested_correct_sentence', 'status',
])]
class PendingGrammarRule extends Model
{
    protected function casts(): array
    {
        return [
            'status' => PendingRuleStatus::class,
        ];
    }

    public function grammarRule(): BelongsTo
    {
        return $this->belongsTo(GrammarRule::class);
    }
}