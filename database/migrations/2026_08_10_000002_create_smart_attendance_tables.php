<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance_settings')) {
            Schema::create('attendance_settings', function (Blueprint $table): void {
                $table->id();
                $table->time('work_start_time')->default('08:00:00');
                $table->time('work_end_time')->default('17:00:00');
                $table->unsignedSmallInteger('grace_period_minutes')->default(10);
                $table->time('checkout_cutoff_time')->default('20:00:00');
                $table->json('working_days')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->unsignedInteger('allowed_radius_meter')->default(100);
                $table->string('location_name')->nullable();
                $table->boolean('status')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('attendance_records')) {
            Schema::create('attendance_records', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('staff_id');
                $table->date('attendance_date');
                $table->dateTime('check_in_time')->nullable();
                $table->dateTime('check_out_time')->nullable();
                $table->decimal('check_in_latitude', 10, 8)->nullable();
                $table->decimal('check_in_longitude', 11, 8)->nullable();
                $table->decimal('check_out_latitude', 10, 8)->nullable();
                $table->decimal('check_out_longitude', 11, 8)->nullable();
                $table->boolean('check_in_gps_verified')->default(false);
                $table->boolean('check_out_gps_verified')->default(false);
                $table->unsignedInteger('late_minutes')->default(0);
                $table->unsignedInteger('early_leave_minutes')->default(0);
                $table->unsignedInteger('working_minutes')->default(0);
                $table->string('status', 30)->default('present');
                $table->string('location_name')->nullable();
                $table->text('device_information')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['staff_id', 'attendance_date']);
                $table->index(['attendance_date', 'status']);
            });
        }

        if (! Schema::hasTable('attendance_corrections')) {
            Schema::create('attendance_corrections', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('attendance_id')->nullable();
                $table->unsignedBigInteger('staff_id');
                $table->date('correction_date');
                $table->string('correction_type', 40);
                $table->time('requested_time')->nullable();
                $table->text('reason');
                $table->string('supporting_document')->nullable();
                $table->string('status', 20)->default('pending');
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('admin_remark')->nullable();
                $table->timestamps();

                $table->index(['staff_id', 'status']);
                $table->index(['attendance_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_settings');
    }
};
