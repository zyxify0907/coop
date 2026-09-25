<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::unprepared('DROP VIEW IF EXISTS v_laporan_jualan_harian');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_update_stok_sales');
        }

        Schema::dropIfExists('jualan');
    }

    public function down(): void
    {
        // The removed sales data cannot be reconstructed safely.
    }
};
