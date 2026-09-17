<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranAngsuran extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'pembayaran_angsuran';

    protected $fillable = [
        'angsuran_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_pembayaran',
        'bukti_pembayaran',
        'dibayar_oleh',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bayar' => 'date',
            'jumlah_bayar' => 'decimal:2',
        ];
    }

    public function angsuran(): BelongsTo
    {
        return $this->belongsTo(Angsuran::class, 'angsuran_id');
    }

    public function dibayarOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibayar_oleh');
    }
}
