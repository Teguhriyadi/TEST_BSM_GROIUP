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
        'cabang_id',
        'angsuran_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'jumlah_pokok',
        'jumlah_bunga',
        'jumlah_denda',
        'biaya_administrasi',
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
            'jumlah_pokok' => 'decimal:2',
            'jumlah_bunga' => 'decimal:2',
            'jumlah_denda' => 'decimal:2',
            'biaya_administrasi' => 'decimal:2',
        ];
    }

    public function getCabangIdAttribute($value): ?string
    {
        if ($value) {
            return (string) $value;
        }
        try {
            $cabang = $this->angsuran?->pinjaman?->cabang_id;
            return $cabang ? (string) $cabang : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function angsuran(): BelongsTo
    {
        return $this->belongsTo(Angsuran::class, 'angsuran_id');
    }

    public function dibayarOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibayar_oleh');
    }

    public function getUrlBuktiPembayaranAttribute(): ?string
    {
        if (! $this->bukti_pembayaran) {
            return null;
        }
        if (str_starts_with((string) $this->bukti_pembayaran, 'http://') || str_starts_with((string) $this->bukti_pembayaran, 'https://')) {
            return $this->bukti_pembayaran;
        }
        return \App\Helpers\ImageHelper::publicUrl($this->bukti_pembayaran);
    }
}
