<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSimpanan extends Model
{
    use HasUuids;
    
    public $incrementing = false;
    protected $keyType = "string";

    protected $table = 'jenis_simpanan';

    protected $fillable = [
        'nama_jenis',
        'keterangan',
        'setoran_minimal',
        'setoran_maksimal',
    ];

    public function simpanan(): HasMany
    {
        return $this->hasMany(Simpanan::class, 'jenis_simpanan_id');
    }
}
