<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_pekerja', 'syer', 'tambahan_saham', 'yuran', 'tarikh_kemaskini'])]
class SahamStaff extends Model
{
    protected $table = 'saham_staff';

    protected $primaryKey = 'id_saham_staff';

    public $timestamps = false;

    public function pekerja(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class, 'id_pekerja', 'id_pekerja');
    }

    public function scopeEligibleStaff(Builder $query): Builder
    {
        return $query->whereHas('pekerja', fn (Builder $staffQuery) => $staffQuery->eligibleForShares());
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
