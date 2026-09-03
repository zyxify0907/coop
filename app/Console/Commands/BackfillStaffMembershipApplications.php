<?php

namespace App\Console\Commands;

use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\SahamStaff;
use App\Models\ShareTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillStaffMembershipApplications extends Command
{
    protected $signature = 'staff:backfill-anggota {--all-types : Termasuk coop_staff juga}';

    protected $description = 'Cipta permohonan anggota yang diluluskan untuk staff yang belum ada permohonan anggota';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use (&$created, &$skipped): void {
            $staffQuery = Pekerja::query()
                ->where('status_aktif', true)
                ->orderBy('id_pekerja');

            if (! $this->option('all-types')) {
                $staffQuery->eligibleForShares();
            }

            $staffQuery->get()->each(function (Pekerja $staff) use (&$created, &$skipped): void {
                $hasApplication = Permohonan::query()
                    ->where('jenis', 'anggota')
                    ->where('data_permohonan->pemohon_role', 'staff')
                    ->where('no_matrik', $staff->no_pekerja)
                    ->exists();

                if ($hasApplication) {
                    $skipped++;

                    return;
                }

                $memberNumber = filled($staff->no_anggota) ? $staff->no_anggota : $this->nextStaffMemberNumber();

                if (Schema::hasColumn('pekerja', 'no_anggota') && blank($staff->no_anggota)) {
                    $staff->update(['no_anggota' => $memberNumber]);
                }

                $application = Permohonan::query()->create([
                    'id_ahli' => null,
                    'jenis' => 'anggota',
                    'nama_pemohon' => $staff->nama,
                    'no_matrik' => $staff->no_pekerja,
                    'email' => $staff->email,
                    'no_tel' => $staff->no_tel,
                    'status' => 'diluluskan',
                    'data_permohonan' => $this->staffApplicationData($staff, $memberNumber),
                    'catatan_admin' => 'Permohonan anggota staff dijana automatik oleh sistem.',
                    'tarikh_permohonan' => optional($staff->tarikh_mula)->toDateString() ?: now()->toDateString(),
                    'tarikh_keputusan' => now()->toDateString(),
                ]);

                if ($staff->isEligibleForShares()) {
                    $share = SahamStaff::query()->firstOrNew(['id_pekerja' => $staff->id_pekerja]);
                    if (Schema::hasColumn('saham_staff', 'yuran')) {
                        $share->yuran = max((float) ($share->yuran ?? 0), 10);
                    }
                    $share->syer = max((float) ($share->syer ?? 0), 10);
                    $share->tarikh_kemaskini = $share->tarikh_kemaskini ?: $application->tarikh_keputusan;
                    $share->save();

                    $this->recordOpeningBalanceTransaction($staff, $application, $share);
                }

                $created++;
            });
        });

        $this->info("Selesai. Permohonan dicipta: {$created}. Sedia ada/dilangkau: {$skipped}.");

        return self::SUCCESS;
    }

    private function staffApplicationData(Pekerja $staff, string $memberNumber): array
    {
        return [
            'pemohon_role' => 'staff',
            'no_kad_pengenalan' => $staff->nric,
            'no_pekerja' => $staff->no_pekerja,
            'staff_type' => $staff->staff_type,
            'jenis_staff' => $staff->staff_type_label,
            'jawatan' => $staff->jawatan,
            'tarikh_mula_kerja' => optional($staff->tarikh_mula)->toDateString(),
            'no_anggota' => $memberNumber,
            'yuran_anggota' => 10.0,
            'modal_saham' => 10.0,
            'setuju_saham_tidak_dituntut' => true,
            'akuan_pemohon' => true,
            'dijana_automatik' => true,
        ];
    }

    private function nextStaffMemberNumber(): string
    {
        $memberNumbers = collect();

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
                ->where('data_permohonan->pemohon_role', 'staff')
                ->get()
                ->pluck('data_permohonan.no_anggota')
                ->filter()
        );

        $highestNumber = $memberNumbers
            ->map(fn (string $number): int => preg_match('/^PBT(\d+)$/i', trim($number), $matches) ? (int) $matches[1] : 0)
            ->filter(fn (int $number): bool => $number >= 1001)
            ->max() ?? 1000;

        return 'PBT'.($highestNumber + 1);
    }

    private function recordOpeningBalanceTransaction(Pekerja $staff, Permohonan $application, SahamStaff $share): void
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
            'member_type' => 'staff',
            'member_id' => $staff->id_pekerja,
            'transaction_type' => 'OPENING_BALANCE',
            'direction' => 'CREDIT',
            'amount' => (float) ($share->syer ?? 0),
            'balance_after' => (float) ($share->syer ?? 0) + (float) ($share->tambahan_saham ?? 0),
            'reference_type' => Permohonan::class,
            'reference_id' => $application->getKey(),
            'processed_by_role' => 'system',
            'processed_by_id' => null,
            'notes' => 'Saham permulaan dijana automatik semasa backfill anggota staff.',
            'transacted_at' => $application->tarikh_keputusan ?? now()->toDateString(),
        ]);
    }
}
