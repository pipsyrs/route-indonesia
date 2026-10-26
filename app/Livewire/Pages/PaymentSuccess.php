<?php

namespace App\Livewire\Pages;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Render-only: status pesanan dibaca JS dari localStorage. */
#[Layout('layouts.app')]
#[Title('Bukti pembayaran terkirim')]
class PaymentSuccess extends Component
{
    public function render(): View
    {
        return view('livewire.pages.payment-success');
    }
}
