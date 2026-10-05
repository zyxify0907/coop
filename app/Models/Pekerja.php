<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['no_anggota', 'no_pekerja', 'nama', 'nric', 'password_hash', 'jawatan', 'staff_type', 'no_tel', 'email', 'kadar_elaun', 'tarikh_mula', 'status_aktif', 'profile_image_path'])]
class Pekerja extends Model
{
    public const SHAREHOLDER_STAFF_TYPES = [
        'lecturer_member',
        'clothing_staff',
        self::SHARE_MANAGER_STAFF_TYPE,
        self::COOP_MANAGER_STAFF_TYPE,
    ];

    public const COOP_WORKER_STAFF_TYPE = 'coop_staff';

    public const SHARE_MANAGER_STAFF_TYPE = 'share_staff';

    public const COOP_MANAGER_STAFF_TYPE = 'coop_manager';

    public const STAFF_TYPES = [
        'lecturer_member',
        self::COOP_WORKER_STAFF_TYPE,
        'clothing_staff',
        self::SHARE_MANAGER_STAFF_TYPE,
        self::COOP_MANAGER_STAFF_TYPE,
    ];

    protected $table = 'pekerja';

    protected $primaryKey = 'id_pekerja';

    public $timestamps = false;

    public function sahamStaff(): HasOne
    {
        return $this->hasOne(SahamStaff::class, 'id_pekerja', 'id_pekerja');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'staff_id', 'id_pekerja');
    }

    public function monthlyAllowances(): HasMany
    {
        return $this->hasMany(MonthlyAllowance::class, 'staff_id', 'id_pekerja');
    }

    public function scopeEligibleForShares(Builder $query): Builder
    {
        return $query->whereIn('staff_type', self::SHAREHOLDER_STAFF_TYPES);
    }

    public function scopeCoopWorkers(Builder $query): Builder
    {
        return $query->where('staff_type', self::COOP_WORKER_STAFF_TYPE);
    }

    public function isEligibleForShares(): bool
    {
        return in_array($this->staff_type, self::SHAREHOLDER_STAFF_TYPES, true);
    }

    public function getStaffTypeLabelAttribute(): string
    {
        if (filled($this->nric) && AdminUser::query()
            ->where('nric', $this->nric)
            ->where('status_aktif', true)
            ->exists()) {
            return 'Admin Pengurusan Sistem';
        }

        return match ($this->staff_type) {
            'lecturer_member' => 'Pensyarah / Staf Akademik',
            'clothing_staff' => 'Staff Pengurus Baju',
            self::SHARE_MANAGER_STAFF_TYPE => 'Staff Pengurus Saham',
            self::COOP_MANAGER_STAFF_TYPE => 'Staff Pengurus Pekerja Koperasi',
            default => 'Pekerja Koperasi',
        };
    }

    protected function casts(): array
    {
        return [
            'kadar_elaun' => 'decimal:2',
            'tarikh_mula' => 'date',
            'status_aktif' => 'boolean',
        ];
    }
}
