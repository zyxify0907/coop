<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['recipient_role', 'recipient_id', 'title', 'message', 'link', 'read_at'])]
class CooperativeNotification extends Model
{
    protected $table = 'notifications';

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
