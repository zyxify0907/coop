<?php

namespace App\Console\Commands;

use App\Models\Ahli;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\Saham;
use App\Models\ShareTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillStudentMembershipApplications extends Command
{
    protected $signature = 'student:backfill-anggota {--include-inactive : Termasuk pelajar tidak aktif}';

    protected $description = 'Cipta permohonan anggota yang diluluskan untuk pelajar yang belum ada permohonan anggota';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use (&$created, &$skipped): void {
            $studentsQuery = Ahli::query()->orderBy('id_ahli');

            if (! $this->option('include-inactive')) {
                $studentsQuery->where('status_aktif', true);
            }

            $studentsQuery->get()->each(function (Ahli $student) use (&$created, &$skipped): void {
                $hasApplication = Permohonan::query()
                    ->where('jenis', 'anggota')
                    ->where('id_ahli', $student->id_ahli)
                    ->exists();

                if ($hasApplication) {
                    $skipped++;

                    return;
                }

                $memberNumber = filled($student->no_anggota) ? $student->no_anggota : $this->nextMemberNumber();

                if (Schema::hasColumn('ahli', 'no_anggota') && blank($student->no_anggota)) {
                    $student->update(['no_anggota' => $memberNumber]);
                }

                $application = Permohonan::query()->create([
                    'id_ahli' => $student->id_ahli,
                    'jenis' => 'anggota',
                    'nama_pemohon' => $student->nama,
                    'no_matrik' => $student->no_matrik,
                    'email' => $student->email,
                    'no_tel' => $student->no_tel,
                    'status' => 'diluluskan',
                    'data_permohonan' => $this->studentApplicationData($student, $memberNumber),
                    'catatan_admin' => 'Permohonan anggota pelajar dijana automatik oleh sistem.',
                    'tarikh_permohonan' => optional($student->tarikh_daftar)->toDateString() ?: now()->toDateString(),
                    'tarikh_keputusan' => now()->toDateString(),
                ]);

                $share = Saham::query()->firstOrNew(['id_ahli' => $student->id_ahli]);
                if (Schema::hasColumn('saham', 'yuran')) {
                    $share->yuran = max((float) ($share->yuran ?? 0), 10);
                }
                $share->syer = max((float) ($share->syer ?? 0), 10);
                $share->tarikh_kemaskini = $share->tarikh_kemaskini ?: $application->tarikh_keputusan;
                $share->save();

                $this->recordOpeningBalanceTransaction($student, $application, $share);

                $created++;
            });
        });

        $this->info("Selesai. Permohonan dicipta: {$created}. Sedia ada/dilangkau: {$skipped}.");

        return self::SUCCESS;
    }

    private function studentApplicationData(Ahli $student, string $memberNumber): array
    {
        return [
            'pemohon_role' => 'ahli',
            'no_kad_pengenalan' => $student->nric,
            'no_anggota' => $memberNumber,
            'program_pengajian' => $student->program,
            'kelas' => $student->kelas,
            'semester' => $student->semester,
            'yuran_anggota' => 10.0,
            'modal_saham' => 10.0,
            'setuju_saham_tidak_dituntut' => true,
            'akuan_pemohon' => true,
            'dijana_automatik' => true,
        ];
    }

    private function nextMemberNumber(): string
    {
        $memberNumbers = Ahli::query()
            ->whereNotNull('no_anggota')
            ->lockForUpdate()
            ->pluck('no_anggota');

        if (Schema::hasColumn('pekerja', 'no_anggota')) {
            $memberNumbers = $memberNumbers->merge(
                Pekerja::query()
                    ->whereNotNull('no_anggota')
                    ->lockForUpdate()
                    ->pluck('no_anggota')
            );
        }

        $memberNumbers = $memberNumbers->merge(
            Permohonan::query()
                ->where('jenis', 'anggota')
                ->where('status', 'diluluskan')
                ->get()
                ->pluck('data_permohonan.no_anggota')
                ->filter()
        );

        $highestNumber = $memberNumbers
            ->map(fn (string $number): int => preg_match('/^PBT(\d+)$/i', trim($number), $matches) ? (int) $matches[1] : 0)
            ->max() ?? 0;

        return 'PBT'.max(123, $highestNumber + 1);
    }

    private function recordOpeningBalanceTransaction(Ahli $student, Permohonan $application, Saham $share): void
    {
        if (! Schema::hasTable('share_transactions')) {
            return;
        }

        $exists = ShareTransaction::query()
            ->where('reference_type', Permohonan::class)
            ->where('reference_id', $application->getKey())
            ->where('transaction_type', 'OPENING_BALANCE')
            ->where('direction', 'CREDIT')
            ->exists();

        if ($exists) {
            return;
        }

        ShareTransaction::query()->create([
            'member_type' => 'student',
            'member_id' => $student->id_ahli,
            'transaction_type' => 'OPENING_BALANCE',
            'direction' => 'CREDIT',
            'amount' => (float) ($share->syer ?? 0),
            'balance_after' => (float) ($share->syer ?? 0) + (float) ($share->tambahan_saham ?? 0),
            'reference_type' => Permohonan::class,
            'reference_id' => $application->getKey(),
            'processed_by_role' => 'system',
            'processed_by_id' => null,
            'notes' => 'Saham permulaan dijana automatik semasa backfill anggota pelajar.',
            'transacted_at' => $application->tarikh_keputusan ?? now()->toDateString(),
        ]);
    }
}
