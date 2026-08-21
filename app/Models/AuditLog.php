<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['actor_role', 'actor_id', 'action', 'module', 'subject_type', 'subject_id', 'description', 'meta', 'ip_address', 'user_agent'])]
class AuditLog extends Model
{
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }
}
