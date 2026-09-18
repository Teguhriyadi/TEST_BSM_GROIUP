<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coa extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'coa';

    protected $fillable = [
        'parent_id',
        'cabang_id',
        'kode_akun',
        'nama_akun',
        'level',
        'kelompok',
        'posisi_laporan',
        'saldo_normal',
        'is_active',
        'keterangan',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('kode_akun', 'asc');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function coaMapping(): HasMany
    {
        return $this->hasMany(CoaMapping::class, 'coa_id');
    }

    public function saldoAwal(): HasMany
    {
        return $this->hasMany(CoaSaldoAwal::class, 'coa_id');
    }

    public function jurnalDetail(): HasMany
    {
        return $this->hasMany(JurnalUmumDetail::class, 'coa_id');
    }

    public function getFullLabelAttribute(): string
    {
        return "{$this->kode_akun} - {$this->nama_akun}";
    }
}
