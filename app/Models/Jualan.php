<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['item_id', 'quantity', 'jumlah', 'tarikh'])]
class Jualan extends Model
{
    protected $table = 'jualan';

    protected $primaryKey = 'jualan_id';

    public function stok(): BelongsTo
    {
        return $this->belongsTo(Stok::class, 'item_id', 'item_id');
    }

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tarikh' => 'date',
        ];
    }
}
