<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['filename', 'imported_count', 'failed_count', 'errors'])]
class AhliImport extends Model
{
    protected function casts(): array
    {
        return [
            'errors' => 'array',
        ];
    }
}
