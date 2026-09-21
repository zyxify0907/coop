<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('monthly_allowances')) {
            return;
        }

        Schema::create('monthly_allowances', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('present_days')->default(0);
            $table->unsignedInteger('full_days')->default(0);
            $table->unsignedInteger('half_days')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('total_minutes')->default(0);
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->decimal('total_allowance', 10, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->string('generated_by_role', 20)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'month', 'year']);
            $table->index(['year', 'month', 'status']);
            $table->index(['staff_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_allowances');
    }
};
