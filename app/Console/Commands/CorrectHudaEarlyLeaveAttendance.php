<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\Pekerja;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CorrectHudaEarlyLeaveAttendance extends Command
{
    protected $signature = 'attendance:correct-huda-early-leave';

    protected $description = 'Kemaskan rekod keluar awal dan lewat Nurul Huda untuk September 2026.';

    public function handle(): int
    {
        $staff = Pekerja::query()->where('no_pekerja', 'PBT-008')->first();

        if (! $staff) {
            $this->error('Pekerja PBT-008 tidak ditemui.');

            return self::FAILURE;
        }

        $records = [
            [
                'date' => '2026-09-23',
                'check_in' => '00:10:00',
                'check_out' => '06:35:00',
                'late_minutes' => 10,
                'early_leave_minutes' => 145,
                'working_minutes' => 385,
                'break_minutes' => 60,
                'total_minutes' => 325,
                'allowance_amount' => 27.08,
            ],
            [
                'date' => '2026-09-24',
                'check_in' => '00:03:00',
                'check_out' => '05:40:00',
                'late_minutes' => 3,
                'early_leave_minutes' => 200,
                'working_minutes' => 337,
                'break_minutes' => 40,
                'total_minutes' => 297,
                'allowance_amount' => 24.75,
            ],
        ];

        foreach ($records as $record) {
            AttendanceRecord::query()->updateOrCreate(
                [
                    'staff_id' => $staff->id_pekerja,
                    'attendance_date' => $record['date'],
                ],
                [
                    'check_in_time' => CarbonImmutable::parse("{$record['date']} {$record['check_in']}", 'UTC'),
                    'check_out_time' => CarbonImmutable::parse("{$record['date']} {$record['check_out']}", 'UTC'),
                    'check_in_gps_verified' => true,
                    'check_out_gps_verified' => true,
                    'late_minutes' => $record['late_minutes'],
                    'early_leave_minutes' => $record['early_leave_minutes'],
                    'working_minutes' => $record['working_minutes'],
                    'break_minutes' => $record['break_minutes'],
                    'total_minutes' => $record['total_minutes'],
                    'daily_rate' => 40,
                    'allowance_amount' => $record['allowance_amount'],
                    'status' => 'early_leave',
                ],
            );
        }

        $this->info('Huda dikemaskini: 23/09 (5 jam 25 minit, lewat 10 minit) dan 24/09 (keluar awal 3 jam 20 minit, lewat 3 minit).');

        return self::SUCCESS;
    }
}
