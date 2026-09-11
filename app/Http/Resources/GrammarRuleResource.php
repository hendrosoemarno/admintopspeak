<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrammarRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rule_code' => $this->rule_code,
            'category' => $this->category,
            'cefr_level' => $this->cefr_level->value,
            'regex_pattern' => $this->regex_pattern,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];
    }
}