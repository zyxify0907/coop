<?php

namespace Tests\Feature;

use App\Models\Ahli;
use App\Models\Permohonan;
use App\Models\Saham;
use App\Models\ShareTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentBackfillMembershipApplicationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_approved_membership_application_for_students(): void
    {
        $student = Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'nric' => '010101010001',
            'semester' => 'Sem 1',
            'program' => 'JTMK',
            'kelas' => 'DIT1A',
            'status_aktif' => true,
        ]);

        $this->artisan('student:backfill-anggota')
            ->expectsOutput('Selesai. Permohonan dicipta: 1. Sedia ada/dilangkau: 0.')
            ->assertSuccessful();

        $student->refresh();

        $this->assertSame('PBT123', $student->no_anggota);
        $this->assertDatabaseHas('permohonan', [
            'id_ahli' => $student->id_ahli,
            'jenis' => 'anggota',
            'status' => 'diluluskan',
        ]);
        $this->assertDatabaseHas('saham', [
            'id_ahli' => $student->id_ahli,
            'syer' => 10,
            'yuran' => 10,
        ]);
        $this->assertDatabaseHas('share_transactions', [
            'member_type' => 'student',
            'member_id' => $student->id_ahli,
            'transaction_type' => 'OPENING_BALANCE',
            'direction' => 'CREDIT',
            'amount' => 10,
        ]);
    }

    public function test_command_skips_students_that_already_have_membership_application(): void
    {
        $student = Ahli::query()->create([
            'no_anggota' => 'PBT123',
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'status_aktif' => true,
        ]);

        Permohonan::query()->create([
            'id_ahli' => $student->id_ahli,
            'jenis' => 'anggota',
            'nama_pemohon' => $student->nama,
            'no_matrik' => $student->no_matrik,
            'status' => 'diluluskan',
            'data_permohonan' => ['no_anggota' => 'PBT123'],
            'tarikh_permohonan' => now()->toDateString(),
            'tarikh_keputusan' => now()->toDateString(),
        ]);

        $this->artisan('student:backfill-anggota')
            ->expectsOutput('Selesai. Permohonan dicipta: 0. Sedia ada/dilangkau: 1.')
            ->assertSuccessful();

        $this->assertSame(1, Permohonan::query()->where('jenis', 'anggota')->count());
        $this->assertSame(0, Saham::query()->count());
        $this->assertSame(0, ShareTransaction::query()->count());
    }
}
