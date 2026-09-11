<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionStatus;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\Engine\QuotaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_premium_user_is_downgraded_to_free(): void
    {
        $user = User::factory()->create([
            'subscription_status' => SubscriptionStatus::PREMIUM_MONTHLY,
            'subscription_expires_at' => now()->subDay(),
            'remaining_trial_sessions' => 0,
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'status' => SubscriptionStatus::PREMIUM_MONTHLY->value,
            'payment_provider' => 'duitku',
            'merchant_order_id' => 'TP-EXP-0001',
            'amount' => 49000,
            'payment_status' => 'PAID',
            'is_active' => true,
        ]);

        Artisan::call('subscription:expire');
        $this->assertStringContainsString('1 langganan', Artisan::output());

        $user->refresh();
        $this->assertSame(SubscriptionStatus::FREE, $user->subscription_status);
        $this->assertNull($user->subscription_expires_at);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'merchant_order_id' => 'TP-EXP-0001',
            'is_active' => false,
        ]);
    }

    public function test_active_premium_user_is_not_downgraded(): void
    {
        $user = User::factory()->create([
            'subscription_status' => SubscriptionStatus::PREMIUM_YEARLY,
            'subscription_expires_at' => now()->addWeek(),
        ]);

        Artisan::call('subscription:expire');
        $this->assertStringContainsString('0 langganan', Artisan::output());

        $user->refresh();
        $this->assertSame(SubscriptionStatus::PREMIUM_YEARLY, $user->subscription_status);
    }

    public function test_quota_treats_expired_premium_as_free(): void
    {
        $expired = User::factory()->create([
            'subscription_status' => SubscriptionStatus::PREMIUM_MONTHLY,
            'subscription_expires_at' => now()->subHour(),
            'remaining_trial_sessions' => 0,
        ]);

        $active = User::factory()->create([
            'subscription_status' => SubscriptionStatus::PREMIUM_MONTHLY,
            'subscription_expires_at' => now()->addMonth(),
            'remaining_trial_sessions' => 0,
        ]);

        $quota = app(QuotaService::class);

        $this->assertFalse($quota->canStartSession($expired));
        $this->assertTrue($quota->canStartSession($active));
    }
}