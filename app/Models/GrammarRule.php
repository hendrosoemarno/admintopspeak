<?php

namespace App\Models;

use App\Enums\CefrLevel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['rule_code', 'category', 'rule_type', 'source', 'llm_meta', 'cefr_level', 'regex_pattern', 'description', 'is_active'])]
class GrammarRule extends Model
{
    protected function casts(): array
    {
        return [
            'cefr_level' => CefrLevel::class,
            'is_active' => 'boolean',
            'llm_meta' => 'array',
        ];
    }

    public function isPositive(): bool
    {
        return $this->rule_type === 'positive';
    }

    public function pendingRules(): HasMany
    {
        return $this->hasMany(PendingGrammarRule::class);
    }
}