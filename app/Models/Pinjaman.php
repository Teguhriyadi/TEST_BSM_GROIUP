<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Pinjaman extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'pinjaman';

    protected $fillable = [
        'anggota_id',
        'cabang_id',
        'jenis_pinjaman_id',
        'nomor_pinjaman',
        'jumlah_pinjaman',
        'tenor',
        'bunga',
        'angsuran_per_bulan',
        'tujuan',
        'status',
        'tgl_pengajuan',
        'tgl_cair',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_pinjaman' => 'decimal:2',
            'bunga' => 'decimal:2',
            'angsuran_per_bulan' => 'decimal:2',
            'tgl_pengajuan' => 'date',
            'tgl_cair' => 'date',
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

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function jenisPinjaman(): BelongsTo
    {
        return $this->belongsTo(JenisPinjaman::class, 'jenis_pinjaman_id');
    }

    public function angsuran(): HasMany
    {
        return $this->hasMany(Angsuran::class, 'pinjaman_id');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(PinjamanDokumen::class, 'pinjaman_id')
            ->orderBy('created_at', 'asc');
    }

    public function getDokumenWajibAttribute(): Collection
    {
        return $this->dokumen->filter(function (PinjamanDokumen $d) {
            $wajibIds = $this->jenisPinjaman?->daftar_master_dokumen_id_wajib ?? [];
            return in_array((string) $d->master_dokumen_id, $wajibIds, true);
        })->values();
    }

    public function getDokumenWajibDisetujuiAttribute(): Collection
    {
        return $this->dokumen_wajib->filter(fn (PinjamanDokumen $d) => $d->status === 'disetujui')->values();
    }

    public function getJumlahDokumenWajibAttribute(): int
    {
        return $this->dokumen_wajib->count();
    }

    public function getJumlahDokumenWajibDisetujuiAttribute(): int
    {
        return $this->dokumen_wajib_disetujui->count();
    }

    public function getPersentaseProgressDokumenAttribute(): int
    {
        $total = $this->jumlah_dokumen_wajib;
        if ($total <= 0) {
            return 100;
        }
        return (int) min(100, max(0, (int) round(($this->jumlah_dokumen_wajib_disetujui / $total) * 100)));
    }

    public function getProgressLabelAttribute(): string
    {
        return "{$this->jumlah_dokumen_wajib_disetujui}/{$this->jumlah_dokumen_wajib} disetujui";
    }

    public function semuaDokumenWajibDisetujui(): bool
    {
        foreach ($this->dokumen_wajib as $d) {
            if ($d->status !== 'disetujui') {
                return false;
            }
        }
        return true;
    }

    public function dapatDiverifikasi(): bool
    {
        return $this->status === 'diajukan' && $this->semuaDokumenWajibDisetujui();
    }

    public function getHambatanVerifikasiAttribute(): array
    {
        $hambatan = [];
        foreach ($this->dokumen_wajib as $d) {
            $nama = $d->masterDokumen?->nama_dokumen ?? 'Dokumen';
            if ($d->status === 'belum_diunggah') {
                $hambatan[] = "{$nama} belum diunggah.";
            } elseif ($d->status === 'menunggu_verifikasi') {
                $hambatan[] = "{$nama} masih menunggu verifikasi.";
            } elseif ($d->status === 'ditolak') {
                $hambatan[] = "{$nama} ditolak.";
            } elseif ($d->status === 'perlu_diperbaiki') {
                $hambatan[] = "{$nama} perlu diperbaiki.";
            }
        }
        return $hambatan;
    }
}
