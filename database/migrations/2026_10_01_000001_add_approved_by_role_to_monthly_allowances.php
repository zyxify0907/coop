<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('monthly_allowances') || Schema::hasColumn('monthly_allowances', 'approved_by_role')) {
            return;
        }

        Schema::table('monthly_allowances', function (Blueprint $table): void {
            $table->string('approved_by_role', 20)->nullable()->after('approved_by');
        });

        DB::table('monthly_allowances')
            ->whereNotNull('approved_by')
            ->orderBy('id')
            ->chunkById(200, function ($allowances): void {
                foreach ($allowances as $allowance) {
                    $isAdmin = DB::table('admin')->where('id_admin', $allowance->approved_by)->exists();
                    $isStaff = DB::table('pekerja')->where('id_pekerja', $allowance->approved_by)->exists();

                    if ($isAdmin !== $isStaff) {
                        DB::table('monthly_allowances')
                            ->where('id', $allowance->id)
                            ->update(['approved_by_role' => $isAdmin ? 'admin' : 'staff']);
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('monthly_allowances') && Schema::hasColumn('monthly_allowances', 'approved_by_role')) {
            Schema::table('monthly_allowances', function (Blueprint $table): void {
                $table->dropColumn('approved_by_role');
            });
        }
    }
};
