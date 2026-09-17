<?php

declare(strict_types=1);

namespace App\Http\Requests\PembayaranAngsuran;

use Illuminate\Foundation\Http\FormRequest;

class PembayaranAngsuranCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'angsuran_id' => 'required|uuid|exists:angsuran,id',
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:tunai,transfer,lainnya',
            'bukti_pembayaran' => 'nullable|string|max:255',
            'dibayar_oleh' => 'nullable|uuid|exists:users,id',
            'keterangan' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'angsuran_id.required' => 'Data Angsuran yang dibayar wajib dipilih.',
            'angsuran_id.uuid' => 'Format data Angsuran tidak valid.',
            'angsuran_id.exists' => 'Angsuran yang dipilih tidak ditemukan di database.',
            'tanggal_bayar.required' => 'Tanggal Bayar wajib diisi.',
            'tanggal_bayar.date' => 'Format Tanggal Bayar tidak valid, pilih dari tanggalan.',
            'jumlah_bayar.required' => 'Jumlah Bayar wajib diisi.',
            'jumlah_bayar.numeric' => 'Jumlah Bayar harus berupa angka (Rupiah).',
            'jumlah_bayar.min' => 'Jumlah Bayar tidak boleh kurang dari 0.',
            'metode_pembayaran.required' => 'Metode Pembayaran wajib dipilih.',
            'metode_pembayaran.in' => 'Metode Pembayaran harus Tunai, Transfer, atau Lainnya.',
            'bukti_pembayaran.max' => 'Nama / link Bukti Pembayaran maksimal 255 karakter.',
            'dibayar_oleh.uuid' => 'Format data User (Dibayar Oleh) tidak valid.',
            'dibayar_oleh.exists' => 'User (Dibayar Oleh) yang dipilih tidak ditemukan.',
        ];
    }
}
