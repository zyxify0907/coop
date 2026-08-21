<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\AttendanceSetting;
use App\Models\Pekerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SmartAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_coop_staff_can_check_in_once_with_verified_location(): void
    {
        $worker = $this->worker('coop_staff');
        AttendanceSetting::query()->create([
            'work_start_time' => '08:00:00', 'work_end_time' => '17:00:00',
            'checkout_cutoff_time' => '20:00:00', 'working_days' => [1, 2, 3, 4, 5, 6, 7],
            'latitude' => 5.75000000, 'longitude' => 102.50000000,
            'allowed_radius_meter' => 100, 'location_name' => 'Koperasi Ujian', 'status' => true,
        ]);

        $response = $this->withSession(['auth_role' => 'staff', 'auth_id' => $worker->id_pekerja])
            ->post(route('coop-staff.attendance.check-in'), ['latitude' => 5.75000000, 'longitude' => 102.50000000]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_records', ['staff_id' => $worker->id_pekerja, 'check_in_gps_verified' => true]);
        $this->assertSame(1, \App\Models\AttendanceRecord::query()->where('staff_id', $worker->id_pekerja)->whereDate('attendance_date', today())->count());

        $this->withSession(['auth_role' => 'staff', 'auth_id' => $worker->id_pekerja])
            ->post(route('coop-staff.attendance.check-in'), ['latitude' => 5.75000000, 'longitude' => 102.50000000])
            ->assertSessionHasErrors('attendance');
    }

    public function test_admin_can_open_attendance_dashboard(): void
    {
        $admin = AdminUser::query()->create([
            'nama' => 'Admin Attendance', 'username' => 'attendance-admin', 'nric' => '800101010101',
            'password_hash' => Hash::make('secret123'), 'status_aktif' => true,
        ]);

        $this->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->get(route('admin.attendance.index'))
            ->assertOk()
            ->assertSee('Smart Attendance');
    }

    private function worker(string $staffType): Pekerja
    {
        return Pekerja::query()->create([
            'no_pekerja' => 'PBT-201', 'nama' => 'Pekerja Attendance', 'nric' => '820101010201',
            'email' => 'worker@example.com', 'password_hash' => Hash::make('secret123'),
            'staff_type' => $staffType, 'status_aktif' => true,
        ]);
    }
}
