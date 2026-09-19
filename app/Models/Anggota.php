<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'anggota';

    protected $fillable = [
        'cabang_id',
        'users_id',
        'no_anggota',
        'kategori_anggota',
        'nik',
        'nama',
        'jenis_kelamin',
        'alamat',
        'tgl_lahir',
        'no_hp',
        'status_anggota',
        'status',
        'status_pendaftaran',
        'tgl_verifikasi_pendaftaran',
        'catatan_verifikasi_pendaftaran',
        'verifikator_users_id',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
            'status_anggota' => 'date',
            'tgl_verifikasi_pendaftaran' => 'datetime',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_users_id');
    }

    public function simpanan(): HasMany
    {
        return $this->hasMany(Simpanan::class, 'anggota_id');
    }

    public function pinjaman(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'anggota_id');
    }
}
