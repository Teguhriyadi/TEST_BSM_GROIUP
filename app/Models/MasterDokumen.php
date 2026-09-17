<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MasterDokumen extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'master_dokumen';

    protected $fillable = [
        'kode_dokumen',
        'nama_dokumen',
        'deskripsi',
        'format_diperbolehkan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function jenisPinjaman(): BelongsToMany
    {
        return $this->belongsToMany(
            JenisPinjaman::class,
            'jenis_pinjaman_dokumen_persyaratan',
            'master_dokumen_id',
            'jenis_pinjaman_id',
        )->withPivot(['is_wajib', 'urutan'])
         ->withTimestamps();
    }

    public function pinjamanDokumen(): HasMany
    {
        return $this->hasMany(PinjamanDokumen::class, 'master_dokumen_id');
    }

    public function getFormatListArray(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', strtolower($this->format_diperbolehkan ?? '')))));
    }
}
