<?php

namespace App\Livewire\Pages;

use App\Support\DummyShop;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Render-only: isi checkout tetap dikelola JS dari localStorage. */
#[Layout('layouts.app')]
#[Title('Checkout')]
class Checkout extends Component
{
    public function render(): View
    {
        return view('livewire.pages.checkout', [
            'serviceFee' => DummyShop::SERVICE_FEE,
        ]);
    }
}
