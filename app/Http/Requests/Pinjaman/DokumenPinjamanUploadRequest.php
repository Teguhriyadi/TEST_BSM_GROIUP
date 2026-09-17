<?php

declare(strict_types=1);

namespace App\Http\Requests\Pinjaman;

use Illuminate\Foundation\Http\FormRequest;

class DokumenPinjamanUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dokumen' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf',
        ];
    }

    public function messages(): array
    {
        return [
            'dokumen.required' => 'Pilih file dokumen terlebih dahulu sebelum mengunggah.',
            'dokumen.file' => 'File dokumen yang dipilih tidak valid.',
            'dokumen.max' => 'Ukuran file dokumen maksimal 10 MB.',
            'dokumen.mimes' => 'Format file dokumen harus berupa JPG, JPEG, PNG, atau PDF.',
        ];
    }
}
