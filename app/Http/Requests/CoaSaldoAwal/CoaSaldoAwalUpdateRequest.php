<?php

namespace App\Http\Requests\CoaSaldoAwal;

use Illuminate\Foundation\Http\FormRequest;

class CoaSaldoAwalUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode' => 'required|string|max:7|regex:/^\d{4}-\d{2}$/',
            'rows' => 'required|array|min:1',
            'rows.*.id' => 'nullable|string|max:50|exists:coa_saldo_awal,id',
            'rows.*.cabang_id' => 'required|string|max:50|exists:cabang,id',
            'rows.*.coa_id' => 'required|string|max:50|exists:coa,id',
            'rows.*.saldo_awal_debet' => 'required|numeric|min:0|max:999999999999.99',
            'rows.*.saldo_awal_kredit' => 'required|numeric|min:0|max:999999999999.99',
        ];
    }
}
