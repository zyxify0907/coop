<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\Pekerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_login_with_no_matrik_and_default_password(): void
    {
        Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'nric' => '010101010001',
            'password_hash' => Hash::make('student12345'),
            'semester' => 'Sem 1',
            'status_aktif' => true,
        ]);

        $response = $this->post(route('login.submit'), [
            'role' => 'ahli',
            'identifier' => 'A001',
            'password' => 'student12345',
        ]);

        $response->assertRedirect(route('auth.dashboard'));
        $this->assertSame('ahli', session('auth_role'));
    }

    public function test_staff_can_login_with_nric_and_default_password(): void
    {
        Pekerja::query()->create([
            'no_pekerja' => 'STAFF-001',
            'nama' => 'Staff One',
            'nric' => '820101010001',
            'password_hash' => Hash::make('staff12345'),
            'status_aktif' => true,
        ]);

        $response = $this->post(route('login.submit'), [
            'role' => 'staff',
            'identifier' => '820101010001',
            'password' => 'staff12345',
        ]);

        $response->assertRedirect(route('auth.dashboard'));
        $this->assertSame('staff', session('auth_role'));
    }

    public function test_student_can_login_with_nric_and_default_password(): void
    {
        Ahli::query()->create([
            'no_matrik' => 'A002',
            'nama' => 'Siti Ahmad',
            'nric' => '020202020002',
            'password_hash' => Hash::make('student12345'),
            'semester' => 'Sem 1',
            'status_aktif' => true,
        ]);

        $response = $this->post(route('login.submit'), [
            'identifier' => '020202020002',
            'password' => 'student12345',
        ]);

        $response->assertRedirect(route('auth.dashboard'));
        $this->assertSame('ahli', session('auth_role'));
    }

    public function test_student_can_login_with_legacy_2a_bcrypt_hash(): void
    {
        $legacyHash = Hash::make('010101010001');
        $legacyHash = '$2a$'.substr($legacyHash, 4);

        $student = Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'nric' => '010101010001',
            'password_hash' => $legacyHash,
            'semester' => 'Sem 1',
            'status_aktif' => true,
        ]);

        $response = $this->post(route('login.submit'), [
            'role' => 'ahli',
            'identifier' => 'A001',
            'password' => '010101010001',
        ]);

        $response->assertRedirect(route('auth.dashboard'));

        $student->refresh();

        $this->assertStringStartsWith('$2y$', $student->password_hash);
        $this->assertTrue(Hash::check('010101010001', $student->password_hash));
    }

    public function test_admin_can_update_student_password_and_profile(): void
    {
        $admin = AdminUser::query()->create([
            'nama' => 'Admin One',
            'username' => 'adminone',
            'nric' => '800101010001',
            'password_hash' => Hash::make('admin12345'),
            'status_aktif' => true,
        ]);

        $student = Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'nric' => '010101010001',
            'password_hash' => Hash::make('student12345'),
            'semester' => 'Sem 1',
            'status_aktif' => true,
        ]);

        $response = $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->put(route('admin.users.update', ['type' => 'student', 'id' => $student->id_ahli]), [
                'nama' => 'Ali Updated',
                'nric' => '010101010001',
                'semester' => 'Sem 2',
                'program' => 'JTMK',
                'password' => 'newpass123',
            ]);

        $response->assertRedirect(route('admin.users.students'));

        $student->refresh();

        $this->assertSame('Ali Updated', $student->nama);
        $this->assertSame('Sem 2', $student->semester);
        $this->assertTrue(Hash::check('newpass123', $student->password_hash));
    }

    public function test_admin_can_add_staff_with_nric_default_password(): void
    {
        $admin = AdminUser::query()->create([
            'nama' => 'Admin One',
            'username' => 'adminone',
            'nric' => '800101010001',
            'password_hash' => Hash::make('admin12345'),
            'status_aktif' => true,
        ]);

        $response = $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->post(route('admin.users.staff.store'), [
                'no_pekerja' => 'PBT-007',
                'nama' => 'Staff Seven',
                'nric' => '820101010007',
            'jawatan' => 'Pembantu Kedai',
            'email' => 'staff7@example.com',
            'staff_type' => 'coop_staff',
            ]);

        $response->assertRedirect(route('admin.users.staff'));

        $staff = Pekerja::query()->where('no_pekerja', 'PBT-007')->firstOrFail();

        $this->assertSame('Staff Seven', $staff->nama);
        $this->assertSame('coop_staff', $staff->staff_type);
        $this->assertTrue(Hash::check('staff12345', $staff->password_hash));
    }

    public function test_admin_can_add_student_with_nric_default_password(): void
    {
        $admin = AdminUser::query()->create([
            'nama' => 'Admin One',
            'username' => 'adminone',
            'nric' => '800101010001',
            'password_hash' => Hash::make('admin12345'),
            'status_aktif' => true,
        ]);

        $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->get(route('admin.users.create', ['type' => 'student']))
            ->assertOk()
            ->assertSee('Tambah Student');

        $response = $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->post(route('admin.users.students.store'), [
                'no_matrik' => 'A002',
                'nama' => 'Student Two',
                'nric' => '010101010002',
                'semester' => 'Sem 2',
                'program' => 'JTMK',
            ]);

        $response->assertRedirect(route('admin.users.students'));

        $student = Ahli::query()->where('no_matrik', 'A002')->firstOrFail();

        $this->assertSame('Student Two', $student->nama);
        $this->assertTrue(Hash::check('student12345', $student->password_hash));
    }

    public function test_admin_student_create_validates_duplicate_normalized_nric(): void
    {
        $admin = AdminUser::query()->create([
            'nama' => 'Admin One',
            'username' => 'adminone',
            'nric' => '800101010001',
            'password_hash' => Hash::make('admin12345'),
            'status_aktif' => true,
        ]);

        Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Existing Student',
            'nric' => '123456789',
            'password_hash' => Hash::make('student12345'),
            'semester' => 'Sem 1',
            'status_aktif' => true,
        ]);

        $response = $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->from(route('admin.users.create', ['type' => 'student']))
            ->post(route('admin.users.students.store'), [
                'no_matrik' => '34DIT24F3001',
                'nama' => 'KHOMANI',
                'nric' => '123-45-6789',
                'kelas' => 'DIT1A',
                'no_tel' => '011-3456789',
                'tarikh_daftar' => '2026-09-24',
            ]);

        $response
            ->assertRedirect(route('admin.users.create', ['type' => 'student']))
            ->assertSessionHasErrors('nric');

        $this->assertDatabaseMissing('ahli', [
            'no_matrik' => '34DIT24F3001',
        ]);
    }

    public function test_admin_can_delete_staff(): void
    {
        $admin = AdminUser::query()->create([
            'nama' => 'Admin One',
            'username' => 'adminone',
            'nric' => '800101010001',
            'password_hash' => Hash::make('admin12345'),
            'status_aktif' => true,
        ]);

        $staff = Pekerja::query()->create([
            'no_pekerja' => 'PBT-008',
            'nama' => 'Staff Eight',
            'nric' => '820101010008',
            'password_hash' => Hash::make('staff12345'),
            'status_aktif' => true,
        ]);

        $response = $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->delete(route('admin.users.destroy', ['type' => 'staff', 'id' => $staff->id_pekerja]));

        $response->assertRedirect(route('admin.users.staff'));

        $this->assertDatabaseMissing('pekerja', [
            'id_pekerja' => $staff->id_pekerja,
        ]);
    }
}
