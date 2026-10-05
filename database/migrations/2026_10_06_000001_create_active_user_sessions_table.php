<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_user_sessions', function (Blueprint $table): void {
            $table->string('role', 20);
            $table->unsignedBigInteger('user_id');
            $table->string('session_id', 255);
            $table->timestamp('last_activity_at');

            $table->primary(['role', 'user_id']);
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_user_sessions');
    }
};
