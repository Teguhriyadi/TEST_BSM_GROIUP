<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class JenisPinjamanDokumenPersyaratan extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'jenis_pinjaman_dokumen_persyaratan';

    protected $fillable = [
        'jenis_pinjaman_id',
        'master_dokumen_id',
        'is_wajib',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_wajib' => 'boolean',
            'urutan' => 'integer',
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

    public function jenisPinjaman(): BelongsTo
    {
        return $this->belongsTo(JenisPinjaman::class, 'jenis_pinjaman_id');
    }

    public function masterDokumen(): BelongsTo
    {
        return $this->belongsTo(MasterDokumen::class, 'master_dokumen_id');
    }
}
