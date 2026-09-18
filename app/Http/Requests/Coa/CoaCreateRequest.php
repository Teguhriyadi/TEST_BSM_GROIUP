<?php

namespace App\Http\Requests\Coa;

use Illuminate\Foundation\Http\FormRequest;

class CoaCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|string|max:50|exists:coa,id',
            'cabang_id' => 'nullable|string|max:50|exists:cabang,id',
            'kode_akun' => 'required|string|max:50',
            'nama_akun' => 'required|string|max:150',
            'level' => 'required|integer|min:1|max:9',
            'kelompok' => 'required|string|in:aset,kewajiban,ekuitas,pendapatan,beban,ikhtisar_laba_rugi',
            'posisi_laporan' => 'required|string|in:neraca,laba_rugi',
            'saldo_normal' => 'required|string|in:debet,kredit',
            'is_active' => 'nullable|in:1,0',
            'keterangan' => 'nullable|string|max:1000',
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_akun' => 'Kode Akun',
            'nama_akun' => 'Nama Akun',
            'kelompok' => 'Kelompok',
            'posisi_laporan' => 'Posisi Laporan',
            'saldo_normal' => 'Saldo Normal',
            'level' => 'Level',
        ];
    }
}
