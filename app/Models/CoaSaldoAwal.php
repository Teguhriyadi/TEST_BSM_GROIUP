<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoaSaldoAwal extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'coa_saldo_awal';

    protected $fillable = [
        'cabang_id',
        'coa_id',
        'periode',
        'saldo_awal_debet',
        'saldo_awal_kredit',
    ];

    protected function casts(): array
    {
        return [
            'saldo_awal_debet' => 'decimal:2',
            'saldo_awal_kredit' => 'decimal:2',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }
}
