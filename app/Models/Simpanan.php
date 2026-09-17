<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Simpanan extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'simpanan';

    protected $fillable = [
        'anggota_id',
        'cabang_id',
        'jenis_simpanan_id',
        'tanggal',
        'nominal',
        'saldo',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'decimal:2',
            'saldo' => 'decimal:2',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function jenisSimpanan(): BelongsTo
    {
        return $this->belongsTo(JenisSimpanan::class, 'jenis_simpanan_id');
    }
}
