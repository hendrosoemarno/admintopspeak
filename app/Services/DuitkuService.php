<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\PaymentGatewaySetting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\UserSubscriptionRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Integrasi Payment Gateway Duitku (Merchant API v2 - webapi):
 * - Get Payment Method          : daftar kanal pembayaran aktif merchant
 * - Create Transaction (inquiry): membuat transaksi & paymentUrl untuk satu kanal
 * - Verifikasi & proses callback (notify) pembayaran
 * - Aktivasi langganan Premium setelah pembayaran sukses
 *
 * Signature: HMAC-SHA256 (bukan sha256 plain). stringToSign tergantung endpoint:
 * - getpaymentmethod : merchantcode + amount + datetime
 * - v2/inquiry       : merchantCode + merchantOrderId + paymentAmount
 * - callback         : merchantCode + amount + merchantOrderId
 */
class DuitkuService
{
    public const RESULT_SUCCESS = '00';

    public function __construct(
        private readonly UserSubscriptionRepository $subscriptions,
        private readonly UserRepository $users,
    ) {
    }

    /**
     * Daftar kanal pembayaran aktif untuk nominal tertentu.
     *
     * @return array<int, array<string, string>>
     */
    public function paymentMethods(float $amount): array
    {
        $settings = PaymentGatewaySetting::current();
        $datetime = now()->format('Y-m-d H:i:s');

        $response = Http::asJson()
            ->timeout(20)
            ->post($this->baseUrl().'/api/merchant/paymentmethod/getpaymentmethod', [
                'merchantcode' => $settings->effectiveMerchantCode(),
                'amount' => (int) $amount,
                'datetime' => $datetime,
                'signature' => hash_hmac('sha256', $settings->effectiveMerchantCode().(int) $amount.$datetime, $settings->effectiveApiKey()),
            ]);

        if ($response->failed() || data_get($response->json(), 'responseCode') !== self::RESULT_SUCCESS) {
            throw new \RuntimeException(
                'Gagal mengambil kanal pembayaran Duitku: '.$this->errorDetail($response),
            );
        }

        return collect($response->json('paymentFee') ?? [])
            ->map(fn (array $method) => [
                'code' => (string) $method['paymentMethod'],
                'name' => (string) $method['paymentName'],
                'image' => (string) $method['paymentImage'],
                'fee' => (string) ($method['totalFee'] ?? '0'),
            ])
            ->values()
            ->all();
    }

    /**
     * Membuat transaksi (inquiry) untuk satu kanal & menyimpan langganan PENDING.
     * Paket diambil dari Master Paket Langganan (SubscriptionPlan).
     *
     * @param  string|null  $paymentMethod  kode kanal dari paymentMethods(); bila null,
     *                                      dipilih kanal pertama yang tersedia.
     */
    public function purchase(User $user, SubscriptionPlan $plan, ?string $paymentMethod = null): array
    {
        $settings = PaymentGatewaySetting::current();
        $amount = $plan->effectivePrice();
        $status = $plan->premiumStatus();

        if ($paymentMethod === null || trim($paymentMethod) === '') {
            $methods = $this->paymentMethods($amount);
            $paymentMethod = $methods[0]['code'] ?? '';

            if ($paymentMethod === '') {
                throw new \RuntimeException('Tidak ada kanal pembayaran Duitku yang tersedia.');
            }
        }

        $merchantOrderId = 'TP'.date('YmdHis').strtoupper(Str::random(8));

        $response = Http::asJson()
            ->timeout(20)
            ->post($this->baseUrl().'/api/merchant/v2/inquiry', [
                'merchantCode' => $settings->effectiveMerchantCode(),
                'paymentAmount' => (int) $amount,
                'paymentMethod' => $paymentMethod,
                'merchantOrderId' => $merchantOrderId,
                'productDetails' => $plan->description ?: $plan->name,
                'additionalParam' => '',
                'merchantUserInfo' => '',
                'customerVaName' => Str::limit($user->name ?: 'TopSpeak User', 19, ''),
                'email' => $user->email,
                'phoneNumber' => (string) ($user->phone_number ?? ''),
                'itemDetails' => [[
                    'name' => $plan->name,
                    'price' => (int) $amount,
                    'quantity' => 1,
                ]],
                'callbackUrl' => $settings->effectiveNotifyUrl(),
                'returnUrl' => $settings->effectiveReturnUrl(),
                'signature' => $this->inquirySignature($merchantOrderId, (int) $amount),
                'expiryPeriod' => 60,
            ]);

        if ($response->failed() || data_get($response->json(), 'statusCode') !== self::RESULT_SUCCESS) {
            throw new \RuntimeException(
                'Gagal membuat transaksi Duitku: '.$this->errorDetail($response),
            );
        }

        $checkoutUrl = (string) $response->json('paymentUrl');
        $reference = (string) ($response->json('reference') ?? $merchantOrderId);

        $this->subscriptions->createForPurchase(
            userId: $user->id,
            planId: $plan->id,
            status: $status,
            merchantOrderId: $merchantOrderId,
            amount: $amount,
            checkoutUrl: $checkoutUrl,
            paymentRef: $reference,
            paymentMethod: $paymentMethod,
        );

        $payload = [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'subscription_status' => $status->value,
            'merchant_order_id' => $merchantOrderId,
            'amount' => $amount,
            'payment_status' => 'PENDING',
            'payment_method' => $paymentMethod,
            'checkout_url' => $checkoutUrl,
        ];

        foreach (['vaNumber' => 'va_number', 'qrString' => 'qr_string', 'appUrl' => 'app_url'] as $duitkuKey => $payloadKey) {
            if ($response->json($duitkuKey) !== null) {
                $payload[$payloadKey] = $response->json($duitkuKey);
            }
        }

        return $payload;
    }

