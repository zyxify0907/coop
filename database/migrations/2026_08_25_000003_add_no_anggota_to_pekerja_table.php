<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pekerja') && ! Schema::hasColumn('pekerja', 'no_anggota')) {
            Schema::table('pekerja', function (Blueprint $table): void {
                $table->string('no_anggota', 20)->nullable()->unique()->after('id_pekerja');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pekerja') && Schema::hasColumn('pekerja', 'no_anggota')) {
            Schema::table('pekerja', function (Blueprint $table): void {
                $table->dropUnique(['no_anggota']);
                $table->dropColumn('no_anggota');
            });
        }
    }
};
