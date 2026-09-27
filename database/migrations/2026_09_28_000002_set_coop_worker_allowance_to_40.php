<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pekerja')) {
            DB::table('pekerja')
                ->where('staff_type', 'coop_staff')
                ->update(['kadar_elaun' => 40]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pekerja')) {
            DB::table('pekerja')
                ->where('staff_type', 'coop_staff')
                ->update(['kadar_elaun' => 0]);
        }
    }
};
