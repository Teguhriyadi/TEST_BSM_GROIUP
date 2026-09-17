<?php

declare(strict_types=1);

namespace App\Http\Requests\Pinjaman;

use Illuminate\Foundation\Http\FormRequest;

class DokumenPinjamanVerifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:disetujui,ditolak,perlu_diperbaiki',
            'catatan' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes(
            'catatan',
            'required|string|min:5|max:1000',
            function ($input) {
                return in_array($input->status, ['ditolak', 'perlu_diperbaiki'], true);
            },
        );
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Pilih hasil verifikasi dokumen (Disetujui / Ditolak / Perlu Diperbaiki).',
            'status.in' => 'Status verifikasi yang dipilih tidak valid.',
            'catatan.min' => 'Catatan minimal 5 karakter untuk menjelaskan alasan penolakan atau perbaikan.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
            'catatan.required' => 'Catatan wajib diisi ketika dokumen ditolak atau perlu diperbaiki.',
        ];
    }
}
