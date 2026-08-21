<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['member_type', 'member_id', 'transaction_type', 'direction', 'amount', 'balance_after', 'reference_type', 'reference_id', 'processed_by_role', 'processed_by_id', 'notes', 'transacted_at'])]
class ShareTransaction extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Ahli::class, 'member_id', 'id_ahli');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class, 'member_id', 'id_pekerja');
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'transacted_at' => 'date',
        ];
    }
}
