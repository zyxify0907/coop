<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('item_baju') && ! Schema::hasColumn('item_baju', 'size_chart_path')) {
            Schema::table('item_baju', function (Blueprint $table): void {
                $table->string('size_chart_path')->nullable()->after('image_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('item_baju') && Schema::hasColumn('item_baju', 'size_chart_path')) {
            Schema::table('item_baju', function (Blueprint $table): void {
                $table->dropColumn('size_chart_path');
            });
        }
    }
};
