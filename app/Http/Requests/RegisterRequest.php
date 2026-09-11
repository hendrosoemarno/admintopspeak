<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            // unique tidak wajib: email yang sudah terdaftar dipakai untuk registrasi ulang (ganti perangkat)
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}