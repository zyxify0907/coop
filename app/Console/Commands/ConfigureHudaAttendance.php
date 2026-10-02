<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\Pekerja;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ConfigureHudaAttendance extends Command
{
    protected $signature = 'attendance:configure-huda';

    protected $description = 'Tetapkan rekod kehadiran dan elaun contoh untuk Nurul Huda';

    public function handle(): int
    {
        $staff = Pekerja::query()->where('no_pekerja', 'PBT-008')->first();

        if (! $staff) {
            $this->error('Pekerja PBT-008 tidak ditemui.');

            return self::FAILURE;
        }

        $staff->update(['kadar_elaun' => 40]);

        $fullDays = ['2026-09-20', '2026-09-21', '2026-09-22'];
        $earlyLeaveDays = ['2026-09-23', '2026-09-24', '2026-09-27'];
        $absentDays = ['2026-09-28', '2026-09-30'];

        foreach ($fullDays as $date) {
            $this->saveRecord($staff->id_pekerja, $date, 'present', '00:00:00', '09:00:00', 0, 540, 60, 480, 40);
        }

        foreach ($earlyLeaveDays as $date) {
            $this->saveRecord($staff->id_pekerja, $date, 'early_leave', '00:00:00', '05:00:00', 240, 300, 0, 300, 25);
        }

        foreach ($absentDays as $date) {
            AttendanceRecord::query()->updateOrCreate(
                ['staff_id' => $staff->id_pekerja, 'attendance_date' => $date],
                [
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'check_in_gps_verified' => false,
                    'check_out_gps_verified' => false,
                    'late_minutes' => 0,
                    'early_leave_minutes' => 0,
                    'working_minutes' => 0,
                    'break_minutes' => 0,
                    'total_minutes' => 0,
                    'daily_rate' => 40,
                    'allowance_amount' => 0,
                    'status' => 'absent',
                ]
            );
        }

        $this->info('Huda dikemaskini: 3 hadir penuh, 3 keluar awal, 2 tidak hadir. Kadar elaun RM40.');

        return self::SUCCESS;
    }

    private function saveRecord(int $staffId, string $date, string $status, string $checkInTime, string $checkOutTime, int $earlyLeaveMinutes, int $workingMinutes, int $breakMinutes, int $totalMinutes, int $allowanceAmount): void
    {
        AttendanceRecord::query()->updateOrCreate(
            ['staff_id' => $staffId, 'attendance_date' => $date],
            [
                'check_in_time' => CarbonImmutable::parse($date.' '.$checkInTime, 'UTC'),
                'check_out_time' => CarbonImmutable::parse($date.' '.$checkOutTime, 'UTC'),
                'check_in_gps_verified' => true,
                'check_out_gps_verified' => true,
                'late_minutes' => 0,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'working_minutes' => $workingMinutes,
                'break_minutes' => $breakMinutes,
                'total_minutes' => $totalMinutes,
                'daily_rate' => 40,
                'allowance_amount' => $allowanceAmount,
                'status' => $status,
            ]
        );
    }
}
