<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'work_start_time', 'work_end_time', 'break_start_time', 'break_end_time',
    'grace_period_minutes', 'full_day_minutes', 'half_day_rate_multiplier', 'checkout_cutoff_time',
    'working_days', 'latitude', 'longitude', 'allowed_radius_meter', 'location_name', 'status',
])]
class AttendanceSetting extends Model
{
    protected function casts(): array
    {
        return [
            'grace_period_minutes' => 'integer',
            'full_day_minutes' => 'integer',
            'half_day_rate_multiplier' => 'decimal:2',
            'working_days' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'allowed_radius_meter' => 'integer',
            'status' => 'boolean',
        ];
    }
}
