<?php

declare(strict_types=1);

namespace App\Http\Requests\Simpanan;

use Illuminate\Foundation\Http\FormRequest;

class SimpananCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anggota_id' => 'required|uuid|exists:anggota,id',
            'cabang_id' => 'required|uuid|exists:cabang,id',
            'jenis_simpanan_id' => 'required|uuid|exists:jenis_simpanan,id',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric|min:0',
            'saldo' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'anggota_id.required' => 'Anggota pemilik simpanan wajib dipilih.',
            'anggota_id.uuid' => 'Format data Anggota tidak valid.',
            'anggota_id.exists' => 'Anggota yang dipilih tidak ditemukan di database.',
            'cabang_id.required' => 'Cabang simpanan wajib dipilih.',
            'cabang_id.uuid' => 'Format data Cabang tidak valid.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak ditemukan di database.',
            'jenis_simpanan_id.required' => 'Jenis Simpanan wajib dipilih.',
            'jenis_simpanan_id.uuid' => 'Format data Jenis Simpanan tidak valid.',
            'jenis_simpanan_id.exists' => 'Jenis Simpanan yang dipilih tidak ditemukan.',
            'tanggal.required' => 'Tanggal transaksi simpanan wajib diisi.',
            'tanggal.date' => 'Format Tanggal tidak valid, pilih dari tanggalan.',
            'nominal.required' => 'Nominal simpanan wajib diisi.',
            'nominal.numeric' => 'Nominal simpanan harus berupa angka (Rupiah).',
            'nominal.min' => 'Nominal simpanan tidak boleh kurang dari 0.',
            'saldo.required' => 'Saldo akhir simpanan wajib diisi.',
            'saldo.numeric' => 'Saldo simpanan harus berupa angka (Rupiah).',
            'saldo.min' => 'Saldo simpanan tidak boleh kurang dari 0.',
        ];
    }
}
