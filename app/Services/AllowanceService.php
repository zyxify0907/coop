<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\MonthlyAllowance;
use App\Models\Pekerja;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AllowanceService
{
    public function __construct(private readonly AttendanceService $attendance)
    {
    }

    public function calculateRecord(AttendanceRecord $record, ?AttendanceSetting $setting = null): AttendanceRecord
    {
        $setting ??= $this->attendance->setting();
        $dailyRate = (float) ($record->staff?->kadar_elaun ?? 0);
        $fullDayMinutes = max(1, (int) ($setting->full_day_minutes ?? 480));

        $record->break_minutes = $record->check_in_time && $record->check_out_time
            ? $this->breakMinutes($record, $setting)
            : 0;
        $record->total_minutes = max(0, (int) ($record->working_minutes ?? 0) - (int) $record->break_minutes);
        $record->daily_rate = $dailyRate;
        $eligible = $this->eligibleForAllowance($record);
        $record->allowance_amount = match (true) {
            ! $eligible => 0,
            $record->status === 'half_day' => round($dailyRate * (float) ($setting->half_day_rate_multiplier ?? 0.50), 2),
            default => round(((int) $record->total_minutes / $fullDayMinutes) * $dailyRate, 2),
        };

        return $record;
    }

    public function generateMonthly(Pekerja $staff, int $month, int $year, string $actorRole, int $actorId): MonthlyAllowance
    {
        $setting = $this->attendance->setting();
        $this->attendance->finalizeDueDates(setting: $setting);

        $records = AttendanceRecord::query()
            ->with('staff')
            ->where('staff_id', $staff->id_pekerja)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->get();

        $records->each(function (AttendanceRecord $record) use ($setting): void {
            $this->calculateRecord($record, $setting);
            $record->save();
        });

        $summary = $this->summary($records, $setting);

        return MonthlyAllowance::query()->updateOrCreate(
            ['staff_id' => $staff->id_pekerja, 'month' => $month, 'year' => $year],
            [
                ...$summary,
                'daily_rate' => $staff->kadar_elaun ?? 0,
                'status' => MonthlyAllowance::STATUS_PENDING,
                'generated_by' => $actorId,
                'generated_by_role' => $actorRole,
                'approved_at' => null,
                'approved_by' => null,
                'approved_by_role' => null,
                'paid_at' => null,
                'paid_by' => null,
            ]
        );
    }

    /** @param Collection<int, AttendanceRecord> $records */
    public function summary(Collection $records, AttendanceSetting $setting): array
    {
        $eligible = $records->filter(fn (AttendanceRecord $record): bool => $this->eligibleForAllowance($record));
        $fullDayMinutes = max(1, (int) ($setting->full_day_minutes ?? 480));
        $halfDayFloor = (int) ceil($fullDayMinutes / 2);

        return [
            'present_days' => $eligible->count(),
            'full_days' => $eligible->filter(fn (AttendanceRecord $record): bool => (int) $record->total_minutes >= $fullDayMinutes)->count(),
            'half_days' => $eligible->filter(fn (AttendanceRecord $record): bool => (int) $record->total_minutes >= $halfDayFloor && (int) $record->total_minutes < $fullDayMinutes)->count(),
            'late_minutes' => $records->sum('late_minutes'),
            'total_minutes' => $eligible->sum('total_minutes'),
            'total_allowance' => round($eligible->sum('allowance_amount'), 2),
        ];
    }

    public function eligibleForAllowance(AttendanceRecord $record): bool
    {
        return in_array($record->status, ['present', 'late', 'early_leave', 'half_day', 'outside_area'], true)
            && (int) ($record->total_minutes ?? 0) > 0;
    }

    private function breakMinutes(AttendanceRecord $record, AttendanceSetting $setting): int
    {
        $checkIn = CarbonImmutable::parse($record->check_in_time)->timezone('Asia/Kuala_Lumpur');
        $checkOut = CarbonImmutable::parse($record->check_out_time)->timezone('Asia/Kuala_Lumpur');
        $date = $record->attendance_date->toDateString();
        $breakStart = CarbonImmutable::parse($date.' '.($setting->break_start_time ?? '13:00:00'), 'Asia/Kuala_Lumpur');
        $breakEnd = CarbonImmutable::parse($date.' '.($setting->break_end_time ?? '14:00:00'), 'Asia/Kuala_Lumpur');
        $overlapStart = $checkIn->greaterThan($breakStart) ? $checkIn : $breakStart;
        $overlapEnd = $checkOut->lessThan($breakEnd) ? $checkOut : $breakEnd;

        return $overlapEnd->greaterThan($overlapStart) ? $overlapStart->diffInMinutes($overlapEnd) : 0;
    }
}
