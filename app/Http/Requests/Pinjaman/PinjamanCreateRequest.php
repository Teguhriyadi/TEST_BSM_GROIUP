<?php

declare(strict_types=1);

namespace App\Http\Requests\Pinjaman;

use Illuminate\Foundation\Http\FormRequest;

class PinjamanCreateRequest extends FormRequest
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
            'jenis_pinjaman_id' => 'required|uuid|exists:jenis_pinjaman,id',
            'nomor_pinjaman' => 'required|string|max:30|unique:pinjaman,nomor_pinjaman',
            'jumlah_pinjaman' => 'required|numeric|min:0',
            'tenor' => 'required|integer|min:1',
            'bunga' => 'required|numeric|min:0|max:100',
            'angsuran_per_bulan' => 'required|numeric|min:0',
            'tujuan' => 'nullable|string',
            'status' => 'required|in:diajukan,diverifikasi,disetujui,ditolak,dicairkan,berjalan,lunas,dibatalkan',
            'tgl_pengajuan' => 'nullable|date',
            'tgl_cair' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'anggota_id.required' => 'Anggota pemohon pinjaman wajib dipilih.',
            'anggota_id.uuid' => 'Format data Anggota tidak valid.',
            'anggota_id.exists' => 'Anggota yang dipilih tidak ditemukan.',
            'cabang_id.required' => 'Cabang pinjaman wajib dipilih.',
            'cabang_id.uuid' => 'Format data Cabang tidak valid.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak ditemukan.',
            'jenis_pinjaman_id.required' => 'Jenis Pinjaman wajib dipilih.',
            'jenis_pinjaman_id.uuid' => 'Format data Jenis Pinjaman tidak valid.',
            'jenis_pinjaman_id.exists' => 'Jenis Pinjaman yang dipilih tidak ditemukan.',
            'nomor_pinjaman.required' => 'Nomor Pinjaman wajib diisi.',
            'nomor_pinjaman.max' => 'Nomor Pinjaman maksimal 30 karakter.',
            'nomor_pinjaman.unique' => 'Nomor Pinjaman tersebut sudah terdaftar, gunakan nomor lain.',
            'jumlah_pinjaman.required' => 'Jumlah Pinjaman wajib diisi.',
            'jumlah_pinjaman.numeric' => 'Jumlah Pinjaman harus berupa angka (Rupiah).',
            'jumlah_pinjaman.min' => 'Jumlah Pinjaman tidak boleh kurang dari 0.',
            'tenor.required' => 'Tenor pinjaman wajib diisi.',
            'tenor.integer' => 'Tenor harus berupa bilangan bulat (bulan).',
            'tenor.min' => 'Tenor minimal 1 bulan.',
            'bunga.required' => 'Persen Bunga wajib diisi.',
            'bunga.numeric' => 'Bunga harus berupa angka (persen).',
            'bunga.min' => 'Bunga minimal 0%.',
            'bunga.max' => 'Bunga maksimal 100%.',
            'angsuran_per_bulan.required' => 'Angsuran Per Bulan wajib diisi.',
            'angsuran_per_bulan.numeric' => 'Angsuran Per Bulan harus berupa angka (Rupiah).',
            'angsuran_per_bulan.min' => 'Angsuran Per Bulan tidak boleh kurang dari 0.',
            'status.required' => 'Status Pinjaman wajib dipilih.',
            'status.in' => 'Status Pinjaman yang dipilih tidak valid.',
            'tgl_pengajuan.date' => 'Format Tanggal Pengajuan tidak valid.',
            'tgl_cair.date' => 'Format Tanggal Cair tidak valid.',
        ];
    }
}
