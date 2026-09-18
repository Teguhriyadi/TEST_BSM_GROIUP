<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoaMapping extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'coa_mapping';

    protected $fillable = [
        'cabang_id',
        'tipe_transaksi',
        'posisi',
        'coa_id',
        'keterangan',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }
}
