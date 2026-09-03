<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('item_tempahan')) {
            return;
        }

        Schema::table('item_tempahan', function (Blueprint $table): void {
            if (! Schema::hasColumn('item_tempahan', 'status')) {
                $table->string('status', 30)->default('baru')->after('subtotal');
            }

            if (! Schema::hasColumn('item_tempahan', 'tarikh_ambil')) {
                $table->date('tarikh_ambil')->nullable()->after('status');
            }
        });

        DB::table('item_tempahan')
            ->join('tempahan', 'item_tempahan.id_tempahan', '=', 'tempahan.id_tempahan')
            ->update([
                'item_tempahan.status' => DB::raw('tempahan.status'),
                'item_tempahan.tarikh_ambil' => DB::raw('tempahan.tarikh_ambil'),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('item_tempahan')) {
            return;
        }

        Schema::table('item_tempahan', function (Blueprint $table): void {
            $columns = [];

            if (Schema::hasColumn('item_tempahan', 'tarikh_ambil')) {
                $columns[] = 'tarikh_ambil';
            }

            if (Schema::hasColumn('item_tempahan', 'status')) {
                $columns[] = 'status';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
