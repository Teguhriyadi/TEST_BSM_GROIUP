<?php

namespace App\Http\Requests\Password;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password_baru' => 'required|string|min:6|max:100|confirmed',
            'password_baru_confirmation' => 'required|string|min:6|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token reset password tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email yang Anda masukkan tidak terdaftar di sistem.',
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
