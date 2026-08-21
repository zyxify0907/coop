<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan', function (Blueprint $table): void {
            $table->id('id_permohonan');
            $table->unsignedInteger('id_ahli');
            $table->string('jenis', 30);
            $table->string('nama_pemohon', 100);
            $table->string('no_matrik', 20);
            $table->string('email')->nullable();
            $table->string('no_tel', 20)->nullable();
            $table->string('status', 30)->default('baru');
            $table->json('data_permohonan')->nullable();
            $table->text('catatan_pelajar')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->date('tarikh_permohonan');
            $table->date('tarikh_keputusan')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'status']);
            $table->index('tarikh_permohonan');
            $table->foreign('id_ahli')->references('id_ahli')->on('ahli')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan');
    }
};
