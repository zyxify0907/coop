<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_item', 'quantity', 'harga'])]
class Stok extends Model
{
    protected $table = 'stok';

    protected $primaryKey = 'item_id';

    public function getRouteKeyName(): string
    {
        return 'item_id';
    }

    public function tempahan(): HasMany
    {
        return $this->hasMany(Tempahan::class, 'item_id', 'item_id');
    }

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
        ];
    }
}
