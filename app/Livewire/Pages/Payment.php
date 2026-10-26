<?php

namespace App\Livewire\Pages;

use App\Livewire\Forms\PaymentProofForm;
use App\Services\PaymentAccountService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/** Transfer bank manual: tampilkan rekening tujuan, terima bukti bayar (belum disimpan permanen). */
#[Layout('layouts.app')]
#[Title('Pembayaran')]
class Payment extends Component
{
    use WithFileUploads;

    public const SUBMIT_MAX_ATTEMPTS = 5;

    public const SUBMIT_DECAY_SECONDS = 60;

    public PaymentProofForm $form;

    /** @return list<array{bank: string, number: string, holder: string}> */
    #[Computed]
    public function bankAccounts(): array
    {
        return app(PaymentAccountService::class)->active();
    }

    public function submit(): void
    {
        $key = 'payment-proof:'.request()->ip().'|'.session()->getId();

        if (RateLimiter::tooManyAttempts($key, self::SUBMIT_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            $this->resetErrorBag();
            $this->addError('form.proof', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");

            return;
        }

        RateLimiter::hit($key, self::SUBMIT_DECAY_SECONDS);

        $this->form->validate();

        $this->dispatch('payment-proof-submitted');

        // Belum disimpan permanen: jangan biarkan bukti tertinggal di livewire-tmp.
        $this->form->proof?->delete();
        $this->form->reset();
    }

    /** Nilai non-file dari $set() dibuang agar tidak sampai ke view/submit. */
    public function updatedFormProof(mixed $value): void
    {
        if ($value !== null && ! $value instanceof TemporaryUploadedFile) {
            $this->form->proof = null;
            $this->addError('form.proof', 'Bukti pembayaran harus berupa file.');
        }
    }

    public function render(): View
    {
        return view('livewire.pages.payment');
    }
}
