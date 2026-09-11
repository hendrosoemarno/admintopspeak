<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'duitku.merchant_code' => 'TESTMERCHANT',
            'duitku.api_key' => 'test-secret-key',
            'duitku.sandbox' => true,
        ]);
    }

    private function timePlan(array $overrides = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Paket Bulanan Uji',
            'description' => 'Akses premium 30 hari.',
            'type' => 'TIME',
            'duration_value' => 30,
            'duration_unit' => 'DAY',
            'price_original' => 49000,
            'status' => 'ACTIVE',
        ], $overrides));
    }

    private function callbackParams(string $merchantOrderId, float $amount, string $resultCode, ?string $signature = null): array
    {
        $signature ??= hash_hmac('sha256',
            config('duitku.merchant_code').(int) $amount.$merchantOrderId,
            config('duitku.api_key'),
        );

        return [
            'merchantCode' => config('duitku.merchant_code'),
            'merchantOrderId' => $merchantOrderId,
            'amount' => (int) $amount,
            'resultCode' => $resultCode,
            'reference' => 'REF-'.strtoupper(substr($merchantOrderId, -6)),
            'paymentCode' => 'VC',
            'signature' => $signature,
        ];
    }

    private function inquiryFake(): void
    {
        Http::fake([
            'sandbox.duitku.com/webapi/api/merchant/v2/inquiry' => Http::response([
                'merchantCode' => 'TESTMERCHANT',
                'merchantOrderId' => 'TP202608130001AAAAAAAA',
                'reference' => 'REF-123456',
                'paymentAmount' => 49000,
                'paymentUrl' => 'https://sandbox.duitku.com/topup/v2/TopUp.aspx?reference=REF-123456',
                'vaNumber' => '7007014001444348',
                'statusCode' => '00',
                'statusMessage' => 'SUCCESS',
            ], 200),
        ]);
    }

    private function paymentMethodsFake(): void
    {
        Http::fake([
            'sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod' => Http::response([
                'paymentFee' => [
                    ['paymentMethod' => 'VA', 'paymentName' => 'MAYBANK VA', 'paymentImage' => 'https://images.duitku.com/hotlink-ok/VA.PNG', 'totalFee' => '0'],
                    ['paymentMethod' => 'VC', 'paymentName' => 'CREDIT CARD', 'paymentImage' => 'https://images.duitku.com/hotlink-ok/VC.PNG', 'totalFee' => '0'],
                ],
                'responseCode' => '00',
                'responseMessage' => 'SUCCESS',
            ], 200),
        ]);
    }

    public function test_plans_endpoint_lists_active_master_plans_only(): void
    {
        $this->timePlan();
        $this->timePlan(['name' => 'Paket Tahunan Uji', 'duration_unit' => 'YEAR', 'price_original' => 399000]);
        $this->timePlan(['name' => 'Paket Arsip', 'status' => 'ARCHIVED']);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('api/v1/subscriptions/plans')
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertCount(2, $response->json('data.plans'));
        $names = collect($response->json('data.plans'))->pluck('name');
        $this->assertTrue($names->contains('Paket Bulanan Uji'));
        $this->assertTrue($names->contains('Paket Tahunan Uji'));
        $this->assertFalse($names->contains('Paket Arsip'));
    }

    public function test_purchase_creates_duitku_transaction_from_master_plan(): void
    {
        $this->inquiryFake();
        $plan = $this->timePlan();

        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);
        Sanctum::actingAs($user);

        $response = $this->postJson('api/v1/subscriptions/purchase', [
            'plan_id' => $plan->id,
            'payment_method' => 'VC',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.plan_id', $plan->id)
            ->assertJsonPath('data.plan_name', 'Paket Bulanan Uji')
            ->assertJsonPath('data.payment_status', 'PENDING')
            ->assertJsonPath('data.payment_method', 'VC')
            ->assertJsonPath('data.amount', 49000)
            ->assertJsonPath('data.checkout_url', 'https://sandbox.duitku.com/topup/v2/TopUp.aspx?reference=REF-123456');

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => 49000,
            'payment_status' => 'PENDING',
            'is_active' => false,
            'payment_provider' => 'duitku',
            'payment_method' => 'VC',
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'
                && $request['merchantCode'] === 'TESTMERCHANT'
                && $request['paymentMethod'] === 'VC'
                && hash_hmac('sha256',
                    config('duitku.merchant_code').$request['merchantOrderId'].(int) $request['paymentAmount'],
                    config('duitku.api_key'),
                ) === $request['signature'];
        });
    }

    public function test_purchase_without_payment_method_falls_back_to_first_available(): void
    {
        $this->paymentMethodsFake();
        $this->inquiryFake();

        $plan = $this->timePlan();
        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);
        Sanctum::actingAs($user);

        $this->postJson('api/v1/subscriptions/purchase', ['plan_id' => $plan->id])
            ->assertCreated()
            ->assertJsonPath('data.payment_method', 'VA');
    }

    public function test_payment_methods_endpoint_lists_channels_for_plan(): void
    {
        $this->paymentMethodsFake();
        $plan = $this->timePlan();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('api/v1/subscriptions/payment-methods?plan_id='.$plan->id)
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.plan_id', $plan->id)
            ->assertJsonPath('data.amount', 49000)
            ->assertJsonStructure([
                'data' => ['plan_id', 'amount', 'methods' => [['code', 'name', 'image', 'fee']]],
            ]);
    }

    public function test_purchase_rejects_archived_plan(): void
    {
        $plan = $this->timePlan(['status' => 'ARCHIVED']);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/subscriptions/purchase', ['plan_id' => $plan->id])
            ->assertNotFound()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Paket tidak ditemukan atau tidak aktif.');
    }

    public function test_purchase_requires_authentication(): void
    {
        $this->postJson('api/v1/subscriptions/purchase', ['plan_id' => 1])
            ->assertUnauthorized();
    }

    public function test_valid_callback_activates_time_plan_premium(): void
    {
        $plan = $this->timePlan();
        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PREMIUM_MONTHLY->value,
            'payment_provider' => 'duitku',
            'payment_ref' => 'REF-123456',
            'merchant_order_id' => 'TP202608130001AAAAAAAA',
            'amount' => 49000,
            'payment_status' => 'PENDING',
            'is_active' => false,
        ]);

        $this->postJson('api/v1/subscriptions/duitku-callback', $this->callbackParams(
            merchantOrderId: 'TP202608130001AAAAAAAA',
            amount: 49000,
            resultCode: '00',
        ))->assertOk()
            ->assertJsonPath('data.activated', true)
            ->assertJsonPath('data.payment_status', 'PAID');

        $subscription->refresh();
        $this->assertSame('PAID', $subscription->payment_status->value);
        $this->assertTrue($subscription->is_active);
        $this->assertNotNull($subscription->expires_at);
        $this->assertTrue($subscription->expires_at->isAfter(now()->addDays(28)));

        $user->refresh();
        $this->assertTrue($user->subscription_status->isPremium());
        $this->assertSame(SubscriptionStatus::PREMIUM_MONTHLY, $user->subscription_status);
    }

    public function test_callback_with_invalid_signature_is_rejected(): void
    {
        $user = User::factory()->create();

        UserSubscription::create([
            'user_id' => $user->id,
            'status' => SubscriptionStatus::PREMIUM_MONTHLY->value,
            'payment_provider' => 'duitku',
            'merchant_order_id' => 'TP202608130001BBBBBBBB',
            'amount' => 49000,
            'payment_status' => 'PENDING',
            'is_active' => false,
        ]);

        $this->postJson('api/v1/subscriptions/duitku-callback', $this->callbackParams(
            merchantOrderId: 'TP202608130001BBBBBBBB',
            amount: 49000,
            resultCode: '00',
            signature: 'invalid-signature-value',
        ))->assertForbidden();

        $user->refresh();
        $this->assertFalse($user->subscription_status->isPremium());
    }

    public function test_callback_with_failed_result_expires_subscription(): void
    {
        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);

        UserSubscription::create([
            'user_id' => $user->id,
            'status' => SubscriptionStatus::PREMIUM_MONTHLY->value,
            'payment_provider' => 'duitku',
            'merchant_order_id' => 'TP202608130001CCCCCCCC',
            'amount' => 49000,
            'payment_status' => 'PENDING',
            'is_active' => false,
        ]);

        $this->postJson('api/v1/subscriptions/duitku-callback', $this->callbackParams(
            merchantOrderId: 'TP202608130001CCCCCCCC',
            amount: 49000,
            resultCode: '99',
        ))->assertOk()
            ->assertJsonPath('data.activated', false)
            ->assertJsonPath('data.payment_status', 'EXPIRED');

        $user->refresh();
        $this->assertFalse($user->subscription_status->isPremium());
    }
}