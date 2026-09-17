<?php

declare(strict_types=1);

namespace App\Http\Requests\Cabang;

use Illuminate\Foundation\Http\FormRequest;

class CabangCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_cabang' => 'required|string|max:50|unique:cabang,kode_cabang',
            'nama_cabang' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|string|max:30',
            'is_active' => 'required|in:1,0',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_cabang.required' => 'Kode Cabang wajib diisi.',
            'kode_cabang.max' => 'Kode Cabang maksimal 50 karakter.',
            'kode_cabang.unique' => 'Kode Cabang tersebut sudah digunakan, pilih kode lain.',
            'nama_cabang.required' => 'Nama Cabang wajib diisi.',
            'nama_cabang.max' => 'Nama Cabang maksimal 100 karakter.',
            'alamat.required' => 'Alamat Cabang wajib diisi.',
            'alamat.max' => 'Alamat Cabang maksimal 255 karakter.',
            'telepon.required' => 'Nomor Telepon wajib diisi.',
            'telepon.max' => 'Nomor Telepon maksimal 30 karakter.',
            'is_active.required' => 'Status Cabang wajib dipilih.',
            'is_active.in' => 'Status Cabang yang dipilih tidak valid.',
        ];
    }
}
