<?php

declare(strict_types=1);

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class KaryawanCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cabang_id' => 'required|uuid|exists:cabang,id',
            'nik' => 'required|string|max:16',
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable|string',
            'tgl_lahir' => 'nullable|date',
            'no_hp' => 'required|string|max:15',
            'email' => 'required|email|max:100|unique:users,email',
            'status_anggota' => 'nullable|date',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    public function messages(): array
    {
        return [
            'cabang_id.required' => 'Cabang anggota wajib dipilih.',
            'cabang_id.uuid' => 'Format Cabang tidak valid.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak ditemukan di database.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.max' => 'NIK maksimal 16 digit.',
            'nama.required' => 'Nama Anggota wajib diisi.',
            'nama.max' => 'Nama Anggota maksimal 100 karakter.',
            'jenis_kelamin.required' => 'Jenis Kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis Kelamin harus L (Laki-laki) atau P (Perempuan).',
            'tgl_lahir.date' => 'Format Tanggal Lahir tidak valid, pilih dari tanggalan.',
            'no_hp.required' => 'Nomor HP Wajib Diisi',
            'no_hp.max' => 'Nomor HP maksimal 15 digit.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format Email anggota tidak valid.',
            'email.max' => 'Email anggota maksimal 100 karakter.',
            'email.unique' => 'Email tersebut sudah terdaftar sebagai user sistem. Gunakan email lain.',
            'status_anggota.date' => 'Format Tanggal Masuk Anggota tidak valid.',
            'status.required' => 'Status Anggota wajib dipilih.',
            'status.in' => 'Status Anggota harus Aktif atau Nonaktif.',
        ];
    }
}
