<?php

namespace App\Http\Middleware;

use App\Services\ActiveUserSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleActiveSession
{
    public function __construct(private readonly ActiveUserSessionService $activeSessions)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->session()->get('auth_role');
        $userId = (int) $request->session()->get('auth_id');

        if (! in_array($role, ['ahli', 'staff', 'admin'], true) || ! $userId) {
            return $next($request);
        }

        $activeSession = $this->activeSessions->current($role, $userId);

        if ($activeSession
            && hash_equals((string) $activeSession->session_id, $request->session()->getId())
            && ! $this->activeSessions->hasExpired($activeSession)) {
            return $next($request);
        }

        $timedOut = $activeSession
            && hash_equals((string) $activeSession->session_id, $request->session()->getId())
            && $this->activeSessions->hasExpired($activeSession);

        if ($activeSession && hash_equals((string) $activeSession->session_id, $request->session()->getId())) {
            $this->activeSessions->forget($role, $userId, $request->session()->getId());
        }

        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Cookie::queue(Cookie::forget('coopbest_remember'));

        return redirect()
            ->route('login')
            ->with('status', $timedOut
                ? 'Sesi anda telah tamat, sila log masuk semula.'
                : 'Sesi anda telah ditamatkan kerana akaun ini telah digunakan pada peranti lain. Sila log masuk semula.');
    }
}
