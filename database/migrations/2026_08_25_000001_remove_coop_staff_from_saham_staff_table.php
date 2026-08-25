<?php

use App\Models\Pekerja;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pekerja') || ! Schema::hasTable('saham_staff')) {
            return;
        }

        $coopWorkerIds = DB::table('pekerja')
            ->where('staff_type', Pekerja::COOP_WORKER_STAFF_TYPE)
            ->pluck('id_pekerja');

        if ($coopWorkerIds->isEmpty()) {
            return;
        }

        DB::table('saham_staff')
            ->whereIn('id_pekerja', $coopWorkerIds)
            ->delete();
    }

    public function down(): void
    {
        //
    }
};
