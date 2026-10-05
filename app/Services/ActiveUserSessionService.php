<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ActiveUserSessionService
{
    public const IDLE_TIMEOUT_MINUTES = 15;

    public function activate(string $role, int $userId, string $sessionId): void
    {
        DB::table('active_user_sessions')->updateOrInsert(
            ['role' => $role, 'user_id' => $userId],
            [
                'session_id' => $sessionId,
                'last_activity_at' => now(),
            ],
        );
    }

    public function touch(string $role, int $userId, string $sessionId): bool
    {
        return DB::table('active_user_sessions')
            ->where('role', $role)
            ->where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->update(['last_activity_at' => now()]) === 1;
    }

    public function current(string $role, int $userId): ?object
    {
        return DB::table('active_user_sessions')
            ->where('role', $role)
            ->where('user_id', $userId)
            ->first();
    }

    public function isCurrent(string $role, int $userId, string $sessionId): bool
    {
        $activeSession = $this->current($role, $userId);

        return $activeSession
            && hash_equals((string) $activeSession->session_id, $sessionId)
            && ! $this->hasExpired($activeSession);
    }

    public function hasExpired(object $activeSession): bool
    {
        return Carbon::parse($activeSession->last_activity_at)
            ->lte(now()->subMinutes(self::IDLE_TIMEOUT_MINUTES));
    }

    public function forget(string $role, int $userId, string $sessionId): void
    {
        DB::table('active_user_sessions')
            ->where('role', $role)
            ->where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->delete();
    }
}
