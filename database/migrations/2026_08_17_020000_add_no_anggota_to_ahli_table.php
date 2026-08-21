<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ahli', 'no_anggota')) {
            Schema::table('ahli', function (Blueprint $table): void {
                $table->string('no_anggota', 20)->nullable()->unique()->after('id_ahli');
            });
        }

        $approvedMemberIds = DB::table('permohonan')
            ->where('jenis', 'anggota')
            ->where('status', 'diluluskan')
            ->whereNotNull('id_ahli')
            ->orderBy('tarikh_keputusan')
            ->orderBy('id_permohonan')
            ->pluck('id_ahli')
            ->unique()
            ->values();

        $nextNumber = 123;

        foreach ($approvedMemberIds as $memberId) {
            $member = DB::table('ahli')->where('id_ahli', $memberId)->first();

            if (! $member || $member->no_anggota) {
                continue;
            }

            DB::table('ahli')
                ->where('id_ahli', $memberId)
                ->update(['no_anggota' => 'PBT'.$nextNumber]);

            $nextNumber++;
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ahli', 'no_anggota')) {
            Schema::table('ahli', function (Blueprint $table): void {
                $table->dropUnique(['no_anggota']);
                $table->dropColumn('no_anggota');
            });
        }
    }
};
