<?php

namespace App\Livewire\Admin\PaymentGateway;

use App\Models\PaymentGatewaySetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Settings extends Component
{
    public bool $is_enabled = true;

    public bool $sandbox = true;

    public string $merchant_code = '';

    public string $api_key = '';

    public string $notify_url = '';

    public string $return_url = '';

    public function mount(): void
    {
        $settings = PaymentGatewaySetting::current();

        $this->is_enabled = $settings->effectiveIsEnabled();
        $this->sandbox = $settings->effectiveSandbox();
        $this->merchant_code = $settings->merchant_code ?? '';
        $this->api_key = $settings->api_key ?? '';
        $this->notify_url = $settings->notify_url ?? '';
        $this->return_url = $settings->return_url ?? '';
    }

    protected function rules(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sandbox' => 'boolean',
            'merchant_code' => 'nullable|string|max:40',
            'api_key' => 'nullable|string|max:255',
            'notify_url' => 'nullable|url|max:500',
            'return_url' => 'nullable|url|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();

        PaymentGatewaySetting::current()->update([
            'is_enabled' => $this->is_enabled,
            'sandbox' => $this->sandbox,
            'merchant_code' => trim($this->merchant_code) !== '' ? trim($this->merchant_code) : null,
            'api_key' => trim($this->api_key) !== '' ? trim($this->api_key) : null,
            'notify_url' => trim($this->notify_url) !== '' ? trim($this->notify_url) : null,
            'return_url' => trim($this->return_url) !== '' ? trim($this->return_url) : null,
        ]);

        $this->dispatch('flash', message: 'Konfigurasi Payment Gateway berhasil disimpan.');
    }

    #[Computed]
    public function environment(): string
    {
        return $this->sandbox ? 'Sandbox' : 'Production';
    }

    #[Computed]
    public function apiKeyConfigured(): bool
    {
        return PaymentGatewaySetting::current()->effectiveApiKey() !== '';
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        return view('livewire.admin.payment-gateway.settings')
            ->layout('layouts.app', ['title' => 'Payment Gateway Settings']);
    }
}