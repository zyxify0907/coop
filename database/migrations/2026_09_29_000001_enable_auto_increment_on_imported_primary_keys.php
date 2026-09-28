<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $autoIncrementColumns = [
            'admin' => ['id_admin', 'INT'],
            'ahli' => ['id_ahli', 'INT'],
            'ahli_imports' => ['id', 'BIGINT UNSIGNED'],
            'announcements' => ['id', 'BIGINT UNSIGNED'],
            'attendance_corrections' => ['id', 'BIGINT UNSIGNED'],
            'attendance_records' => ['id', 'BIGINT UNSIGNED'],
            'attendance_settings' => ['id', 'BIGINT UNSIGNED'],
            'audit_logs' => ['id', 'BIGINT UNSIGNED'],
            'cooperative_events' => ['id', 'BIGINT UNSIGNED'],
            'document_uploads' => ['id', 'BIGINT UNSIGNED'],
            'elaun' => ['id_elaun', 'INT'],
            'failed_jobs' => ['id', 'BIGINT UNSIGNED'],
            'item_baju' => ['id_item', 'INT'],
            'item_tempahan' => ['id_item_tempahan', 'INT'],
            'jobs' => ['id', 'BIGINT UNSIGNED'],
            'kategori_baju' => ['id_kategori', 'INT'],
            'kehadiran' => ['id_kehadiran', 'INT'],
            'log_sistem' => ['id_log', 'INT'],
            'migrations' => ['id', 'INT UNSIGNED'],
            'monthly_allowances' => ['id', 'BIGINT UNSIGNED'],
            'notifications' => ['id', 'BIGINT UNSIGNED'],
            'pekerja' => ['id_pekerja', 'INT'],
            'pembayaran_ahli' => ['id_pembayaran', 'INT'],
            'permohonan' => ['id_permohonan', 'BIGINT UNSIGNED'],
            'saham' => ['id_saham', 'INT'],
            'saham_staff' => ['id_saham_staff', 'BIGINT UNSIGNED'],
            'share_transactions' => ['id', 'BIGINT UNSIGNED'],
            'stok' => ['id_stok', 'INT'],
            'tempahan' => ['id_tempahan', 'INT'],
            'tugasan' => ['id_tugasan', 'INT'],
            'users' => ['id', 'BIGINT UNSIGNED'],
        ];

        foreach ($autoIncrementColumns as $table => [$column, $type]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                $hasLeadingIndex = DB::selectOne(
                    'SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND SEQ_IN_INDEX = 1 LIMIT 1',
                    [$table, $column],
                );

                if (! $hasLeadingIndex) {
                    $hasPrimaryKey = DB::selectOne(
                        'SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
                        [$table, 'PRIMARY'],
                    );

                    if ($hasPrimaryKey) {
                        $index = 'uq_ai_'.$table.'_'.$column;
                        DB::statement("ALTER TABLE `{$table}` ADD UNIQUE KEY `{$index}` (`{$column}`)");
                    } else {
                        DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$column}`)");
                    }
                }

                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$type} NOT NULL AUTO_INCREMENT");
            }
        }
    }

    public function down(): void
    {
        // Keep generated IDs enabled; reverting this repair could break inserts.
    }
};

