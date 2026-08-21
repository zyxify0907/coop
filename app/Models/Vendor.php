<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_vendor', 'no_akaun', 'bank'])]
class Vendor extends Model
{
    protected $table = 'vendor';

    protected $primaryKey = 'vendor_id';

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'vendor_id', 'vendor_id');
    }
}
