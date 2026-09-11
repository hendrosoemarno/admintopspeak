<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', 'in:ADAPTIVE,THEMATIC,IELTS_SPEAKING,TOEFL_IBT'],
            'topic_id' => ['nullable', 'integer', 'exists:thematic_topics,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'mode.in' => 'Mode harus ADAPTIVE, THEMATIC, IELTS_SPEAKING, atau TOEFL_IBT.',
        ];
    }
}