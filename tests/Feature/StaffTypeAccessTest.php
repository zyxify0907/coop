<?php

namespace Tests\Feature;

use App\Models\Pekerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffTypeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_member_is_redirected_to_member_dashboard_and_cannot_open_clothing_pages(): void
    {
        $staff = $this->staff('lecturer_member');

        $this->post(route('login.submit'), ['identifier' => $staff->email, 'password' => 'secret123'])
            ->assertRedirect(route('auth.dashboard'));

        $this->get(route('auth.dashboard'))->assertRedirect(route('lecturer-member.dashboard'));
        $this->get(route('lecturer-member.dashboard'))->assertOk()->assertSee('Dashboard Anggota Staf');
        $this->get(route('clothing-staff.orders.index'))->assertForbidden();
        $this->get(route('coop-staff.attendance.index'))->assertForbidden();
    }

    public function test_coop_staff_can_open_smart_attendance_but_not_clothing_access(): void
    {
        $staff = $this->staff('coop_staff');

        $this->withSession(['auth_role' => 'staff', 'auth_id' => $staff->id_pekerja])
            ->get(route('coop-staff.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Pekerja Koperasi')
            ->assertSee('Smart Attendance');

        $this->withSession(['auth_role' => 'staff', 'auth_id' => $staff->id_pekerja])
            ->get(route('coop-staff.attendance.index'))
            ->assertOk()
            ->assertSee('Kehadiran Hari Ini');

        $this->withSession(['auth_role' => 'staff', 'auth_id' => $staff->id_pekerja])
            ->get(route('clothing-staff.baju.index'))
            ->assertForbidden();
    }

    public function test_clothing_staff_can_open_own_dashboard_but_not_lecturer_dashboard(): void
    {
        $staff = $this->staff('clothing_staff');

        $this->withSession(['auth_role' => 'staff', 'auth_id' => $staff->id_pekerja])
            ->get(route('clothing-staff.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Pengurusan Baju');

        $this->withSession(['auth_role' => 'staff', 'auth_id' => $staff->id_pekerja])
            ->get(route('lecturer-member.dashboard'))
            ->assertForbidden();

        $this->withSession(['auth_role' => 'staff', 'auth_id' => $staff->id_pekerja])
            ->get(route('coop-staff.attendance.index'))
            ->assertForbidden();
    }

    private function staff(string $type): Pekerja
    {
        return Pekerja::query()->create([
            'no_pekerja' => match ($type) {
                'lecturer_member' => 'PBT-101',
                'clothing_staff' => 'PBT-103',
                default => 'PBT-102',
            },
            'nama' => 'Staff '.str_replace('_', ' ', $type),
            'nric' => match ($type) {
                'lecturer_member' => '810101010101',
                'clothing_staff' => '830101010101',
                default => '820101010101',
            },
            'email' => $type.'@example.com',
            'password_hash' => Hash::make('secret123'),
            'staff_type' => $type,
            'status_aktif' => true,
        ]);
    }
}
