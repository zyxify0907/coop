<?php

namespace Tests\Feature;

use App\Models\Ahli;
use App\Models\AdminUser;
use App\Models\DocumentUpload;
use App\Models\Permohonan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MembershipApplicationDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_membership_application_requires_both_documents(): void
    {
        $student = $this->student();

        $this
            ->withSession(['auth_role' => 'ahli', 'auth_id' => $student->id_ahli])
            ->post(route('student.permohonan.store', 'anggota'), $this->applicationData())
            ->assertSessionHasErrors(['salinan_ic', 'slip_bayaran']);

        $this->assertDatabaseCount('permohonan', 0);
    }

    public function test_membership_application_stores_both_uploaded_documents(): void
    {
        Storage::fake('local');
        $student = $this->student();
        $data = $this->applicationData();
        $data['salinan_ic'] = UploadedFile::fake()->create('salinan-ic.pdf', 100, 'application/pdf');
        $data['slip_bayaran'] = UploadedFile::fake()->create('slip-bayaran.pdf', 100, 'application/pdf');

        $this
            ->withSession(['auth_role' => 'ahli', 'auth_id' => $student->id_ahli])
            ->post(route('student.permohonan.store', 'anggota'), $data)
            ->assertRedirect(route('student.permohonan.index', ['jenis' => 'anggota']));

        $application = Permohonan::query()->sole();

        $this->assertSame('anggota', $application->jenis);
        $this->assertSame(2, DocumentUpload::query()->count());
        $this->assertDatabaseHas('document_uploads', [
            'documentable_id' => $application->id_permohonan,
            'category' => 'salinan_ic',
        ]);
        $this->assertDatabaseHas('document_uploads', [
            'documentable_id' => $application->id_permohonan,
            'category' => 'slip_bayaran',
        ]);
    }

    public function test_share_application_requires_and_stores_payment_slip(): void
    {
        Storage::fake('local');
        $student = $this->student();

        $this
            ->withSession(['auth_role' => 'ahli', 'auth_id' => $student->id_ahli])
            ->post(route('student.permohonan.store', 'saham'), $this->shareApplicationData())
            ->assertSessionHasErrors(['slip_bayaran']);

        $this->assertDatabaseCount('permohonan', 0);

        $data = $this->shareApplicationData();
        $data['slip_bayaran'] = UploadedFile::fake()->create('slip-tambah-saham.pdf', 100, 'application/pdf');

        $this
            ->withSession(['auth_role' => 'ahli', 'auth_id' => $student->id_ahli])
            ->post(route('student.permohonan.store', 'saham'), $data)
            ->assertRedirect(route('student.permohonan.index', ['jenis' => 'saham']));

        $application = Permohonan::query()->sole();

        $this->assertDatabaseHas('document_uploads', [
            'documentable_id' => $application->id_permohonan,
            'category' => 'slip_bayaran',
            'application_purpose' => 'Permohonan Penambahan Saham',
        ]);
    }

    public function test_approving_membership_application_generates_member_number(): void
    {
        $student = $this->student();
        $admin = AdminUser::query()->create([
            'nama' => 'Admin Koperasi',
            'username' => 'admin-koperasi',
            'password_hash' => 'password',
            'status_aktif' => true,
        ]);
        $application = Permohonan::query()->create([
            'id_ahli' => $student->id_ahli,
            'jenis' => 'anggota',
            'nama_pemohon' => $student->nama,
            'no_matrik' => $student->no_matrik,
            'status' => 'baru',
            'data_permohonan' => [
                'yuran_anggota' => 10,
                'modal_saham' => 10,
            ],
            'tarikh_permohonan' => now()->toDateString(),
        ]);

        $this
            ->withSession(['auth_role' => 'admin', 'auth_id' => $admin->id_admin])
            ->put(route('admin.permohonan.update', $application), [
                'decision_action' => '1',
                'status' => 'diluluskan',
            ])
            ->assertRedirect(route('admin.permohonan.show', $application));

        $this->assertSame('PBT123', $student->fresh()->no_anggota);
    }

    public function test_withdrawal_application_requires_and_stores_its_documents(): void
    {
        Storage::fake('local');
        $student = $this->student();
        $data = $this->withdrawalApplicationData();
        $data['salinan_ic'] = UploadedFile::fake()->create('salinan-ic.pdf', 100, 'application/pdf');

        $this
            ->withSession(['auth_role' => 'ahli', 'auth_id' => $student->id_ahli])
            ->post(route('student.permohonan.store', 'berhenti'), $data)
            ->assertSessionHasErrors(['surat_sokongan']);

        $this->assertDatabaseCount('permohonan', 0);

        $data['surat_sokongan'] = UploadedFile::fake()->create('surat-pindah.pdf', 100, 'application/pdf');

        $this
            ->withSession(['auth_role' => 'ahli', 'auth_id' => $student->id_ahli])
            ->post(route('student.permohonan.store', 'berhenti'), $data)
            ->assertRedirect(route('student.permohonan.index', ['jenis' => 'berhenti']));

        $application = Permohonan::query()->sole();

        $this->assertSame(2, DocumentUpload::query()->count());
        $this->assertDatabaseHas('document_uploads', [
            'documentable_id' => $application->id_permohonan,
            'category' => 'salinan_ic',
        ]);
        $this->assertDatabaseHas('document_uploads', [
            'documentable_id' => $application->id_permohonan,
            'category' => 'surat_pindah_berhenti_persaraan',
        ]);
    }

    private function student(): Ahli
    {
        return Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'nric' => '010101010001',
            'semester' => 'Sem 1',
            'status_aktif' => true,
        ]);
    }

    private function applicationData(): array
    {
        return [
            'email' => 'ali@example.com',
            'no_tel' => '0123456789',
            'tarikh_lahir' => '2001-01-01',
            'jantina' => 'Lelaki',
            'pekerjaan_pelajar' => 'Pelajar',
            'bangsa' => 'Melayu',
            'agama' => 'Islam',
            'taraf_perkahwinan' => 'Bujang',
            'alamat' => 'Jalan Contoh',
            'program_pengajian' => 'JTMK',
            'kelas' => 'DIT',
            'yuran_anggota' => '10.00',
            'modal_saham' => '10.00',
            'setuju_saham_tidak_dituntut' => '1',
            'akuan_pemohon' => '1',
            'penama_nama' => 'Siti Ahmad',
            'penama_nric' => '020202020002',
            'penama_hubungan' => 'Ibu',
            'penama_no_tel' => '0198765432',
            'penama_alamat' => 'Jalan Penama',
            'penama_poskod' => '22200',
            'nama_waris' => 'Siti Ahmad',
            'telefon_waris' => '0198765432',
            'hubungan_waris' => 'Ibu',
            'saksi_nama' => 'Abu Ahmad',
            'saksi_nric' => '030303030003',
            'saksi_tarikh' => '2026-08-17',
        ];
    }

    private function shareApplicationData(): array
    {
        return [
            'no_kp' => '010101010001',
            'amaun_tambahan' => '20.00',
            'tarikh_pengakuan' => '2026-08-17',
            'akuan_saham' => '1',
        ];
    }

    private function withdrawalApplicationData(): array
    {
        return [
            'no_kp' => '010101010001',
            'jenis_permohonan' => ['Berpindah'],
            'jumlah_dipohon' => '0.00',
            'tarikh_pengakuan' => '2026-08-17',
            'akuan_pengeluaran' => '1',
            'kaedah_terima_bayaran' => 'Bayaran Atas Talian',
        ];
    }
}
