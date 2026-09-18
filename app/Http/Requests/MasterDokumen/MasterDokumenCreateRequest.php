<?php

namespace App\Http\Requests\MasterDokumen;

use Illuminate\Foundation\Http\FormRequest;

class MasterDokumenCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_dokumen' => 'required|string|max:50|unique:master_dokumen,kode_dokumen',
            'nama_dokumen' => 'required|string|max:150',
            'deskripsi' => 'nullable|string|max:1000',
            'format_diperbolehkan' => 'nullable|string|max:200',
            'is_active' => 'nullable|in:1,0,true,false,on,off',
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_dokumen' => 'Kode Dokumen',
            'nama_dokumen' => 'Nama Dokumen',
            'format_diperbolehkan' => 'Format File Diperbolehkan',
            'is_active' => 'Status Aktif',
        ];
    }
}
