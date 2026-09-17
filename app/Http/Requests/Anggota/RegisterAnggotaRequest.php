<?php

namespace App\Http\Requests\Anggota;

use App\Models\Cabang;
use Illuminate\Foundation\Http\FormRequest;

class RegisterAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cabang_id' => [
                'bail',
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $ada = Cabang::where('id', $value)->where('is_active', '1')->exists();
                    if (! $ada) {
                        $fail('Cabang yang dipilih tidak valid atau tidak aktif.');
                    }
                },
            ],
            'nik' => [
                'bail',
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]+$/',
                'unique:anggota,nik',
            ],
            'nama' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:100',
            ],
            'email' => [
                'bail',
                'required',
                'email:dns,filter',
                'max:100',
                'unique:users,email',
                'unique:anggota,nik',
                function ($attribute, $value, $fail) {
                    $lower = strtolower((string) $value);
                    if (preg_match('/(tempmail|10minutemail|mailnesia|yopmail|dispostable|guerrillamail)/i', $lower)) {
                        $fail('Alamat email sementara tidak diperbolehkan. Gunakan email aktif Anda.');
                    }
                },
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
                'max:32',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
            ],
            'jenis_kelamin' => [
                'bail',
                'required',
                'string',
                'in:L,P',
            ],
            'tgl_lahir' => [
                'bail',
                'required',
                'date',
                'before:-17 years',
            ],
            'no_hp' => [
                'bail',
                'required',
                'string',
                'min:9',
                'max:15',
                'regex:/^(\+?62|0)[1-9][0-9]+$/',
            ],
            'alamat' => [
                'bail',
                'required',
                'string',
                'min:10',
                'max:500',
            ],
            'persetujuan_syarat' => [
                'bail',
                'required',
                'in:1,on,yes,true',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'cabang_id' => 'cabang pendaftaran',
            'nik' => 'nomor induk kependudukan (NIK)',
            'nama' => 'nama lengkap',
            'email' => 'alamat email',
            'password' => 'kata sandi',
            'password_confirmation' => 'konfirmasi kata sandi',
            'jenis_kelamin' => 'jenis kelamin',
            'tgl_lahir' => 'tanggal lahir',
            'no_hp' => 'nomor handphone',
            'alamat' => 'alamat lengkap',
            'persetujuan_syarat' => 'persetujuan syarat dan ketentuan',
        ];
    }

    public function messages(): array
    {
        return [
            'cabang_id.required' => 'Silakan pilih cabang pendaftaran terlebih dahulu.',
            'cabang_id.string' => 'Format cabang tidak valid.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'nik.regex' => 'NIK hanya boleh diisi dengan angka 0-9.',
            'nik.unique' => 'NIK ini sudah terdaftar sebagai anggota koperasi.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.min' => 'Nama lengkap minimal :min karakter.',
            'nama.max' => 'Nama lengkap maksimal :max karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid. Contoh benar: nama@domain.com.',
            'email.max' => 'Alamat email maksimal :max karakter.',
            'email.unique' => 'Alamat email ini sudah terdaftar. Gunakan email lain atau lupa password.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal :min karakter.',
            'password.max' => 'Kata sandi maksimal :max karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.regex' => 'Kata sandi harus mengandung huruf besar, huruf kecil, dan angka.',
            'jenis_kelamin.required' => 'Silakan pilih jenis kelamin.',
            'jenis_kelamin.in' => 'Pilihan jenis kelamin tidak valid.',
            'tgl_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tgl_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tgl_lahir.before' => 'Anda harus berusia minimal 17 tahun untuk mendaftar.',
            'no_hp.required' => 'Nomor handphone wajib diisi.',
            'no_hp.min' => 'Nomor handphone minimal :min digit.',
            'no_hp.max' => 'Nomor handphone maksimal :max digit.',
            'no_hp.regex' => 'Format nomor handphone tidak valid. Gunakan 08xxx atau +628xxx.',
            'alamat.required' => 'Alamat lengkap wajib diisi.',
            'alamat.min' => 'Alamat terlalu singkat. Tuliskan alamat selengkap mungkin.',
            'alamat.max' => 'Alamat maksimal :max karakter.',
            'persetujuan_syarat.required' => 'Anda wajib menyetujui syarat dan ketentuan pendaftaran.',
            'persetujuan_syarat.in' => 'Anda wajib menyetujui syarat dan ketentuan sebelum melanjutkan.',
        ];
    }
}
