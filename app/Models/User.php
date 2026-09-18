<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'users';

    protected $fillable = [
        'cabang_id',
        'nama',
        'email',
        'password',
        'role_id',
        'nomor_hp',
        'is_active',
        'force_change_password',
        'password_changed_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'force_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hasRole(string $namaRole): bool
    {
        return (bool) ($this->role && strcasecmp($this->role->nama_role, $namaRole) === 0);
    }

    public function hasPermission(string $kodePermission): bool
    {
        if ($this->hasRole('Administrator')) {
            return true;
        }

        if (! $this->relationLoaded('role') || ! $this->role) {
            return false;
        }

        if (! $this->role->relationLoaded('permissions')) {
            $this->role->load('permissions');
        }

        return $this->role->permissions->contains('kode_permission', $kodePermission);
    }
}
