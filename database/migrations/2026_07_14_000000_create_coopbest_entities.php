<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ahli', function (Blueprint $table): void {
            $table->id('id_ahli');
            $table->string('no_matrik', 20)->unique();
            $table->string('nama');
            $table->string('nric', 20)->nullable()->unique();
            $table->string('password_hash')->nullable();
            $table->string('semester', 10)->nullable();
            $table->string('program', 50)->nullable();
            $table->string('no_tel', 15)->nullable();
            $table->string('email')->nullable();
            $table->decimal('baki_ewallet', 10, 2)->default(0);
            $table->date('tarikh_daftar')->nullable();
            $table->boolean('status_aktif')->default(true);

            $table->index('no_matrik');
            $table->index('semester');
            $table->index('status_aktif');
        });

        Schema::create('pekerja', function (Blueprint $table): void {
            $table->id('id_pekerja');
            $table->string('no_pekerja', 20)->unique();
            $table->string('nama', 100);
            $table->string('nric', 20)->nullable()->unique();
            $table->string('password_hash')->nullable();
            $table->string('jawatan', 50)->nullable();
            $table->string('no_tel', 15)->nullable();
            $table->string('email')->nullable();
            $table->decimal('kadar_elaun', 10, 2)->default(0);
            $table->date('tarikh_mula')->nullable();
            $table->boolean('status_aktif')->default(true);
        });

        Schema::create('admin', function (Blueprint $table): void {
            $table->id('id_admin');
            $table->string('nama', 100);
            $table->string('username', 50)->unique();
            $table->string('nric', 20)->nullable()->unique();
            $table->string('password_hash');
            $table->string('peranan', 50)->default('Admin');
            $table->boolean('status_aktif')->default(true);
        });

        Schema::create('stok', function (Blueprint $table): void {
            $table->id('item_id');
            $table->string('nama_item');
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('harga', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('tempahan', function (Blueprint $table): void {
            $table->id('tempahan_id');
            $table->string('no_matrik');
            $table->foreignId('item_id')->nullable()->constrained('stok', 'item_id')->nullOnDelete();
            $table->string('item');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('status')->default('baru');
            $table->timestamps();

            $table->foreign('no_matrik')->references('no_matrik')->on('ahli')->cascadeOnDelete();
        });

        Schema::create('saham', function (Blueprint $table): void {
            $table->id('id_saham');
            $table->foreignId('id_ahli')->constrained('ahli', 'id_ahli')->cascadeOnDelete();
            $table->decimal('syer', 10, 2)->default(0);
            $table->decimal('yuran', 10, 2)->default(0);
            $table->date('tarikh_kemaskini')->nullable();

            $table->unique('id_ahli');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saham');
        Schema::dropIfExists('tempahan');
        Schema::dropIfExists('stok');
        Schema::dropIfExists('admin');
        Schema::dropIfExists('pekerja');
        Schema::dropIfExists('ahli');
    }
};
