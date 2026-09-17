<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Angsuran extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'angsuran';

    protected $fillable = [
        'pinjaman_id',
        'angsuran_ke',
        'tanggal_jatuh_tempo',
        'nominal',
        'denda',
        'total_bayar',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_jatuh_tempo' => 'date',
            'nominal' => 'decimal:2',
            'denda' => 'decimal:2',
            'total_bayar' => 'decimal:2',
        ];
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id');
    }

    public function pembayaranAngsuran(): HasMany
    {
        return $this->hasMany(PembayaranAngsuran::class, 'angsuran_id');
    }
}
