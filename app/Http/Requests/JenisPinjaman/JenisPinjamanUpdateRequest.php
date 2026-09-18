<?php

declare(strict_types=1);

namespace App\Http\Requests\JenisPinjaman;

use Illuminate\Foundation\Http\FormRequest;

class JenisPinjamanUpdateRequest extends FormRequest
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
            'maksimal_plafon' => 'nullable|numeric|min:0',
            'bunga_tahunan' => 'required|numeric|min:0|max:100',
            'tenor_minimal' => 'nullable|numeric|min:0',
            'tenor_maksimal' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_jenis.required' => 'Nama Jenis Pinjaman wajib diisi.',
            'nama_jenis.max' => 'Nama Jenis Pinjaman maksimal 100 karakter.',
            'maksimal_plafon.numeric' => 'Maksimal Plafon harus berupa angka (Rupiah).',
            'maksimal_plafon.min' => 'Maksimal Plafon tidak boleh kurang dari 0.',
            'bunga_tahunan.required' => 'Bunga Tahunan wajib diisi.',
            'bunga_tahunan.numeric' => 'Bunga Tahunan harus berupa angka (persen).',
            'bunga_tahunan.min' => 'Bunga Tahunan minimal 0%.',
            'bunga_tahunan.max' => 'Bunga Tahunan maksimal 100%.',
            'tenor_minimal.numeric' => 'Tenor Minimal harus berupa angka (bulan).',
            'tenor_minimal.min' => 'Tenor Minimal tidak boleh kurang dari 0.',
            'tenor_maksimal.integer' => 'Tenor Maksimal harus berupa bilangan bulat (bulan).',
            'tenor_maksimal.min' => 'Tenor Maksimal tidak boleh kurang dari 0.',
        ];
    }
}
