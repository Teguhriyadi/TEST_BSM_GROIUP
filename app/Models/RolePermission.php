<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'role_permission';

    protected $fillable = [
        'role_id',
        'permission_id',
    ];
}
