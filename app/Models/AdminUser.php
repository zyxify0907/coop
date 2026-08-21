<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama', 'username', 'nric', 'password_hash', 'peranan', 'status_aktif'])]
class AdminUser extends Model
{
    protected $table = 'admin';

    protected $primaryKey = 'id_admin';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }
}
