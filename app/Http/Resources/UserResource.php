<?php

namespace App\Http\Resources;

use App\Enums\SubscriptionStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource;
        $activeSubscription = $user->activeSubscription;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_guest' => $user->isGuest(),
            'device_uuid' => $user->device_uuid,
            'phone_number' => $user->phone_number,
            'current_cefr_level' => $user->current_cefr_level->value,
            'remaining_trial_sessions' => $user->remaining_trial_sessions,
            'total_free_sessions_granted' => (int) $user->total_free_sessions_granted,
            'subscription_status' => $user->subscription_status->value,
            'subscription' => [
                'status' => $user->subscription_status->value,
                'expires_at' => $user->subscription_expires_at?->toIso8601String(),
                'is_premium' => $user->isPremiumActive(),
            ],
            'level_history' => $user->relationLoaded('levelHistories')
                ? LevelHistoryResource::collection($user->levelHistories)
                : null,
        ];
    }
}