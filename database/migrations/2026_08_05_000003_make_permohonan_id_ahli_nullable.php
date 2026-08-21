<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('permohonan', function (Blueprint $table): void {
                $table->unsignedBigInteger('id_ahli')->nullable()->change();
            });

            return;
        }

        DB::statement('ALTER TABLE permohonan DROP FOREIGN KEY permohonan_id_ahli_foreign');
        DB::statement('ALTER TABLE permohonan MODIFY id_ahli INT UNSIGNED NULL');
        DB::statement('ALTER TABLE permohonan ADD CONSTRAINT permohonan_id_ahli_foreign FOREIGN KEY (id_ahli) REFERENCES ahli(id_ahli) ON DELETE CASCADE');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('permohonan', function (Blueprint $table): void {
                $table->unsignedBigInteger('id_ahli')->nullable(false)->change();
            });

            return;
        }

        DB::statement('ALTER TABLE permohonan DROP FOREIGN KEY permohonan_id_ahli_foreign');
        DB::statement('ALTER TABLE permohonan MODIFY id_ahli INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE permohonan ADD CONSTRAINT permohonan_id_ahli_foreign FOREIGN KEY (id_ahli) REFERENCES ahli(id_ahli) ON DELETE CASCADE');
    }
};
