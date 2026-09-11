<?php

namespace App\Models;

use App\Enums\PlanStatus;
use App\Enums\PlanType;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'name', 'description', 'features', 'price_original', 'price_discount',
    'type', 'duration_value', 'duration_unit',
    'status', 'badge_promo', 'sort_order',
])]
class SubscriptionPlan extends Model
{
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_original' => 'decimal:2',
            'price_discount' => 'decimal:2',
            'type' => PlanType::class,
            'status' => PlanStatus::class,
        ];
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function displayPrice(): string
    {
        return number_format((float) ($this->price_discount ?? $this->price_original), 0, ',', '.');
    }

    public function durationLabel(): string
    {
        if ($this->duration_unit === null) {
            return '—';
        }

        $label = match (strtoupper($this->duration_unit)) {
            'DAY' => 'hari',
            'MONTH' => 'bulan',
            'YEAR' => 'tahun',
            default => '',
        };

        return $this->duration_value.' '.$label;
    }

    /** Harga yang dibayar user (diskon bila ada). */
    public function effectivePrice(): float
    {
        return (float) ($this->price_discount ?? $this->price_original);
    }

    /**
     * Status premium untuk paket TIME. Unit YEAR → PREMIUM_YEARLY, selain itu → PREMIUM_MONTHLY.
     * Masa aktif aktual tetap mengikuti durasi paket (expires_at), bukan label enum.
     */
    public function premiumStatus(): SubscriptionStatus
    {
        return strtoupper((string) $this->duration_unit) === 'YEAR'
            ? SubscriptionStatus::PREMIUM_YEARLY
            : SubscriptionStatus::PREMIUM_MONTHLY;
    }

    /** Tanggal kedaluwarsa paket TIME bila mulai pada $start. */
    public function expiresFrom(Carbon $start): Carbon
    {
        $value = (int) $this->duration_value;

        return match (strtoupper((string) $this->duration_unit)) {
            'DAY' => $start->copy()->addDays($value),
            'YEAR' => $start->copy()->addYears($value),
            default => $start->copy()->addMonths($value),
        };
    }
}