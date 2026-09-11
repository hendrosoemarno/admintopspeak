<?php

namespace App\Livewire\Admin\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function mount(): void
    {
        $this->email = (string) config('admin.email', '');
    }

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function login(): void
    {
        $credentials = $this->validate();

        if (! Auth::attempt($credentials)) {
            $this->addError('password', 'Email atau kata sandi salah.');

            return;
        }

        $user = Auth::user();

        if (! $user->is_admin) {
            Auth::logout();
            $this->addError('email', 'Akun ini tidak memiliki akses admin.');

            return;
        }

        session()->regenerate();

        $this->redirect(route('admin.dashboard'));
    }

    public function render()
    {
        return view('livewire.admin.auth.login')->layout('layouts.guest', ['title' => 'Login Admin']);
    }
}