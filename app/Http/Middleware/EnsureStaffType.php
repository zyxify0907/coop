<?php

namespace App\Http\Middleware;

use App\Models\Pekerja;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffType
{
    /**
     * Restrict a route only when the signed-in account is a staff account.
     * Non-staff roles continue to their controller, where their normal role
     * authorization is still enforced.
     */
    public function handle(Request $request, Closure $next, string ...$types): Response
    {
        $strict = in_array('strict', $types, true);
        $types = array_values(array_diff($types, ['strict']));

        if ($request->session()->get('auth_role') !== 'staff') {
            if ($request->session()->get('auth_role') === 'admin' && $request->routeIs('clothing-staff.dashboard.baju')) {
                return redirect()->route('admin.dashboard.baju');
            }

            if ($strict) {
                abort(403, 'Halaman ini hanya untuk kategori staff yang dibenarkan.');
            }

            return $next($request);
        }

        $staff = Pekerja::query()->find($request->session()->get('auth_id'));

        if (! $staff || ! $staff->status_aktif || ! in_array($staff->staff_type, $types, true)) {
            abort(403, 'Akses halaman ini tidak dibenarkan untuk kategori staff anda.');
        }

        $request->attributes->set('staff_type', $staff->staff_type);

        return $next($request);
    }
}
