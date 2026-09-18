<?php

namespace App\Http\Requests\CoaMapping;

use Illuminate\Foundation\Http\FormRequest;

class CoaMappingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mapping' => 'required|array|min:1',
            'mapping.*.id' => 'nullable|string|max:50|exists:coa_mapping,id',
            'mapping.*.cabang_id' => 'nullable|string|max:50|exists:cabang,id',
            'mapping.*.tipe_transaksi' => 'required|string|in:simpanan_setoran,simpanan_penarikan,pinjaman_cair,angsuran_pokok,angsuran_bunga,biaya_administrasi,denda_tunggakan',
            'mapping.*.posisi' => 'required|string|in:debet,kredit',
            'mapping.*.coa_id' => 'required|string|max:50|exists:coa,id',
            'mapping.*.keterangan' => 'nullable|string|max:65000',
        ];
    }
}
