<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_id', 'jumlah_jualan', 'komisen', 'bayaran_akhir'])]
class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $primaryKey = 'payment_id';

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'vendor_id');
    }

    protected function casts(): array
    {
        return [
            'jumlah_jualan' => 'decimal:2',
            'komisen' => 'decimal:2',
            'bayaran_akhir' => 'decimal:2',
        ];
    }
}
