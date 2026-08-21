<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['owner_role', 'owner_id', 'uploaded_by_role', 'uploaded_by_id', 'documentable_type', 'documentable_id', 'category', 'application_purpose', 'original_name', 'stored_path', 'mime_type', 'size'])]
class DocumentUpload extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Ahli::class, 'owner_id', 'id_ahli');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class, 'owner_id', 'id_pekerja');
    }
}
