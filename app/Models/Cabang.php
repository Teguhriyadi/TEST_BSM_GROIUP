<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'cabang';

    protected $fillable = [
        'kode_cabang',
        'nama_cabang',
        'alamat',
        'telepon',
        'is_active',
    ];

    public function anggota(): HasMany
    {
        return $this->hasMany(Anggota::class, 'cabang_id');
    }

    public function simpanan(): HasMany
    {
        return $this->hasMany(Simpanan::class, 'cabang_id');
    }

    public function pinjaman(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'cabang_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'cabang_id');
    }
}
