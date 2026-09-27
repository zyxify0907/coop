<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\Pekerja;
use Carbon\CarbonImmutable;

class AttendanceService
{
    private const TIMEZONE = 'Asia/Kuala_Lumpur';

    public function setting(): AttendanceSetting
    {
        return AttendanceSetting::query()->firstOrCreate([], [
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
            'break_start_time' => '13:00:00',
            'break_end_time' => '14:00:00',
            'grace_period_minutes' => 15,
            'full_day_minutes' => 480,
            'half_day_rate_multiplier' => 0.50,
            'checkout_cutoff_time' => '20:00:00',
            'working_days' => [1, 2, 3, 4, 5],
            'allowed_radius_meter' => 100,
            'location_name' => 'Koperasi Politeknik Besut',
            'status' => false,
        ]);
    }

    public function isWorkingDay(CarbonImmutable $date, ?AttendanceSetting $setting = null): bool
    {
        $days = ($setting ?? $this->setting())->working_days ?: [1, 2, 3, 4, 5];

        return in_array($date->dayOfWeekIso, array_map('intval', $days), true);
    }

    /** @return array{verified:bool,message:string,distance:?float} */
    public function verifyLocation(float $latitude, float $longitude, AttendanceSetting $setting): array
    {
        if (! $setting->status || $setting->latitude === null || $setting->longitude === null) {
            return ['verified' => false, 'message' => 'Lokasi Smart Attendance belum ditetapkan oleh admin.', 'distance' => null];
        }

        $distance = $this->distanceMeters($latitude, $longitude, (float) $setting->latitude, (float) $setting->longitude);
        if ($distance > (int) $setting->allowed_radius_meter) {
            return [
                'verified' => false,
                'message' => 'Anda berada di luar kawasan kehadiran yang dibenarkan.',
                'distance' => $distance,
            ];
        }

        return ['verified' => true, 'message' => 'GPS disahkan.', 'distance' => $distance];
    }

    public function applyCheckInStatus(AttendanceRecord $record, AttendanceSetting $setting): void
    {
        $checkIn = $this->localDateTime($record->check_in_time);
        $start = $this->scheduledDateTime($record, $setting->work_start_time);
        $lateMinutes = max(0, $start->diffInMinutes($checkIn, false));

        $record->late_minutes = $lateMinutes;
        $record->status = $lateMinutes > (int) $setting->grace_period_minutes ? 'late' : 'present';
    }

    public function applyCheckoutStatus(AttendanceRecord $record, AttendanceSetting $setting): void
    {
        if (! $record->check_in_time || ! $record->check_out_time) {
            return;
        }

        $checkIn = $this->localDateTime($record->check_in_time);
        $checkOut = $this->localDateTime($record->check_out_time);
        $end = $this->scheduledDateTime($record, $setting->work_end_time);

        $record->working_minutes = max(0, $checkIn->diffInMinutes($checkOut));
        $record->break_minutes = $this->breakMinutes($record, $setting);
        $record->total_minutes = max(0, (int) $record->working_minutes - (int) $record->break_minutes);
        $record->early_leave_minutes = max(0, $checkOut->lessThan($end) ? $checkOut->diffInMinutes($end) : 0);
        $record->status = $record->early_leave_minutes > 0 ? 'early_leave' : ($record->late_minutes > (int) $setting->grace_period_minutes ? 'late' : 'present');
        $this->applyAllowanceAmount($record, $setting);
    }

    public function displayStatus(?AttendanceRecord $record, AttendanceSetting $setting, ?CarbonImmutable $now = null): string
    {
        if (! $record) {
            return 'not_checked_in';
        }

        $now ??= CarbonImmutable::now(self::TIMEZONE);
        if ($record->check_in_time && ! $record->check_out_time) {
            $cutoff = $this->scheduledDateTime($record, $setting->checkout_cutoff_time);
            if ($now->greaterThan($cutoff)) {
                return 'missing_checkout';
            }

            return 'working';
        }

        return $record->status;
    }

    public function liveWorkingMinutes(?AttendanceRecord $record, ?CarbonImmutable $now = null): int
    {
        if (! $record?->check_in_time) {
            return 0;
        }

        $checkIn = $this->localDateTime($record->check_in_time);
        $endPoint = $record->check_out_time
            ? $this->localDateTime($record->check_out_time)
            : ($now ?? CarbonImmutable::now(self::TIMEZONE));

        return max(0, $checkIn->diffInMinutes($endPoint));
    }

