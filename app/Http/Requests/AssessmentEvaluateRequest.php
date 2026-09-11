<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentEvaluateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'test_type' => ['required', 'in:IELTS,TOEFL'],
            'task_type' => ['required', 'string', 'max:100'],
            'prompt_question' => ['required', 'string', 'max:1000'],
            'user_transcript' => ['required', 'string', 'max:5000'],
            'duration_seconds' => ['nullable', 'integer', 'between:1,600'],
        ];
    }

    public function messages(): array
    {
        return [
            'test_type.in' => 'test_type harus IELTS atau TOEFL.',
        ];
    }
}
