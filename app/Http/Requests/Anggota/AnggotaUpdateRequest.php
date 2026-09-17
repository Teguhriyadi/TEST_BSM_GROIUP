<?php

declare(strict_types=1);

namespace App\Http\Requests\Anggota;

use Illuminate\Foundation\Http\FormRequest;

class AnggotaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $anggotaId = $this->route('anggota')?->id ?? $this->route('anggota');
        $anggota = \App\Models\Anggota::find($anggotaId);
        $userIdIgnore = $anggota?->users_id;

        return [
            'cabang_id' => 'required|uuid|exists:cabang,id',
            'no_anggota' => 'required|string|max:30|unique:anggota,no_anggota,' . $anggotaId,
            'nik' => 'nullable|string|max:16',
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable|string',
            'tgl_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100|unique:users,email' . ($userIdIgnore ? ',' . $userIdIgnore : ''),
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
            'no_anggota.required' => 'Nomor Anggota wajib diisi.',
            'no_anggota.max' => 'Nomor Anggota maksimal 30 karakter.',
            'no_anggota.unique' => 'Nomor Anggota tersebut sudah terdaftar, gunakan nomor lain.',
            'nik.max' => 'NIK maksimal 16 digit.',
            'nama.required' => 'Nama Anggota wajib diisi.',
            'nama.max' => 'Nama Anggota maksimal 100 karakter.',
            'jenis_kelamin.required' => 'Jenis Kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis Kelamin harus L (Laki-laki) atau P (Perempuan).',
            'tgl_lahir.date' => 'Format Tanggal Lahir tidak valid, pilih dari tanggalan.',
            'no_hp.max' => 'Nomor HP maksimal 15 digit.',
            'email.email' => 'Format Email anggota tidak valid.',
            'email.max' => 'Email anggota maksimal 100 karakter.',
            'email.unique' => 'Email tersebut sudah terdaftar sebagai user sistem. Gunakan email lain.',
            'status_anggota.date' => 'Format Tanggal Masuk Anggota tidak valid.',
            'status.required' => 'Status Anggota wajib dipilih.',
            'status.in' => 'Status Anggota harus Aktif atau Nonaktif.',
        ];
    }
}
