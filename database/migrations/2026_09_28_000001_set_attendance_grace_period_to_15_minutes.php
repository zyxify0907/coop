<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_settings')) {
            DB::table('attendance_settings')->update(['grace_period_minutes' => 15]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_settings')) {
            DB::table('attendance_settings')->update(['grace_period_minutes' => 10]);
        }
    }
};
