<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\Pekerja;
use App\Services\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class FinalizeAttendanceDay extends Command
{
    protected $signature = 'attendance:finalize {date? : Tarikh YYYY-MM-DD, default hari ini}';

    protected $description = 'Tandakan tiada Check Out dan tidak hadir selepas hari bekerja tamat';

    public function handle(AttendanceService $attendance): int
    {
        $date = CarbonImmutable::parse($this->argument('date') ?: today()->toDateString());
        $setting = $attendance->setting();

        if (! $attendance->isWorkingDay($date, $setting)) {
            $this->info('Hari ini bukan hari bekerja. Tiada rekod diubah.');

            return self::SUCCESS;
        }

        $workers = Pekerja::query()->where('staff_type', 'coop_staff')->where('status_aktif', true)->get();
        $updated = 0;
        $absent = 0;

        foreach ($workers as $worker) {
            $record = AttendanceRecord::query()->firstOrNew(['staff_id' => $worker->id_pekerja, 'attendance_date' => $date->toDateString()]);
            if (! $record->exists) {
                $record->fill(['status' => 'absent', 'location_name' => $setting->location_name]);
                $record->save();
                $absent++;
                continue;
            }

            if ($record->check_in_time && ! $record->check_out_time) {
                $record->status = 'missing_checkout';
                $record->save();
                $updated++;
            }
        }

        $this->info("Selesai: {$updated} tiada Check Out, {$absent} tidak hadir.");

        return self::SUCCESS;
    }
}
