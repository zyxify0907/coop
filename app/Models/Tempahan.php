<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['no_matrik', 'item_id', 'item', 'quantity', 'status'])]
class Tempahan extends Model
{
    protected $table = 'tempahan';

    protected $primaryKey = 'tempahan_id';

    public function getRouteKeyName(): string
    {
        return 'tempahan_id';
    }

    public function ahli(): BelongsTo
    {
        return $this->belongsTo(Ahli::class, 'no_matrik', 'no_matrik');
    }

    public function stok(): BelongsTo
    {
        return $this->belongsTo(Stok::class, 'item_id', 'item_id');
    }
}
