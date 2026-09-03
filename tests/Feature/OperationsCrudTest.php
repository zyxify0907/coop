<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\Saham;
use App\Models\SahamStaff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OperationsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_and_delete_vendor(): void
    {
        $admin = $this->admin();
        $vendorId = DB::table('vendor')->insertGetId([
            'nama_vendor' => 'Vendor Lama',
            'no_akaun' => '111',
            'bank' => 'BSN',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->put(route('admin.vendors.update', $vendorId), [
                'nama_vendor' => 'Vendor Baru',
                'no_akaun' => '222',
                'bank' => 'CIMB',
            ])
            ->assertRedirect(route('admin.vendors.index'));

        $this->assertDatabaseHas('vendor', [
            'vendor_id' => $vendorId,
            'nama_vendor' => 'Vendor Baru',
            'no_akaun' => '222',
            'bank' => 'CIMB',
        ]);

        $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->delete(route('admin.vendors.destroy', $vendorId))
            ->assertRedirect(route('admin.vendors.index'));

        $this->assertDatabaseMissing('vendor', [
            'vendor_id' => $vendorId,
        ]);
    }

    public function test_admin_can_delete_saham_record(): void
    {
        $admin = $this->admin();
        $student = Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'status_aktif' => true,
        ]);
        $saham = Saham::query()->create([
            'id_ahli' => $student->id_ahli,
            'syer' => 10,
            'yuran' => 20,
        ]);

        $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->delete(route('admin.saham.destroy', $saham))
            ->assertRedirect(route('admin.saham.index'));

        $this->assertDatabaseMissing('saham', [
            'id_saham' => $saham->id_saham,
        ]);
    }

    public function test_admin_can_delete_student_member_record(): void
    {
        $admin = $this->admin();
        $student = Ahli::query()->create([
            'no_anggota' => 'PBT123',
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'status_aktif' => true,
        ]);
        Saham::query()->create([
            'id_ahli' => $student->id_ahli,
            'syer' => 10,
            'yuran' => 10,
        ]);
        $application = Permohonan::query()->create([
            'id_ahli' => $student->id_ahli,
            'jenis' => 'anggota',
            'nama_pemohon' => $student->nama,
            'no_matrik' => $student->no_matrik,
            'status' => 'diluluskan',
            'data_permohonan' => ['no_anggota' => 'PBT123'],
            'tarikh_permohonan' => now()->toDateString(),
            'tarikh_keputusan' => now()->toDateString(),
        ]);

        $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->delete(route('admin.anggota.destroy', $application))
            ->assertRedirect(route('admin.anggota.students'));

        $this->assertDatabaseHas('ahli', [
            'id_ahli' => $student->id_ahli,
            'no_anggota' => null,
        ]);
        $this->assertDatabaseMissing('saham', [
            'id_ahli' => $student->id_ahli,
        ]);
        $this->assertDatabaseMissing('permohonan', [
            'id_permohonan' => $application->id_permohonan,
        ]);
    }

    public function test_admin_can_delete_staff_member_record(): void
    {
        $admin = $this->admin();
        $staff = Pekerja::query()->create([
            'no_anggota' => 'PBT1001',
            'no_pekerja' => 'STAFF-001',
            'nama' => 'Staff One',
            'status_aktif' => true,
            'staff_type' => 'lecturer_member',
        ]);
        SahamStaff::query()->create([
            'id_pekerja' => $staff->id_pekerja,
            'syer' => 10,
            'yuran' => 10,
        ]);
        $application = Permohonan::query()->create([
            'jenis' => 'anggota',
            'nama_pemohon' => $staff->nama,
            'no_matrik' => $staff->no_pekerja,
            'status' => 'diluluskan',
            'data_permohonan' => [
                'pemohon_role' => 'staff',
                'no_pekerja' => $staff->no_pekerja,
                'no_anggota' => 'PBT1001',
            ],
            'tarikh_permohonan' => now()->toDateString(),
            'tarikh_keputusan' => now()->toDateString(),
        ]);

        $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->delete(route('admin.anggota.destroy', $application))
            ->assertRedirect(route('admin.anggota.staff'));

        $this->assertDatabaseHas('pekerja', [
            'id_pekerja' => $staff->id_pekerja,
            'no_anggota' => null,
        ]);
        $this->assertDatabaseMissing('saham_staff', [
            'id_pekerja' => $staff->id_pekerja,
        ]);
        $this->assertDatabaseMissing('permohonan', [
            'id_permohonan' => $application->id_permohonan,
        ]);
    }

    public function test_staff_can_delete_stock_and_order(): void
    {
        $staff = Pekerja::query()->create([
            'nama' => 'Pengurus Baju',
            'no_pekerja' => 'PBT-TEST01',
            'nric' => '810101010001',
            'password_hash' => Hash::make('staff12345'),
            'status_aktif' => true,
            'staff_type' => 'clothing_staff',
        ]);
        $itemId = DB::table('stok')->insertGetId([
            'nama_item' => 'Item Test',
            'quantity' => 10,
            'harga' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'item_id');
        $student = Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'status_aktif' => true,
        ]);
        $orderId = DB::table('tempahan')->insertGetId([
            'no_matrik' => $student->no_matrik,
            'item_id' => $itemId,
            'item' => 'Item Test',
            'quantity' => 1,
            'status' => 'baru',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'tempahan_id');

        $this
            ->withSession(['auth_role' => 'staff', 'auth_id' => $staff->id_pekerja, 'staff_type' => 'clothing_staff'])
            ->delete(route('staff.tempahan.destroy', $orderId))
            ->assertRedirect(route('clothing-staff.orders.index'));

        $this->assertDatabaseMissing('tempahan', [
            'tempahan_id' => $orderId,
        ]);

        $this
            ->withSession(['auth_role' => 'staff', 'auth_id' => $staff->id_pekerja, 'staff_type' => 'clothing_staff'])
            ->delete(route('staff.stok.destroy', $itemId))
            ->assertRedirect(route('staff.stok.index'));

        $this->assertDatabaseMissing('stok', [
            'item_id' => $itemId,
        ]);
    }

    private function admin(): AdminUser
    {
        return AdminUser::query()->create([
            'nama' => 'Admin One',
            'username' => 'adminone',
            'nric' => '800101010001',
            'password_hash' => Hash::make('aadmin12345'),
            'status_aktif' => true,
        ]);
    }
}
