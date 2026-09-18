<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JurnalUmumHeader extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'jurnal_umum_header';

    protected $fillable = [
        'cabang_id',
        'dibuat_oleh',
        'diposting_oleh',
        'nomor_jurnal',
        'tipe',
        'status',
        'tanggal_jurnal',
        'ref_id',
        'ref_tipe',
        'keterangan',
        'total_debet',
        'total_kredit',
        'diposting_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_jurnal' => 'date',
            'diposting_at' => 'datetime',
            'total_debet' => 'decimal:2',
            'total_kredit' => 'decimal:2',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function dipostingOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diposting_oleh');
    }

    public function details(): HasMany
    {
        return $this->hasMany(JurnalUmumDetail::class, 'jurnal_umum_header_id')
            ->orderBy('id', 'asc');
    }

    public function getIsBalanceAttribute(): bool
    {
        $tol = 0.001;
        return abs((float) $this->total_debet - (float) $this->total_kredit) < $tol;
    }

    public static function generateNomorJurnal(string $cabangId, ?Carbon $tanggal = null, int $maksimalPercobaan = 5): string
    {
        $cabang = Cabang::find($cabangId);
        $kodeCabang = $cabang?->kode_cabang ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $cabang->kode_cabang)) : 'CAB';
        $tgl = $tanggal ?: Carbon::now();
        $periode = $tgl->format('Ym');
        $prefix = "{$kodeCabang}-JV-{$periode}-";
        for ($i = 1; $i <= $maksimalPercobaan; $i++) {
            $nomorUrutTerakhir = (int) DB::table('jurnal_umum_header')
                ->where('nomor_jurnal', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->max(DB::raw('CAST(SUBSTRING(nomor_jurnal, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)'));
            $nomorUrut = (string) ($nomorUrutTerakhir + $i);
            $nomor = $prefix . str_pad($nomorUrut, 4, '0', STR_PAD_LEFT);
            $exists = DB::table('jurnal_umum_header')->where('nomor_jurnal', $nomor)->exists();
            if (! $exists) {
                return $nomor;
            }
        }
        return $prefix . strtoupper(substr(Str::random(6), 0, 4));
    }

    public function posting(?string $userId = null): void
    {
        if ($this->status === 'diposting') {
            return;
        }
        if (! $this->is_balance) {
            throw new \RuntimeException('Jurnal tidak balance, tidak bisa diposting.');
        }
        $userId = $userId ?: (Auth::check() ? (string) Auth::id() : null);
        $this->forceFill([
            'status' => 'diposting',
            'diposting_oleh' => $userId,
            'diposting_at' => Carbon::now(),
        ])->save();
    }

    public function recalculateTotals(): void
    {
        $debet = (float) $this->details()->sum('debet');
        $kredit = (float) $this->details()->sum('kredit');
        $this->forceFill([
            'total_debet' => round($debet, 2),
            'total_kredit' => round($kredit, 2),
        ])->save();
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->nomor_jurnal) || trim((string) $model->nomor_jurnal) === '') {
                $tgl = $model->tanggal_jurnal ? Carbon::parse($model->tanggal_jurnal) : null;
                $model->nomor_jurnal = self::generateNomorJurnal((string) $model->cabang_id, $tgl);
            }
        });
    }
}
