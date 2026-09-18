<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalUmumDetail extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'jurnal_umum_detail';

    protected $fillable = [
        'jurnal_umum_header_id',
        'coa_id',
        'keterangan',
        'debet',
        'kredit',
    ];

    protected function casts(): array
    {
        return [
            'debet' => 'decimal:2',
            'kredit' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(JurnalUmumHeader::class, 'jurnal_umum_header_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }
}
