<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['no_pekerja', 'nama', 'nric', 'password_hash', 'jawatan', 'staff_type', 'no_tel', 'email', 'kadar_elaun', 'tarikh_mula', 'status_aktif'])]
class Pekerja extends Model
{
    protected $table = 'pekerja';

    protected $primaryKey = 'id_pekerja';

    public $timestamps = false;

    public function sahamStaff(): HasOne
    {
        return $this->hasOne(SahamStaff::class, 'id_pekerja', 'id_pekerja');
    }

    public function getStaffTypeLabelAttribute(): string
    {
        return match ($this->staff_type) {
            'lecturer_member' => 'Pensyarah / Staf Akademik (Anggota)',
            'clothing_staff' => 'Staff Pengurusan Baju',
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
