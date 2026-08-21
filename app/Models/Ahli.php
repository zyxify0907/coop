<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['no_anggota', 'no_matrik', 'nama', 'nric', 'password_hash', 'semester', 'program', 'kelas', 'no_tel', 'email', 'baki_ewallet', 'tarikh_daftar', 'status_aktif'])]
class Ahli extends Model
{
    protected $table = 'ahli';

    protected $primaryKey = 'id_ahli';

    public $timestamps = false;

    public function saham(): HasOne
    {
        return $this->hasOne(Saham::class, 'id_ahli', 'id_ahli');
    }

    protected function casts(): array
    {
        return [
            'baki_ewallet' => 'decimal:2',
            'tarikh_daftar' => 'date',
            'status_aktif' => 'boolean',
        ];
    }
}
