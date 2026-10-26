<?php

namespace App\Livewire\Forms;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class PaymentProofForm extends Form
{
    /** @var TemporaryUploadedFile|null Sengaja tanpa type hint: $set() string dari client tidak boleh jadi TypeError/500. */
    public $proof = null;

    public string $senderName = '';

    protected function rules(): array
    {
        return [
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'senderName' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'proof.required' => 'Bukti pembayaran wajib diunggah.',
            'proof.file' => 'Bukti pembayaran harus berupa file.',
            'proof.uploaded' => 'Bukti pembayaran gagal diunggah. Coba lagi.',
            'proof.mimes' => 'Bukti pembayaran harus berformat JPG, PNG, atau PDF.',
            'proof.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
            'senderName.string' => 'Nama pengirim tidak valid.',
            'senderName.max' => 'Nama pengirim maksimal 100 karakter.',
        ];
    }
}
