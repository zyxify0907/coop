<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_ahli',
    'jenis',
    'nama_pemohon',
    'no_matrik',
    'email',
    'no_tel',
    'status',
    'data_permohonan',
    'catatan_pelajar',
    'catatan_admin',
    'tarikh_permohonan',
    'tarikh_keputusan',
])]
class Permohonan extends Model
{
    protected $table = 'permohonan';

    protected $primaryKey = 'id_permohonan';

    public function getRouteKeyName(): string
    {
        return 'id_permohonan';
    }

    public function ahli(): BelongsTo
    {
        return $this->belongsTo(Ahli::class, 'id_ahli', 'id_ahli');
    }

    protected function casts(): array
    {
        return [
            'data_permohonan' => 'array',
            'tarikh_permohonan' => 'date',
            'tarikh_keputusan' => 'date',
        ];
    }
}
