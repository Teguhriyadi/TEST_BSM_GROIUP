<?php

namespace App\Http\Requests\Password;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordSelfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_lama' => [
                'required',
                'string',
                'min:6',
                function ($attribute, $value, $fail) {
                    if (! Hash::check($value, $this->user()->password)) {
                        $fail('Password lama yang Anda masukkan tidak sesuai.');
                    }
                },
            ],
            'password_baru' => 'required|string|min:6|max:100|confirmed',
            'password_baru_confirmation' => 'required|string|min:6|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password_lama.min' => 'Password lama minimal 6 karakter.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 6 karakter.',
            'password_baru.max' => 'Password baru maksimal 100 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak sama dengan password baru.',
            'password_baru_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'password_baru_confirmation.min' => 'Konfirmasi password baru minimal 6 karakter.',
            'password_baru_confirmation.max' => 'Konfirmasi password baru maksimal 100 karakter.',
        ];
    }
}
