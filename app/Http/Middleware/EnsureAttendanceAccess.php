<?php

namespace App\Http\Middleware;

use App\Models\Pekerja;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAttendanceAccess
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $role = $request->session()->get('auth_role');

        if ($area === 'admin') {
            abort_unless($role === 'admin', 403, 'Smart Attendance hanya untuk admin.');

            return $next($request);
        }

        abort_unless($role === 'staff', 403, 'Smart Attendance hanya untuk Pekerja Koperasi.');

        $staff = Pekerja::query()->find($request->session()->get('auth_id'));
        abort_unless($staff && $staff->status_aktif && $staff->staff_type === 'coop_staff', 403, 'Akses Smart Attendance tidak dibenarkan untuk kategori staff anda.');

        $request->attributes->set('attendance_staff', $staff);

        return $next($request);
    }
}
