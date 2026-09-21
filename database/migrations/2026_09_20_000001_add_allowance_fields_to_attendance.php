<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendance_settings', 'break_start_time')) {
                $table->time('break_start_time')->default('13:00:00')->after('work_end_time');
            }
            if (! Schema::hasColumn('attendance_settings', 'break_end_time')) {
                $table->time('break_end_time')->default('14:00:00')->after('break_start_time');
            }
            if (! Schema::hasColumn('attendance_settings', 'full_day_minutes')) {
                $table->unsignedSmallInteger('full_day_minutes')->default(480)->after('grace_period_minutes');
            }
            if (! Schema::hasColumn('attendance_settings', 'half_day_rate_multiplier')) {
                $table->decimal('half_day_rate_multiplier', 5, 2)->default(0.50)->after('full_day_minutes');
            }
        });

        Schema::table('attendance_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendance_records', 'break_minutes')) {
                $table->unsignedSmallInteger('break_minutes')->default(0)->after('check_out_time');
            }
            if (! Schema::hasColumn('attendance_records', 'total_minutes')) {
                $table->unsignedInteger('total_minutes')->default(0)->after('break_minutes');
            }
            if (! Schema::hasColumn('attendance_records', 'daily_rate')) {
                $table->decimal('daily_rate', 10, 2)->default(0)->after('working_minutes');
            }
            if (! Schema::hasColumn('attendance_records', 'allowance_amount')) {
                $table->decimal('allowance_amount', 10, 2)->default(0)->after('daily_rate');
            }
        });

        if (Schema::hasColumn('attendance_records', 'working_minutes') && Schema::hasColumn('attendance_records', 'total_minutes')) {
            DB::table('attendance_records')
                ->where('total_minutes', 0)
                ->where('working_minutes', '>', 0)
                ->update(['total_minutes' => DB::raw('working_minutes')]);
        }
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            foreach (['allowance_amount', 'daily_rate', 'total_minutes', 'break_minutes'] as $column) {
                if (Schema::hasColumn('attendance_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('attendance_settings', function (Blueprint $table): void {
            foreach (['half_day_rate_multiplier', 'full_day_minutes', 'break_end_time', 'break_start_time'] as $column) {
                if (Schema::hasColumn('attendance_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
