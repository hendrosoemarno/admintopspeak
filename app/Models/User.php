<?php

namespace App\Models;

use App\Enums\CefrLevel;
use App\Enums\SubscriptionStatus;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'email', 'password',
    'device_uuid', 'phone_number',
    'current_cefr_level', 'remaining_trial_sessions',
    'subscription_status', 'subscription_expires_at', 'is_admin',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'current_cefr_level' => CefrLevel::class,
            'subscription_status' => SubscriptionStatus::class,
            'subscription_expires_at' => 'datetime',
        ];
    }

    public function conversationLogs(): HasMany
    {
        return $this->hasMany(ConversationLog::class);
    }

    public function levelHistories(): HasMany
    {
        return $this->hasMany(UserLevelHistory::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where('is_active', true)
            ->latestOfMany();
    }

    public function createdTopics(): HasMany
    {
        return $this->hasMany(ThematicTopic::class, 'created_by');
    }

    public function isPremiumActive(): bool
    {
        if (! $this->subscription_status->isPremium()) {
            return false;
        }

        return $this->subscription_expires_at === null || $this->subscription_expires_at->isFuture();
    }

    /**
     * Akun guest (belum registrasi email): dibuat lewat register-device.
     * Penanda eksplisit agar app tidak perlu menebak lewat heuristik nama/email.
     */
    public function isGuest(): bool
    {
        return $this->name === 'Guest Learner'
            || (str_starts_with((string) $this->email, 'guest-')
                && str_ends_with((string) $this->email, '@topspeak.app'));
    }
}
