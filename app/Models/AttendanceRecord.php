<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'staff_id', 'attendance_date', 'check_in_time', 'check_out_time',
    'check_in_latitude', 'check_in_longitude', 'check_out_latitude', 'check_out_longitude',
    'check_in_gps_verified', 'check_out_gps_verified', 'late_minutes', 'early_leave_minutes',
    'working_minutes', 'status', 'location_name', 'device_information', 'notes',
])]
class AttendanceRecord extends Model
{
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_time' => 'datetime',
            'check_out_time' => 'datetime',
            'check_in_latitude' => 'decimal:8',
            'check_in_longitude' => 'decimal:8',
            'check_out_latitude' => 'decimal:8',
            'check_out_longitude' => 'decimal:8',
            'check_in_gps_verified' => 'boolean',
            'check_out_gps_verified' => 'boolean',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'working_minutes' => 'integer',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class, 'staff_id', 'id_pekerja');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class, 'attendance_id');
    }
}
