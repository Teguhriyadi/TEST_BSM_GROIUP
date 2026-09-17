<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

class RoleCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_role' => 'required|string|max:50|unique:role,kode_role',
            'nama_role' => 'required|string|max:50',
            'is_active' => 'required|in:1,0',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_role.required' => 'Kode Role wajib diisi.',
            'kode_role.max' => 'Kode Role maksimal 50 karakter.',
            'kode_role.unique' => 'Kode Role tersebut sudah digunakan, pilih kode lain.',
            'nama_role.required' => 'Nama Role wajib diisi.',
            'nama_role.max' => 'Nama Role maksimal 50 karakter.',
            'is_active.required' => 'Status Role wajib dipilih.',
            'is_active.in' => 'Status Role harus Aktif atau Nonaktif.',
        ];
    }
}
