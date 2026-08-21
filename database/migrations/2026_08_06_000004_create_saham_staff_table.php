<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saham_staff', function (Blueprint $table): void {
            $table->id('id_saham_staff');
            $table->integer('id_pekerja')->unique();
            $table->decimal('syer', 12, 2)->default(0);
            $table->date('tarikh_kemaskini')->nullable();

            $table->foreign('id_pekerja')->references('id_pekerja')->on('pekerja')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saham_staff');
    }
};
