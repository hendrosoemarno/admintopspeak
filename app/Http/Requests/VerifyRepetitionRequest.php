<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRepetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'uuid'],
            'turn_number' => ['required', 'integer', 'between:1,8'],
            'user_repetition_transcript' => ['required', 'string', 'max:2000'],
        ];
    }
}