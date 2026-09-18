<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PinjamanDokumen extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'pinjaman_dokumen';

    protected $fillable = [
        'pinjaman_id',
        'master_dokumen_id',
        'uploader_users_id',
        'file_path',
        'nama_file_asli',
        'ukuran_file',
        'tipe_mime',
        'status',
        'catatan',
        'verifikator_users_id',
        'tgl_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'ukuran_file' => 'integer',
            'tgl_verifikasi' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::updating(function (self $model) {
            if (
                in_array($model->status, ['disetujui', 'ditolak', 'perlu_diperbaiki'], true)
                && ($model->isDirty('status') || $model->isDirty('catatan'))
                && empty($model->tgl_verifikasi)
            ) {
                $model->tgl_verifikasi = now();
            }
        });
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id');
    }

    public function masterDokumen(): BelongsTo
    {
        return $this->belongsTo(MasterDokumen::class, 'master_dokumen_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_users_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_users_id');
    }

    public function getUrlFileAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return neo_public_url($this->file_path, 1440);
    }

    public function getUkuranFileFormatAttribute(): string
    {
        $bytes = (int) $this->ukuran_file;
        if ($bytes === 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i] ?? 'B';
    }

    public function getWarnaStatusAttribute(): string
    {
        return match ($this->status) {
            'disetujui' => 'success',
            'ditolak' => 'danger',
            'perlu_diperbaiki' => 'warning',
            'menunggu_verifikasi' => 'info',
            default => 'secondary',
        };
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'perlu_diperbaiki' => 'Perlu Diperbaiki',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            default => 'Belum Diunggah',
        };
    }

    public function getDapatDiunggahUlangAttribute(): bool
    {
        return in_array($this->status, ['belum_diunggah', 'ditolak', 'perlu_diperbaiki', 'menunggu_verifikasi'], true);
    }
}
