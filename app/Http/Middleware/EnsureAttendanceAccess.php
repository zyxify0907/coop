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
            if ($role === 'admin') {
                return $next($request);
            }

            $manager = $role === 'staff'
                ? Pekerja::query()
                    ->whereKey($request->session()->get('auth_id'))
                    ->where('staff_type', Pekerja::COOP_MANAGER_STAFF_TYPE)
                    ->where('status_aktif', true)
                    ->first()
                : null;

            abort_unless($manager, 403, 'Smart Attendance hanya untuk Admin atau Staff Pengurus Pekerja Koperasi.');

            return $next($request);
        }

        abort_unless($role === 'staff', 403, 'Smart Attendance hanya untuk Pekerja Koperasi.');

        $staff = Pekerja::query()->find($request->session()->get('auth_id'));
        abort_unless($staff && $staff->status_aktif && $staff->staff_type === 'coop_staff', 403, 'Akses Smart Attendance tidak dibenarkan untuk kategori staff anda.');

        $request->attributes->set('attendance_staff', $staff);

        return $next($request);
    }
}
