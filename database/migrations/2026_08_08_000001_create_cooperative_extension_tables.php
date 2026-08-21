<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->string('actor_role', 20);
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('action', 50);
                $table->string('module', 80);
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('description')->nullable();
                $table->json('meta')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['actor_role', 'actor_id']);
                $table->index(['module', 'action']);
                $table->index(['subject_type', 'subject_id']);
            });
        }

        if (! Schema::hasTable('document_uploads')) {
            Schema::create('document_uploads', function (Blueprint $table): void {
                $table->id();
                $table->string('owner_role', 20);
                $table->unsignedBigInteger('owner_id');
                $table->string('uploaded_by_role', 20);
                $table->unsignedBigInteger('uploaded_by_id')->nullable();
                $table->string('documentable_type')->nullable();
                $table->unsignedBigInteger('documentable_id')->nullable();
                $table->string('category', 80);
                $table->string('original_name');
                $table->string('stored_path');
                $table->string('mime_type', 100)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();

                $table->index(['owner_role', 'owner_id']);
                $table->index(['documentable_type', 'documentable_id']);
            });
        }

        if (! Schema::hasTable('share_transactions')) {
            Schema::create('share_transactions', function (Blueprint $table): void {
                $table->id();
                $table->string('member_type', 20);
                $table->unsignedBigInteger('member_id');
                $table->string('transaction_type', 60);
                $table->string('direction', 10);
                $table->decimal('amount', 12, 2);
                $table->decimal('balance_after', 12, 2)->default(0);
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('processed_by_role', 20)->nullable();
                $table->unsignedBigInteger('processed_by_id')->nullable();
                $table->text('notes')->nullable();
                $table->date('transacted_at');
                $table->timestamps();

                $table->index(['member_type', 'member_id']);
                $table->index(['reference_type', 'reference_id']);
                $table->index(['transaction_type', 'direction']);
            });
        }

        if (! Schema::hasTable('cooperative_settings')) {
            Schema::create('cooperative_settings', function (Blueprint $table): void {
                $table->string('key')->primary();
                $table->text('value')->nullable();
                $table->string('type', 30)->default('string');
                $table->string('label')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->id();
                $table->string('recipient_role', 20);
                $table->unsignedBigInteger('recipient_id');
                $table->string('title');
                $table->text('message')->nullable();
                $table->string('link')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['recipient_role', 'recipient_id', 'read_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('cooperative_settings');
        Schema::dropIfExists('share_transactions');
        Schema::dropIfExists('document_uploads');
        Schema::dropIfExists('audit_logs');
    }
};
