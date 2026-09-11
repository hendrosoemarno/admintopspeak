<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Livewire\Admin\PaymentGateway\Settings as PaymentGatewaySettings;
use App\Livewire\Admin\PaymentTest\Index as PaymentTestIndex;
use App\Models\PaymentGatewaySetting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPaymentGatewaySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_payment_gateway_settings_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.payment-gateway.settings'))
            ->assertOk()
            ->assertSee('Konfigurasi Duitku');
    }

    public function test_admin_can_save_payment_gateway_settings(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(PaymentGatewaySettings::class)
            ->set('is_enabled', true)
            ->set('sandbox', false)
            ->set('merchant_code', 'PRODMERCHANT')
            ->set('api_key', 'prod-secret-key')
            ->set('notify_url', '')
            ->set('return_url', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_gateway_settings', [
            'merchant_code' => 'PRODMERCHANT',
            'api_key' => 'prod-secret-key',
            'sandbox' => false,
        ]);

        $settings = PaymentGatewaySetting::current();
        $this->assertSame('PRODMERCHANT', $settings->effectiveMerchantCode());
        $this->assertSame('prod-secret-key', $settings->effectiveApiKey());
        $this->assertFalse($settings->effectiveSandbox());
        $this->assertSame('https://passport.duitku.com', $settings->effectiveBaseUrl());
    }

    public function test_purchase_uses_db_settings_over_env(): void
    {
        PaymentGatewaySetting::current()->update([
            'is_enabled' => true,
            'merchant_code' => 'PRODMERCHANT',
            'api_key' => 'prod-secret-key',
            'sandbox' => false,
        ]);

        $plan = $this->timePlan(['price_original' => 49000]);

        Http::fake([
            'passport.duitku.com/webapi/api/merchant/v2/inquiry' => Http::response([
                'statusCode' => '00',
                'statusMessage' => 'SUCCESS',
                'paymentUrl' => 'https://passport.duitku.com/topup/v2/TopUp.aspx?x=abc',
                'reference' => 'REF-123',
            ], 200),
        ]);

        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);
        Sanctum::actingAs($user);

        $this->postJson('api/v1/subscriptions/purchase', [
            'plan_id' => $plan->id,
            'payment_method' => 'VC',
        ])
            ->assertCreated()
            ->assertJsonPath('data.checkout_url', 'https://passport.duitku.com/topup/v2/TopUp.aspx?x=abc');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry'
                && $request['merchantCode'] === 'PRODMERCHANT'
                && hash_hmac('sha256',
                    'PRODMERCHANT'.$request['merchantOrderId'].(int) $request['paymentAmount'],
                    'prod-secret-key',
                ) === $request['signature'];
        });
    }

    public function test_disabled_gateway_rejects_purchase(): void
    {
        PaymentGatewaySetting::current()->update(['is_enabled' => false]);

        $plan = $this->timePlan();
        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);
        Sanctum::actingAs($user);

        $this->postJson('api/v1/subscriptions/purchase', ['plan_id' => $plan->id])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Pembayaran sedang nonaktif. Silakan coba lagi nanti.');

        $this->assertDatabaseCount('user_subscriptions', 0);
    }

    public function test_callback_signature_verified_with_db_settings(): void
    {
        PaymentGatewaySetting::current()->update([
            'merchant_code' => 'PRODMERCHANT',
            'api_key' => 'prod-secret-key',
        ]);

        $plan = $this->timePlan();
        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);

        \App\Models\UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PREMIUM_MONTHLY->value,
            'payment_provider' => 'duitku',
            'payment_ref' => 'REF-123',
            'merchant_order_id' => 'TP202609080001AAAAAAAA',
            'amount' => 49000,
            'payment_status' => 'PENDING',
            'is_active' => false,
        ]);

        $signature = hash_hmac('sha256',
            'PRODMERCHANT'.'49000'.'TP202609080001AAAAAAAA',
            'prod-secret-key',
        );

        $this->postJson('api/v1/subscriptions/duitku-callback', [
            'merchantCode' => 'PRODMERCHANT',
            'merchantOrderId' => 'TP202609080001AAAAAAAA',
            'amount' => 49000,
            'resultCode' => '00',
            'reference' => 'REF-123',
            'paymentCode' => 'VA',
            'signature' => $signature,
        ])->assertOk()
            ->assertJsonPath('data.activated', true);
    }

    public function test_payment_test_can_create_checkout_and_simulate_success_callback(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        PaymentGatewaySetting::current()->update(['is_enabled' => true]);

        $plan = $this->timePlan();
        $user = User::factory()->create([
            'email' => 'test-payment@test.com',
            'subscription_status' => SubscriptionStatus::FREE,
        ]);

        $this->fakeDuitkuEndpoints();

        $component = Livewire::test(PaymentTestIndex::class);

        $component
            ->assertSet('plan_id', $plan->id)
            ->assertSet('payment_method', 'VA')
            ->set('searchEmail', 'test-payment@test.com')
            ->call('findUser')
            ->assertSet('selectedUserId', $user->id)
            ->call('createCheckout')
            ->assertSet('success', 'Checkout Duitku berhasil dibuat. Lanjutkan ke halaman pembayaran.')
            ->assertSet('checkout.payment_status', 'PENDING')
            ->assertSet('checkout.payment_method', 'VA')
            ->assertSet('checkout.plan_id', $plan->id);

        $merchantOrderId = $component->get('checkout.merchant_order_id');
        $this->assertNotEmpty($merchantOrderId);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'merchant_order_id' => $merchantOrderId,
            'payment_status' => 'PENDING',
            'payment_method' => 'VA',
        ]);

        $component
            ->call('simulateSuccessCallback')
            ->assertSet('checkout.payment_status', 'PAID');

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'merchant_order_id' => $merchantOrderId,
            'payment_status' => 'PAID',
        ]);

        $user->refresh();
        $this->assertTrue($user->isPremiumActive());
        $this->assertSame(SubscriptionStatus::PREMIUM_MONTHLY, $user->subscription_status);
    }

    public function test_payment_test_ensure_test_user_creates_deterministic_account(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->timePlan();
        $this->fakeDuitkuEndpoints();

        Livewire::test(PaymentTestIndex::class)
            ->call('ensureTestUser')
            ->assertSet('searchEmail', 'test-payment@test.com');

        $this->assertDatabaseHas('users', ['email' => 'test-payment@test.com']);
        $this->assertTrue(Hash::check('password123', User::where('email', 'test-payment@test.com')->first()->password));
    }

    private function timePlan(array $overrides = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Paket Bulanan Uji',
            'type' => 'TIME',
            'duration_value' => 30,
            'duration_unit' => 'DAY',
            'price_original' => 49000,
            'status' => 'ACTIVE',
        ], $overrides));
    }

    private function fakeDuitkuEndpoints(): void
    {
        Http::fake([
            'sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod' => Http::response([
                'paymentFee' => [
                    ['paymentMethod' => 'VA', 'paymentName' => 'MAYBANK VA', 'paymentImage' => 'https://images.duitku.com/hotlink-ok/VA.PNG', 'totalFee' => '0'],
                ],
                'responseCode' => '00',
                'responseMessage' => 'SUCCESS',
            ], 200),
            'sandbox.duitku.com/webapi/api/merchant/v2/inquiry' => Http::response([
                'statusCode' => '00',
                'statusMessage' => 'SUCCESS',
                'paymentUrl' => 'https://sandbox.duitku.com/topup/v2/TopUp.aspx?x=abc',
                'reference' => 'REF-123',
            ], 200),
        ]);
    }
}