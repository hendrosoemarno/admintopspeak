<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'max:40'],
            'question_id' => ['required', 'integer'],
            'user_transcript' => ['required', 'string', 'max:5000'],
        ];
    }
}