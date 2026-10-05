<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['ahli', 'pekerja', 'admin'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'profile_image_path')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->string('profile_image_path')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['ahli', 'pekerja', 'admin'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'profile_image_path')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropColumn('profile_image_path');
                });
            }
        }
    }
};
