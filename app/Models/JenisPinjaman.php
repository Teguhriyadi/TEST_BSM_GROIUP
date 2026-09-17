<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JenisPinjaman extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'jenis_pinjaman';

    protected $fillable = [
        'nama_jenis',
        'keterangan',
        'maksimal_plafon',
        'bunga_tahunan',
        'tenor_minimal',
        'tenor_maksimal',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function pinjaman(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'jenis_pinjaman_id');
    }

    public function dokumenPersyaratan(): BelongsToMany
    {
        return $this->belongsToMany(
            MasterDokumen::class,
            'jenis_pinjaman_dokumen_persyaratan',
            'jenis_pinjaman_id',
            'master_dokumen_id',
        )->withPivot(['is_wajib', 'urutan'])
         ->withTimestamps()
         ->orderByPivot('urutan', 'asc')
         ->orderBy('master_dokumen.nama_dokumen', 'asc');
    }

    public function dokumenPersyaratanWajib(): BelongsToMany
    {
        return $this->dokumenPersyaratan()->wherePivot('is_wajib', true);
    }

    public function getDaftarMasterDokumenIdWajibAttribute(): array
    {
        return $this->dokumenPersyaratanWajib()
            ->pluck('master_dokumen.id')
            ->map(fn ($v) => (string) $v)
            ->values()
            ->all();
    }
}
