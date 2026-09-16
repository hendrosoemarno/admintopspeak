<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\UserSessionQuotaLog;

/**
 * Penyesuaian sisa sesi user (Session Counter) dengan audit log.
 * Mendukung mode ADD (+) dan SET (=) dengan pencatatan before/after/delta + alasan.
 */
class SessionQuotaService
{
    /**
     * Tambah sisa sesi user (+delta) dan catat log.
     * delta harus integer (bisa negatif untuk koreksi/pengurangan).
     */
    public function add(int $userId, int $delta, string $reason, ?int $adminId = null): User
    {
        $user = User::findOrFail($userId);

        if ($delta === 0) {
            return $user;
        }

        $before = (int) $user->remaining_trial_sessions;
        $after = max(0, $before + $delta);

        $this->applyAndLog($user, $before, $after, $reason, $adminId);

        return $user->fresh();
    }

    /**
     * Set sisa sesi user secara presisi (=nil) dan catat log.
     */
    public function set(int $userId, int $newCount, string $reason, ?int $adminId = null): User
    {
        $user = User::findOrFail($userId);

        $before = (int) $user->remaining_trial_sessions;

        if ($before === $newCount) {
            return $user;
        }

        $this->applyAndLog($user, $before, $newCount, $reason, $adminId);

        return $user->fresh();
    }

    private function applyAndLog(User $user, int $before, int $after, string $reason, ?int $adminId): void
    {
        $user->remaining_trial_sessions = $after;
        $user->save();

        $user->syncTotalFreeSessionsGranted();

        UserSessionQuotaLog::create([
            'user_id' => $user->id,
            'admin_id' => $adminId,
            'before_count' => $before,
            'after_count' => $after,
            'delta' => $after - $before,
            'reason' => $reason ?: null,
        ]);
    }
}
