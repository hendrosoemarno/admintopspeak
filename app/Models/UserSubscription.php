<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionSource;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'plan_id', 'source', 'admin_note',
    'status', 'started_at', 'expires_at',
    'payment_provider', 'payment_ref', 'is_active',
    'merchant_order_id', 'amount', 'payment_status',
    'payment_method', 'checkout_url',
])]
class UserSubscription extends Model
{
    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'source' => SubscriptionSource::class,
            'payment_status' => PaymentStatus::class,
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function isActivePremium(): bool
    {
        return $this->is_active && $this->status->isPremium();
    }
}