<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saham_staff') && ! Schema::hasColumn('saham_staff', 'yuran')) {
            Schema::table('saham_staff', function (Blueprint $table): void {
                $table->decimal('yuran', 12, 2)->default(0)->after('id_pekerja');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('saham_staff') && Schema::hasColumn('saham_staff', 'yuran')) {
            Schema::table('saham_staff', function (Blueprint $table): void {
                $table->dropColumn('yuran');
            });
        }
    }
};
