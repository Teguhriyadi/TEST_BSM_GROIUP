<?php

declare(strict_types=1);

namespace App\Http\Requests\JenisSimpanan;

use Illuminate\Foundation\Http\FormRequest;

class JenisSimpananCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_jenis' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
            'setoran_minimal' => 'required|numeric|min:0',
            'setoran_maksimal' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_jenis.required' => 'Nama Jenis Simpanan wajib diisi.',
            'nama_jenis.max' => 'Nama Jenis Simpanan maksimal 100 karakter.',
            'setoran_minimal.required' => 'Setoran Minimal wajib diisi.',
            'setoran_minimal.numeric' => 'Setoran Minimal harus berupa angka (Rupiah).',
            'setoran_minimal.min' => 'Setoran Minimal tidak boleh kurang dari 0.',
            'setoran_maksimal.numeric' => 'Setoran Maksimal harus berupa angka (Rupiah).',
            'setoran_maksimal.min' => 'Setoran Maksimal tidak boleh kurang dari 0.',
        ];
    }
}
