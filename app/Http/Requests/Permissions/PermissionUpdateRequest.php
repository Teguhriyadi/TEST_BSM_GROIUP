<?php

declare(strict_types=1);

namespace App\Http\Requests\Permissions;

use Illuminate\Foundation\Http\FormRequest;

class PermissionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')?->id ?? $this->route('permission');

        return [
            'kode_permission' => 'required|string|max:50|unique:permissions,kode_permission,' . $permissionId,
            'nama_permission' => 'required|string|max:50',
            'deskripsi' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_permission.required' => 'Kode Permission wajib diisi.',
            'kode_permission.max' => 'Kode Permission maksimal 50 karakter.',
            'kode_permission.unique' => 'Kode Permission tersebut sudah digunakan, pilih kode lain.',
            'nama_permission.required' => 'Nama Permission wajib diisi.',
            'nama_permission.max' => 'Nama Permission maksimal 50 karakter.',
        ];
    }
}
