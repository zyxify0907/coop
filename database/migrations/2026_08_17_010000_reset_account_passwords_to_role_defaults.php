<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ahli')
            ->orderBy('id_ahli')
            ->get(['id_ahli', 'no_matrik'])
            ->each(function (object $student): void {
                DB::table('ahli')
                    ->where('id_ahli', $student->id_ahli)
                    ->update([
                        'password_hash' => Hash::make(strtoupper(trim((string) $student->no_matrik)).'@123'),
                    ]);
            });

        foreach ([
            'pekerja' => 'staff12345',
            'admin' => 'admin12345',
        ] as $table => $defaultPassword) {
            DB::table($table)->update([
                'password_hash' => Hash::make($defaultPassword),
            ]);
        }
    }

    public function down(): void
    {
        // Password hashes cannot be restored after a deliberate reset.
    }
};
