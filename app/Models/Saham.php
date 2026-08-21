<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_ahli', 'syer', 'tambahan_saham', 'yuran', 'tarikh_kemaskini'])]
class Saham extends Model
{
    protected $table = 'saham';

    protected $primaryKey = 'id_saham';

    public function getRouteKeyName(): string
    {
        return 'id_saham';
    }

    public $timestamps = false;

    public function ahli(): BelongsTo
    {
        return $this->belongsTo(Ahli::class, 'id_ahli', 'id_ahli');
    }

    protected function casts(): array
    {
        return [
            'syer' => 'decimal:2',
            'tambahan_saham' => 'decimal:2',
            'yuran' => 'decimal:2',
            'tarikh_kemaskini' => 'date',
        ];
    }
}