    public function verifyCallbackSignature(array $params): bool
    {
        $settings = PaymentGatewaySetting::current();

        $signature = (string) ($params['signature'] ?? '');
        $computed = hash_hmac('sha256',
            $settings->effectiveMerchantCode().($params['amount'] ?? '').($params['merchantOrderId'] ?? ''),
            $settings->effectiveApiKey(),
        );

        return hash_equals($computed, $signature);
    }

    /**
     * Memproses callback Duitku. Mengembalikan payload untuk respons webhook.
     *
     * @throws \RuntimeException saat signature tidak valid.
     */
    public function processCallback(array $params): array
    {
        if (! $this->verifyCallbackSignature($params)) {
            throw new \RuntimeException('Invalid Duitku callback signature.');
        }

        $merchantOrderId = $params['merchantOrderId'] ?? '';
        $resultCode = $params['resultCode'] ?? '';

        $subscription = $this->subscriptions->findByMerchantOrderId($merchantOrderId);

        if (! $subscription) {
            throw new \RuntimeException('Merchant order tidak ditemukan.', 404);
        }

        if ($resultCode !== self::RESULT_SUCCESS) {
            $this->subscriptions->markExpired($subscription);

            return [
                'activated' => false,
                'merchant_order_id' => $merchantOrderId,
                'payment_status' => 'EXPIRED',
            ];
        }

        if ($subscription->payment_status->value === 'PAID') {
            // Idempoten. Bila user sudah premium aktif, jangan ubah apa pun.
            // Jika belum (mis. status premium sempat di-reset/di-expire), aktifkan ulang
            // sesuai paket agar callback sukses yang diulang tetap menegakkan status.
            $user = $subscription->user;

            if ($user && ! $user->isPremiumActive()) {
                $plan = $subscription->plan;
                $status = $plan !== null
                    ? $plan->premiumStatus()
                    : SubscriptionStatus::from($subscription->status->value);
                $expiresAt = $plan !== null
                    ? $plan->expiresFrom(now())
                    : now()->addDays(30);

                $this->users->activatePremium($user, $status, $expiresAt);
            }

            return [
                'activated' => true,
                'merchant_order_id' => $merchantOrderId,
                'payment_status' => 'PAID',
            ];
        }

        // TIME: aktifkan premium sesuai durasi paket (fallback 30 hari bila paket tak dikenal).
        $this->subscriptions->deactivateActiveFor($subscription->user_id);

        $plan = $subscription->plan;
        $status = $plan !== null
            ? $plan->premiumStatus()
            : SubscriptionStatus::from($subscription->status->value);
        $expiresAt = $plan !== null
            ? $plan->expiresFrom(now())
            : now()->addDays(30);

        $paid = $this->subscriptions->markPaid($subscription, (string) ($params['paymentCode'] ?? ''), $expiresAt);
        $this->users->activatePremium($paid->user, $status, $expiresAt);

        return [
            'activated' => true,
            'merchant_order_id' => $merchantOrderId,
            'payment_status' => 'PAID',
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    private function baseUrl(): string
    {
        return PaymentGatewaySetting::current()->effectiveBaseUrl().'/webapi';
    }

    private function inquirySignature(string $merchantOrderId, int $amount): string
    {
        $settings = PaymentGatewaySetting::current();

        return hash_hmac('sha256',
            $settings->effectiveMerchantCode().$merchantOrderId.$amount,
            $settings->effectiveApiKey(),
        );
    }

    private function errorDetail($response): string
    {
        $message = data_get($response->json(), 'statusMessage')
            ?? data_get($response->json(), 'responseMessage')
            ?? data_get($response->json(), 'Message')
            ?? '';

        if ($message === '') {
            return 'HTTP '.$response->status().' ('.$response->reason().')';
        }

        return $message;
    }
}