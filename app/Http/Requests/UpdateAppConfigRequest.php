<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latest_app_version' => ['required', 'string', 'max:20'],
            'min_required_version' => ['required', 'string', 'max:20'],
            'is_force_update' => ['required', 'boolean'],
            'play_store_url' => ['required', 'url'],
            'update_message' => ['required', 'string'],
        ];
    }
}