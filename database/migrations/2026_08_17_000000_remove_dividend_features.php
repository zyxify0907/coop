<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permohonan')) {
            DB::table('permohonan')
                ->select('id_permohonan', 'data_permohonan')
                ->orderBy('id_permohonan')
                ->each(function (object $application): void {
                    $data = json_decode((string) $application->data_permohonan, true);

                    if (! is_array($data)) {
                        return;
                    }

                    unset($data['dividen_dipohon']);

                    if (isset($data['jenis_permohonan']) && is_array($data['jenis_permohonan'])) {
                        $data['jenis_permohonan'] = array_values(array_filter(
                            $data['jenis_permohonan'],
                            fn ($type) => $type !== 'Pengeluaran Dividen'
                        ));
                    }

                    if (array_key_exists('jumlah_dipohon', $data)) {
                        $data['jumlah_dipohon'] = (float) ($data['saham_dipohon'] ?? 0);
                    }

                    DB::table('permohonan')
                        ->where('id_permohonan', $application->id_permohonan)
                        ->update(['data_permohonan' => json_encode($data)]);
                });
        }

        if (Schema::hasTable('document_uploads')) {
            DB::table('document_uploads')->where('category', 'pengeluaran_dividen')->delete();

            if (Schema::hasColumn('document_uploads', 'application_purpose')) {
                DB::table('document_uploads')
                    ->where('application_purpose', 'like', '%Dividen%')
                    ->update(['application_purpose' => 'Permohonan Pengeluaran Saham / Berhenti']);
            }
        }

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('title', 'like', '%Dividen%')
                ->orWhere('message', 'like', '%Dividen%')
                ->delete();
        }

        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')
                ->where('module', 'dividend')
                ->orWhere('description', 'like', '%Dividen%')
                ->delete();
        }

        Schema::dropIfExists('dividend_records');
        Schema::dropIfExists('dividend_batches');

        if (Schema::hasColumn('saham', 'dividen_terkumpul')) {
            Schema::table('saham', function (Blueprint $table): void {
                $table->dropColumn('dividen_terkumpul');
            });
        }

        if (Schema::hasTable('saham_staff') && Schema::hasColumn('saham_staff', 'dividen_terkumpul')) {
            Schema::table('saham_staff', function (Blueprint $table): void {
                $table->dropColumn('dividen_terkumpul');
            });
        }

        if (Schema::hasTable('cooperative_settings')) {
            DB::table('cooperative_settings')->where('key', 'dividend_rate')->delete();
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_create_saham_new_ahli');

        if (Schema::hasTable('ahli') && Schema::hasTable('saham')) {
            DB::unprepared('
                CREATE TRIGGER trg_create_saham_new_ahli
                AFTER INSERT ON ahli
                FOR EACH ROW
                BEGIN
                    INSERT INTO saham (id_ahli, syer, yuran, tarikh_kemaskini)
                    VALUES (NEW.id_ahli, 0.00, 0.00, CURRENT_DATE);
                END
            ');
        }
    }

    public function down(): void
    {
        Schema::table('saham', function (Blueprint $table): void {
            $table->decimal('dividen_terkumpul', 10, 2)->default(0);
        });

        if (Schema::hasTable('saham_staff')) {
            Schema::table('saham_staff', function (Blueprint $table): void {
                $table->decimal('dividen_terkumpul', 12, 2)->default(0);
            });
        }
    }
};
