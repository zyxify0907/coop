<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ahli', 'kelas')) {
            Schema::table('ahli', function (Blueprint $table): void {
                $table->string('kelas', 10)->nullable()->after('program');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ahli', 'kelas')) {
            Schema::table('ahli', function (Blueprint $table): void {
                $table->dropColumn('kelas');
            });
        }
    }
};
