<?php

namespace App\Console\Commands;

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

        $result = $attendance->finalizeDate($date, $setting);
        $updated = $result['missing_checkout'];
        $absent = $result['absent'];

        $this->info("Selesai: {$updated} tiada Check Out, {$absent} tidak hadir.");

        return self::SUCCESS;
    }
}
