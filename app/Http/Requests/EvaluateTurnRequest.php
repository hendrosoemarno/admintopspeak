<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class EvaluateTurnRequest extends FormRequest
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
            'question_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $inBank = DB::table('question_banks')->where('id', $value)->exists();
                    $inCurriculum = DB::table('questions')->where('id', $value)->exists();

                    if (! $inBank && ! $inCurriculum) {
                        $fail('The selected question id is invalid.');
                    }
                },
            ],
            'user_transcript' => ['required', 'string', 'max:2000'],
        ];
    }
}