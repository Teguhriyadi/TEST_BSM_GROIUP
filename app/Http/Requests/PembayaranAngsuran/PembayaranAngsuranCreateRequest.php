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
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'bukti_pembayaran_lama_hapus' => 'nullable|boolean',
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
            'bukti_pembayaran.mimes' => 'Bukti Pembayaran hanya bisa file JPG, JPEG, PNG, atau PDF.',
            'bukti_pembayaran.max' => 'Bukti Pembayaran maksimal ukuran 10 MB.',
            'bukti_pembayaran_lama_hapus.boolean' => 'Format hapus bukti lama tidak valid.',
            'dibayar_oleh.uuid' => 'Format data User (Dibayar Oleh) tidak valid.',
            'dibayar_oleh.exists' => 'User (Dibayar Oleh) yang dipilih tidak ditemukan.',
        ];
    }
}
