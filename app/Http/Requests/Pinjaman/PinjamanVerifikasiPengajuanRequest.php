<?php

declare(strict_types=1);

namespace App\Http\Requests\Pinjaman;

use Illuminate\Foundation\Http\FormRequest;

class PinjamanVerifikasiPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catatan' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
