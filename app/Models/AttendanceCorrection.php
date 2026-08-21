<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attendance_id', 'staff_id', 'correction_date', 'correction_type', 'requested_time',
    'reason', 'supporting_document', 'status', 'reviewed_by', 'reviewed_at', 'admin_remark',
])]
class AttendanceCorrection extends Model
{
    protected function casts(): array
    {
        return [
            'correction_date' => 'date',
            'requested_time' => 'datetime:H:i',
            'reviewed_at' => 'datetime',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class, 'staff_id', 'id_pekerja');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'reviewed_by', 'id_admin');
    }
}