    public function liveLateMinutes(?AttendanceRecord $record, AttendanceSetting $setting): int
    {
        if (! $record?->check_in_time) {
            return 0;
        }

        $checkIn = $this->localDateTime($record->check_in_time);
        $start = $this->scheduledDateTime($record, $setting->work_start_time);

        return max(0, $start->diffInMinutes($checkIn, false));
    }

    /** @return array{missing_checkout:int, absent:int} */
    public function finalizeDate(CarbonImmutable $date, ?AttendanceSetting $setting = null): array
    {
        $setting ??= $this->setting();
        $updated = 0;
        $absent = 0;

        if (! $this->isWorkingDay($date, $setting)) {
            return ['missing_checkout' => 0, 'absent' => 0];
        }

        Pekerja::query()
            ->where('staff_type', Pekerja::COOP_WORKER_STAFF_TYPE)
            ->where('status_aktif', true)
            ->get()
            ->each(function (Pekerja $worker) use ($date, $setting, &$updated, &$absent): void {
                $record = AttendanceRecord::query()->firstOrNew([
                    'staff_id' => $worker->id_pekerja,
                    'attendance_date' => $date->toDateString(),
                ]);

                if (! $record->exists) {
                    $record->fill([
                        'status' => 'absent',
                        'location_name' => $setting->location_name,
                        'notes' => 'Rekod tidak hadir dijana automatik oleh sistem.',
                    ]);
                    $record->save();
                    $absent++;

                    return;
                }

                if ($record->check_in_time && ! $record->check_out_time && $record->status !== 'missing_checkout') {
                    $record->status = 'missing_checkout';
                    $record->save();
                    $updated++;
                }
            });

        return ['missing_checkout' => $updated, 'absent' => $absent];
    }

    public function finalizeDueDates(?CarbonImmutable $now = null, ?AttendanceSetting $setting = null): void
    {
        $now ??= CarbonImmutable::now(self::TIMEZONE);
        $setting ??= $this->setting();
        $today = $now->startOfDay();

        $this->finalizeDate($today->subDay(), $setting);

        if ($this->isWorkingDay($today, $setting)) {
            $cutoff = CarbonImmutable::parse($today->toDateString().' '.$setting->checkout_cutoff_time, self::TIMEZONE);

            if ($now->greaterThanOrEqualTo($cutoff)) {
                $this->finalizeDate($today, $setting);
            }
        }
    }

    private function localDateTime(mixed $value): CarbonImmutable
    {
        return CarbonImmutable::parse($value)->timezone(self::TIMEZONE);
    }

    private function scheduledDateTime(AttendanceRecord $record, string $time): CarbonImmutable
    {
        return CarbonImmutable::parse($record->attendance_date->toDateString().' '.$time, self::TIMEZONE);
    }

    private function breakMinutes(AttendanceRecord $record, AttendanceSetting $setting): int
    {
        if (! $record->check_in_time || ! $record->check_out_time) {
            return 0;
        }

        $checkIn = $this->localDateTime($record->check_in_time);
        $checkOut = $this->localDateTime($record->check_out_time);
        $date = $record->attendance_date->toDateString();
        $breakStart = CarbonImmutable::parse($date.' '.($setting->break_start_time ?? '13:00:00'), self::TIMEZONE);
        $breakEnd = CarbonImmutable::parse($date.' '.($setting->break_end_time ?? '14:00:00'), self::TIMEZONE);
        $overlapStart = $checkIn->greaterThan($breakStart) ? $checkIn : $breakStart;
        $overlapEnd = $checkOut->lessThan($breakEnd) ? $checkOut : $breakEnd;

        return $overlapEnd->greaterThan($overlapStart) ? $overlapStart->diffInMinutes($overlapEnd) : 0;
    }

    private function applyAllowanceAmount(AttendanceRecord $record, AttendanceSetting $setting): void
    {
        $record->loadMissing('staff');
        $dailyRate = (float) ($record->staff?->kadar_elaun ?? 0);
        $fullDayMinutes = max(1, (int) ($setting->full_day_minutes ?? 480));
        $eligible = in_array($record->status, ['present', 'late', 'early_leave', 'half_day', 'outside_area'], true)
            && (int) ($record->total_minutes ?? 0) > 0;

        $record->daily_rate = $dailyRate;
        $record->allowance_amount = match (true) {
            ! $eligible => 0,
            $record->status === 'half_day' => round($dailyRate * (float) ($setting->half_day_rate_multiplier ?? 0.50), 2),
            default => round(((int) $record->total_minutes / $fullDayMinutes) * $dailyRate, 2),
        };
    }

    public function distanceMeters(float $latA, float $lonA, float $latB, float $lonB): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($latB - $latA);
        $lonDelta = deg2rad($lonB - $lonA);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($latA)) * cos(deg2rad($latB)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
