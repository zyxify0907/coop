<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_uploads') && ! Schema::hasColumn('document_uploads', 'application_purpose')) {
            Schema::table('document_uploads', function (Blueprint $table): void {
                $table->string('application_purpose', 160)->nullable()->after('category');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('document_uploads') && Schema::hasColumn('document_uploads', 'application_purpose')) {
            Schema::table('document_uploads', function (Blueprint $table): void {
                $table->dropColumn('application_purpose');
            });
        }
    }
};
