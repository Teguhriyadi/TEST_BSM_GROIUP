<?php

namespace App\Http\Requests\Anggota;

use Illuminate\Foundation\Http\FormRequest;

class AnggotaVerifikasiPendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->hasRole('Administrator') ||
            auth()->user()->hasRole('Teller') ||
            auth()->user()->hasRole('Kepala Cabang')
        );
    }

    public function rules(): array
    {
        return [
            'keputusan' => [
                'bail',
                'required',
                'string',
                'in:disetujui,ditolak',
            ],
            'no_anggota_baru' => [
                'bail',
                'nullable',
                'string',
                'max:30',
                'unique:anggota,no_anggota',
                'regex:/^[A-Za-z0-9._\-]+$/',
                function ($attr, $val, $fail) {
                    if (request()->input('keputusan') === 'disetujui' && trim((string) $val) === '') {
                        $fail('Nomor Anggota wajib diisi jika keputusan disetujui.');
                    }
                },
            ],
            'catatan_verifikasi_pendaftaran' => [
                'bail',
                'required',
                'string',
                'min:8',
                'max:600',
            ],
            'password_default' => [
                'bail',
                'nullable',
                'string',
                function ($attr, $val, $fail) {
                    if (request()->input('keputusan') === 'disetujui') {
                        $v = (string) $val;
                        if (trim($v) === '') {
                            $fail('Kata sandi default untuk anggota wajib ditentukan.');
                        } elseif (strlen($v) < 6 || strlen($v) > 32) {
                            $fail('Kata sandi default 6-32 karakter.');
                        }
                    }
                },
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'keputusan' => 'keputusan verifikasi',
            'no_anggota_baru' => 'nomor anggota baru',
            'catatan_verifikasi_pendaftaran' => 'catatan verifikasi',
            'password_default' => 'kata sandi default anggota',
        ];
    }

    public function messages(): array
    {
        return [
            'keputusan.required' => 'Silakan pilih keputusan verifikasi (disetujui / ditolak).',
            'keputusan.in' => 'Keputusan tidak valid.',
            'no_anggota_baru.max' => 'Nomor anggota maksimal :max karakter.',
            'no_anggota_baru.unique' => 'Nomor anggota ini sudah digunakan anggota lain.',
            'no_anggota_baru.regex' => 'Nomor anggota hanya boleh huruf, angka, titik, underscore, dan strip.',
            'catatan_verifikasi_pendaftaran.required' => 'Catatan verifikasi wajib diisi (jelaskan alasan disetujui / ditolak).',
            'catatan_verifikasi_pendaftaran.min' => 'Catatan terlalu singkat. Minimal :min karakter.',
            'catatan_verifikasi_pendaftaran.max' => 'Catatan maksimal :max karakter.',
        ];
    }
}
