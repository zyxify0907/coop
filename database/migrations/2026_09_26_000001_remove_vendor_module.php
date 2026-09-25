<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::unprepared('DROP VIEW IF EXISTS v_laporan_pembayaran_vendor');
            DB::unprepared('DROP PROCEDURE IF EXISTS kira_bayaran_vendor');
        }

        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('pembayaran_vendor');
        Schema::dropIfExists('item_serahan');
        Schema::dropIfExists('serahan');
        Schema::dropIfExists('vendor');
    }

    public function down(): void
    {
        // The removed vendor data cannot be reconstructed safely.
    }
};
