<?php

namespace App\Repositories;

use App\Enums\SubscriptionStatus;
use App\Models\AppConfiguration;
use App\Models\User;
use Illuminate\Support\Str;

class UserRepository extends Repository
{
    protected function model(): string
    {
        return User::class;
    }

    public function findByDeviceUuid(string $deviceUuid): ?User
    {
        return $this->query()->where('device_uuid', $deviceUuid)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    public function firstOrCreateByDeviceUuid(string $deviceUuid): User
    {
        return User::firstOrCreate(
            ['device_uuid' => $deviceUuid],
            [
                'name' => 'Guest Learner',
                'email' => 'guest-'.Str::lower(Str::random(16)).'@topspeak.app',
                'password' => Str::password(32),
                'current_cefr_level' => 'A1',
                'remaining_trial_sessions' => AppConfiguration::initialFreeSessions(),
                'subscription_status' => SubscriptionStatus::FREE,
            ]
        );
    }

    public function profileWithRelations(User $user): User
    {
        return $user->load('levelHistories');
    }

    /**
     * Upgrade akun guest (yang sudah punya device_uuid & token) menjadi akun ber-email.
     */
    public function upgradeGuestToEmail(User $guest, string $name, string $email, string $password): User
    {
        $guest->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ])->save();

        return $guest->fresh();
    }

    /**
     * Registrasi ulang pada perangkat baru: pindahkan akun email yang sudah ada ke device_uuid
     * saat ini (guest lama dihapus bila tanpa data, di-orphan-kan bila sudah memiliki data).
     */
    public function reRegisterOnDevice(User $guest, User $account): User
    {
        $deviceUuid = $guest->device_uuid;

        $hasData = $guest->conversationLogs()->exists()
            || $guest->subscriptions()->exists()
            || $guest->levelHistories()->exists();

        if ($hasData) {
            $guest->forceFill(['device_uuid' => null])->save();
        } else {
            $guest->delete();
        }

        // Registrasi ulang dari perangkat baru: pindahkan akun email ke perangkat ini.
        // Kebijakan satu perangkat aktif: cabut semua token akun di perangkat lama.
        $account->tokens()->delete();

        $account->forceFill(['device_uuid' => $deviceUuid])->save();

        return $account->fresh();
    }

    public function activatePremium(User $user, SubscriptionStatus $status, \Illuminate\Support\Carbon $expiresAt): User
    {
        $user->forceFill([
            'subscription_status' => $status,
            'subscription_expires_at' => $expiresAt,
        ])->save();

        return $user->fresh();
    }

    public function expirePremium(User $user): User
    {
        $user->forceFill([
            'subscription_status' => SubscriptionStatus::FREE,
            'subscription_expires_at' => null,
        ])->save();

        return $user->fresh();
    }

    public function recordLevelPromotion(User $user, string $newLevel, string $sessionId, int $triggerScore): void
    {
        $previous = $user->current_cefr_level->value;

        \App\Models\UserLevelHistory::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'previous_level' => $previous,
            'new_level' => $newLevel,
            'trigger_score' => $triggerScore,
            'promotion_reason' => "Akuisisi skor {$triggerScore}/8 dari 4-turn berurutan memenuhi threshold ≥ 6.",
        ]);

        $user->update(['current_cefr_level' => $newLevel]);
        $user->refresh();
    }

    /**
     * Ubah level CEFR user secara manual oleh admin, dengan catatan riwayat.
     *
     * @param int|null $adminId  id admin yang melakukan perubahan
     */
    public function setUserLevel(User $user, string $newLevel, ?string $note = null, ?int $adminId = null): User
    {
        $previous = $user->current_cefr_level->value;

        if ($previous === $newLevel) {
            return $user->fresh();
        }

        \App\Models\UserLevelHistory::create([
            'user_id' => $user->id,
            'previous_level' => $previous,
            'new_level' => $newLevel,
            'promotion_reason' => $note
                ?: 'Diubah manual oleh admin'.($adminId ? " ({$adminId})" : ''),
        ]);

        $user->update(['current_cefr_level' => $newLevel]);

        return $user->fresh();
    }
}