<?php

namespace App\Http\Requests;

use App\Enums\CefrLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGrammarRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rule_code' => ['required', 'string', 'max:50', Rule::unique('grammar_rules', 'rule_code')],
            'category' => ['required', 'string', 'max:100'],
            'cefr_level' => ['required', Rule::in(array_column(CefrLevel::cases(), 'value'))],
            'regex_pattern' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ];
    }
}