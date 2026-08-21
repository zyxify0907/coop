<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pekerja', 'staff_type')) {
            Schema::table('pekerja', function (Blueprint $table): void {
                $table->string('staff_type', 30)->default('coop_staff')->after('jawatan');
                $table->index('staff_type');
            });
        }

        DB::table('pekerja')->where('staff_type', 'member')->update(['staff_type' => 'lecturer_member']);
        DB::table('pekerja')->where('staff_type', 'worker')->update(['staff_type' => 'coop_staff']);
        DB::table('pekerja')->where('staff_type', 'management')->update(['staff_type' => 'clothing_staff']);
        DB::table('pekerja')->whereNull('staff_type')->update(['staff_type' => 'coop_staff']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('pekerja', 'staff_type')) {
            Schema::table('pekerja', function (Blueprint $table): void {
                $table->dropIndex(['staff_type']);
                $table->dropColumn('staff_type');
            });
        }
    }
};
