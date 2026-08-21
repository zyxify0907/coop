<?php

namespace Tests\Feature;

use App\Models\Ahli;
use App\Models\DocumentUpload;
use App\Models\Permohonan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApplicationStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_own_application_status_and_uploaded_slip(): void
    {
        $student = Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'nric' => '010101010001',
            'status_aktif' => true,
        ]);
        $application = Permohonan::query()->create([
            'id_ahli' => $student->id_ahli,
            'jenis' => 'saham',
            'nama_pemohon' => $student->nama,
            'no_matrik' => $student->no_matrik,
            'status' => 'baru',
            'data_permohonan' => [],
            'tarikh_permohonan' => now()->toDateString(),
        ]);
        DocumentUpload::query()->create([
            'owner_role' => 'ahli',
            'owner_id' => $student->id_ahli,
            'uploaded_by_role' => 'ahli',
            'uploaded_by_id' => $student->id_ahli,
            'documentable_type' => Permohonan::class,
            'documentable_id' => $application->id_permohonan,
            'category' => 'slip_bayaran',
            'application_purpose' => 'Permohonan Penambahan Saham',
            'original_name' => 'slip-bayaran.pdf',
            'stored_path' => 'koperasi-documents/slip-bayaran.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
        ]);
        DocumentUpload::query()->create([
            'owner_role' => 'ahli',
            'owner_id' => $student->id_ahli,
            'uploaded_by_role' => 'ahli',
            'uploaded_by_id' => $student->id_ahli,
            'category' => 'penyata_bank',
            'original_name' => 'penyata-bank.jpg',
            'stored_path' => 'koperasi-documents/penyata-bank.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);

        $this
            ->withSession(['auth_role' => 'ahli', 'auth_id' => $student->id_ahli])
            ->get(route('student.permohonan.status'))
            ->assertOk()
            ->assertSee('Status Permohonan')
            ->assertSee('Slip / Bukti Bayaran')
            ->assertSee('slip-bayaran.pdf')
            ->assertDontSee('penyata-bank.jpg');
    }
}
