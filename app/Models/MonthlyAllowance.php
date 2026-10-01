<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'staff_id', 'month', 'year', 'present_days', 'full_days', 'half_days',
    'late_minutes', 'total_minutes', 'daily_rate', 'total_allowance', 'status',
    'generated_by', 'generated_by_role', 'approved_at', 'approved_by', 'paid_at',
    'paid_by', 'notes',
])]
class MonthlyAllowance extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_COMPLETED = 'completed';

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'present_days' => 'integer',
            'full_days' => 'integer',
            'half_days' => 'integer',
            'late_minutes' => 'integer',
            'total_minutes' => 'integer',
            'daily_rate' => 'decimal:2',
            'total_allowance' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class, 'staff_id', 'id_pekerja');
    }
}
