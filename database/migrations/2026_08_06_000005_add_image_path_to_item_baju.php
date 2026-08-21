<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('item_baju') && ! Schema::hasColumn('item_baju', 'image_path')) {
            Schema::table('item_baju', function (Blueprint $table): void {
                $table->string('image_path')->nullable()->after('stok_tertinggal');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('item_baju') && Schema::hasColumn('item_baju', 'image_path')) {
            Schema::table('item_baju', function (Blueprint $table): void {
                $table->dropColumn('image_path');
            });
        }
    }
};
