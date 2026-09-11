<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethodsRequest;
use App\Http\Requests\PurchaseSubscriptionRequest;
use App\Http\Resources\ApiResponse;
use App\Models\PaymentGatewaySetting;
use App\Models\SubscriptionPlan;
use App\Services\DuitkuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private readonly DuitkuService $duitku)
    {
    }

    /** Katalog paket yang dijual (Master Paket Langganan, status ACTIVE). */
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::query()
            ->where('status', \App\Enums\PlanStatus::ACTIVE->value)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SubscriptionPlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'features' => $plan->features ?? [],
                'price' => $plan->effectivePrice(),
                'original_price' => (float) $plan->price_original,
                'duration_value' => $plan->duration_value,
                'duration_unit' => $plan->duration_unit,
                'duration_label' => $plan->durationLabel(),
                'badge_promo' => $plan->badge_promo,
            ])
            ->values()
            ->all();

        return ApiResponse::success('Daftar paket langganan.', ['plans' => $plans]);
    }

    public function purchase(PurchaseSubscriptionRequest $request): JsonResponse
    {
        if (! PaymentGatewaySetting::current()->effectiveIsEnabled()) {
            return ApiResponse::error('Pembayaran sedang nonaktif. Silakan coba lagi nanti.', 422);
        }

        $plan = SubscriptionPlan::query()
            ->where('id', $request->validated('plan_id'))
            ->where('status', \App\Enums\PlanStatus::ACTIVE->value)
            ->first();

        if ($plan === null) {
            return ApiResponse::error('Paket tidak ditemukan atau tidak aktif.', 404);
        }

        $payload = $this->duitku->purchase(
            user: $request->user(),
            plan: $plan,
            paymentMethod: $request->validated('payment_method'),
        );

        return ApiResponse::success('Checkout Duitku dibuat. Lanjutkan pembayaran.', $payload, 201);
    }

    public function paymentMethods(PaymentMethodsRequest $request): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($request->validated('plan_id'));

        $methods = $this->duitku->paymentMethods($plan->effectivePrice());

        return ApiResponse::success('Daftar kanal pembayaran.', [
            'plan_id' => $plan->id,
            'amount' => $plan->effectivePrice(),
            'methods' => $methods,
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::channel('duitku')->info('duitku-callback:incoming', [
            'params' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            $result = $this->duitku->processCallback($request->all());

            \Illuminate\Support\Facades\Log::channel('duitku')->info('duitku-callback:ok', ['result' => $result]);
        } catch (\RuntimeException $e) {
            \Illuminate\Support\Facades\Log::channel('duitku')->warning('duitku-callback:error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return ApiResponse::error($e->getMessage(), $e->getCode() >= 400 ? $e->getCode() : 403);
        }

        return ApiResponse::success('Callback diproses', $result);
    }
}