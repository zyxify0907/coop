<?php

namespace App\Console\Commands;

use App\Models\Permohonan;
use App\Models\Saham;
use App\Models\SahamStaff;
use App\Models\ShareTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class BackfillMembershipShareTransactions extends Command
{
    protected $signature = 'anggota:backfill-transactions';

    protected $description = 'Cipta transaksi saham permulaan untuk permohonan anggota yang telah diluluskan tetapi belum ada transaksi';

    public function handle(): int
    {
        if (! Schema::hasTable('share_transactions')) {
            $this->warn('Table share_transactions tidak ditemui.');

            return self::INVALID;
        }

        $created = 0;

        Permohonan::query()
            ->where('jenis', 'anggota')
            ->where('status', 'diluluskan')
            ->orderBy('id_permohonan')
            ->get()
            ->each(function (Permohonan $application) use (&$created): void {
                $exists = ShareTransaction::query()
                    ->where('reference_type', Permohonan::class)
                    ->where('reference_id', $application->getKey())
                    ->where('transaction_type', 'OPENING_BALANCE')
                    ->where('direction', 'CREDIT')
                    ->exists();

                if ($exists) {
                    return;
                }

                $isStaff = ($application->data_permohonan['pemohon_role'] ?? null) === 'staff';

                if ($isStaff) {
                    $staff = \App\Models\Pekerja::query()
                        ->where('no_pekerja', $application->data_permohonan['no_pekerja'] ?? $application->no_matrik)
                        ->first();

                    if (! $staff) {
                        return;
                    }

                    $share = SahamStaff::query()->where('id_pekerja', $staff->id_pekerja)->first();

                    if (! $share || (float) ($share->syer ?? 0) <= 0) {
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
                        'notes' => 'Transaksi saham permulaan dibackfill untuk anggota staff sedia ada.',
                        'transacted_at' => $application->tarikh_keputusan ?? $application->tarikh_permohonan ?? now()->toDateString(),
                    ]);

                    $created++;

                    return;
                }

                if (! $application->id_ahli) {
                    return;
                }

                $share = Saham::query()->where('id_ahli', $application->id_ahli)->first();

                if (! $share || (float) ($share->syer ?? 0) <= 0) {
                    return;
                }

                ShareTransaction::query()->create([
                    'member_type' => 'student',
                    'member_id' => $application->id_ahli,
                    'transaction_type' => 'OPENING_BALANCE',
                    'direction' => 'CREDIT',
                    'amount' => (float) ($share->syer ?? 0),
                    'balance_after' => (float) ($share->syer ?? 0) + (float) ($share->tambahan_saham ?? 0),
                    'reference_type' => Permohonan::class,
                    'reference_id' => $application->getKey(),
                    'processed_by_role' => 'system',
                    'processed_by_id' => null,
                    'notes' => 'Transaksi saham permulaan dibackfill untuk anggota pelajar sedia ada.',
                    'transacted_at' => $application->tarikh_keputusan ?? $application->tarikh_permohonan ?? now()->toDateString(),
                ]);

                $created++;
            });

        $this->info("Selesai. Transaksi saham permulaan dicipta: {$created}.");

        return self::SUCCESS;
    }
}
