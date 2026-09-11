<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['is_enabled', 'merchant_code', 'api_key', 'sandbox', 'notify_url', 'return_url'])]
class PaymentGatewaySetting extends Model
{
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sandbox' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['id' => 1],
            [
                'is_enabled' => true,
                'merchant_code' => null,
                'api_key' => null,
                'sandbox' => null,
                'notify_url' => null,
                'return_url' => null,
            ]
        );
    }

    /** Nilai efektif: setting DB diutamakan, fallback ke config() / .env. */
    public function effectiveIsEnabled(): bool
    {
        return $this->is_enabled ?? true;
    }

    public function effectiveMerchantCode(): string
    {
        return $this->merchant_code ?: (string) config('duitku.merchant_code', '');
    }

    public function effectiveApiKey(): string
    {
        return $this->api_key ?: (string) config('duitku.api_key', '');
    }

    public function effectiveSandbox(): bool
    {
        return $this->sandbox ?? (bool) config('duitku.sandbox', true);
    }

    public function effectiveBaseUrl(): string
    {
        return $this->effectiveSandbox()
            ? 'https://sandbox.duitku.com'
            : 'https://passport.duitku.com';
    }

    public function effectiveNotifyUrl(): string
    {
        return $this->notify_url ?: (string) config('duitku.notify_url', '');
    }

    public function effectiveReturnUrl(): string
    {
        return $this->return_url ?: (string) config('duitku.return_url', '');
    }
}