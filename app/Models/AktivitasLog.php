<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AktivitasLog extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'aktivitas_log';

    protected $fillable = [
        'users_id',
        'cabang_id',
        'tipe_aktivitas',
        'model_type',
        'model_id',
        'deskripsi',
        'ip_address',
        'users_agent',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public static function record(
        string $tipeAktivitas,
        string $deskripsi,
        ?Model $model = null
    ): ?self {
        try {
            $user = auth()->user();
            $data = [
                'users_id'      => $user?->id,
                'cabang_id'     => $user?->cabang_id,
                'tipe_aktivitas' => $tipeAktivitas,
                'deskripsi'     => $deskripsi,
                'ip_address'    => request()?->ip(),
                'users_agent'   => request()?->userAgent(),
            ];
            if ($model !== null) {
                $data['model_type'] = get_class($model);
                $data['model_id']   = (string) $model->getKey();
            }
            return self::create($data);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}
