<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestoreRememberedSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('auth_role') || ! $request->session()->has('auth_id')) {
            $remembered = AuthController::rememberedUserFromCookie($request);

            if ($remembered) {
                $request->session()->put('auth_role', $remembered['role']);
                $request->session()->put('auth_id', $remembered['id']);
                $request->session()->put('staff_type', $remembered['staff_type']);
                $request->session()->put('last_login_at', now()->timezone('Asia/Kuala_Lumpur')->toDateTimeString());
            }
        }

        return $next($request);
    }
}
