<?php

namespace App\Http\Requests\JurnalUmum;

use Illuminate\Foundation\Http\FormRequest;

class JurnalUmumCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_jurnal' => 'required|date',
            'keterangan' => 'nullable|string|max:65000',
            'details' => 'required|array|min:2',
            'details.*.coa_id' => 'required|string|max:50|exists:coa,id',
            'details.*.keterangan' => 'nullable|string|max:255',
            'details.*.debet' => 'required|numeric|min:0|max:999999999999.99',
            'details.*.kredit' => 'required|numeric|min:0|max:999999999999.99',
        ];
    }

    public function attributes(): array
    {
        return [
            'tanggal_jurnal' => 'Tanggal Jurnal',
            'details' => 'Baris Detail Jurnal',
            'details.*.coa_id' => 'Akun',
            'details.*.debet' => 'Debet',
            'details.*.kredit' => 'Kredit',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $details = $this->input('details', []);
            $debet = 0;
            $kredit = 0;
            foreach ($details as $r) {
                $debet += (float) ($r['debet'] ?? 0);
                $kredit += (float) ($r['kredit'] ?? 0);
            }
            if (abs($debet - $kredit) > 0.001) {
                $v->errors()->add('details', 'Total debet tidak sama dengan total kredit. Jurnal harus balance.');
            }
            if ($debet <= 0) {
                $v->errors()->add('details', 'Total nominal debet harus lebih dari nol.');
            }
        });
    }
}
