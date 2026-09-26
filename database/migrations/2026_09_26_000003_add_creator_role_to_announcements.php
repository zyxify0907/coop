<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('announcements') && ! Schema::hasColumn('announcements', 'created_by_role')) {
            Schema::table('announcements', function (Blueprint $table): void {
                $table->string('created_by_role', 20)->nullable()->after('created_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('announcements') && Schema::hasColumn('announcements', 'created_by_role')) {
            Schema::table('announcements', function (Blueprint $table): void {
                $table->dropColumn('created_by_role');
            });
        }
    }
};
