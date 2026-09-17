<?php

declare(strict_types=1);

namespace App\Http\Requests\Angsuran;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AngsuranUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $angsuranId = $this->route('angsuran')?->id ?? $this->route('angsuran');

        return [
            'pinjaman_id' => 'required|uuid|exists:pinjaman,id',
            'angsuran_ke' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('angsuran', 'angsuran_ke')->where(function ($query) use ($angsuranId) {
                    return $query->where('pinjaman_id', $this->pinjaman_id)
                        ->where('id', '!=', $angsuranId);
                }),
            ],
            'tanggal_jatuh_tempo' => 'required|date',
            'nominal' => 'required|numeric|min:0',
            'denda' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0',
            'status' => 'required|in:belum_lunas,lunas,telat',
        ];
    }

    public function messages(): array
    {
        return [
            'pinjaman_id.required' => 'Data Pinjaman wajib dipilih.',
            'pinjaman_id.uuid' => 'Format data Pinjaman tidak valid.',
            'pinjaman_id.exists' => 'Pinjaman yang dipilih tidak ditemukan di database.',
            'angsuran_ke.required' => 'Nomor urut Angsuran Ke wajib diisi.',
            'angsuran_ke.integer' => 'Angsuran Ke harus berupa bilangan bulat.',
            'angsuran_ke.min' => 'Angsuran Ke minimal 1.',
            'angsuran_ke.unique' => 'Nomor Angsuran Ke tersebut sudah ada untuk pinjaman ini, gunakan nomor lain.',
            'tanggal_jatuh_tempo.required' => 'Tanggal Jatuh Tempo wajib diisi.',
            'tanggal_jatuh_tempo.date' => 'Format Tanggal Jatuh Tempo tidak valid.',
            'nominal.required' => 'Nominal angsuran pokok + bunga wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka (Rupiah).',
            'nominal.min' => 'Nominal tidak boleh kurang dari 0.',
            'denda.required' => 'Denda wajib diisi (isi 0 jika tidak ada denda).',
            'denda.numeric' => 'Denda harus berupa angka (Rupiah).',
            'denda.min' => 'Denda tidak boleh kurang dari 0.',
            'total_bayar.required' => 'Total Bayar wajib diisi.',
            'total_bayar.numeric' => 'Total Bayar harus berupa angka (Rupiah).',
            'total_bayar.min' => 'Total Bayar tidak boleh kurang dari 0.',
            'status.required' => 'Status Angsuran wajib dipilih.',
            'status.in' => 'Status Angsuran harus Belum Lunas, Lunas, atau Telat.',
        ];
    }
}
