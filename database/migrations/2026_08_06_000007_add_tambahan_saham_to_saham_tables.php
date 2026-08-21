<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('saham', 'tambahan_saham')) {
            Schema::table('saham', function (Blueprint $table): void {
                $table->decimal('tambahan_saham', 10, 2)->default(0)->after('syer');
            });
        }

        if (Schema::hasTable('saham_staff') && ! Schema::hasColumn('saham_staff', 'tambahan_saham')) {
            Schema::table('saham_staff', function (Blueprint $table): void {
                $table->decimal('tambahan_saham', 12, 2)->default(0)->after('syer');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('saham', 'tambahan_saham')) {
            Schema::table('saham', function (Blueprint $table): void {
                $table->dropColumn('tambahan_saham');
            });
        }

        if (Schema::hasTable('saham_staff') && Schema::hasColumn('saham_staff', 'tambahan_saham')) {
            Schema::table('saham_staff', function (Blueprint $table): void {
                $table->dropColumn('tambahan_saham');
            });
        }
    }
};
