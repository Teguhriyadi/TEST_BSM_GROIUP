<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'cabang_id' => 'required|uuid|exists:cabang,id',
            'nama' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:6|max:50',
            'role_id' => 'required|uuid|exists:role,id',
            'nomor_hp' => 'nullable|string|max:15',
            'is_active' => 'required|in:1,0',
        ];
    }

    public function messages(): array
    {
        return [
            'cabang_id.required' => 'Cabang User wajib dipilih.',
            'cabang_id.uuid' => 'Format Cabang tidak valid.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak ditemukan.',
            'nama.required' => 'Nama User wajib diisi.',
            'nama.max' => 'Nama User maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format Email tidak valid (contoh: nama@domain.test).',
            'email.max' => 'Email maksimal 100 karakter.',
            'email.unique' => 'Email tersebut sudah terdaftar, gunakan email lain.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.max' => 'Password maksimal 50 karakter.',
            'role_id.required' => 'Role User wajib dipilih.',
            'role_id.uuid' => 'Format Role tidak valid.',
            'role_id.exists' => 'Role yang dipilih tidak ditemukan.',
            'nomor_hp.max' => 'Nomor HP maksimal 15 digit.',
            'is_active.required' => 'Status User wajib dipilih.',
            'is_active.in' => 'Status User harus Aktif atau Nonaktif.',
        ];
    }
}
