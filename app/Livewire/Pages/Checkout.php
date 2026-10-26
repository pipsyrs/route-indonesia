<?php

namespace App\Livewire\Pages;

use App\Actions\ValidateSeatSelection;
use App\Support\DummyShop;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Render-only: isi checkout tetap dikelola JS dari localStorage. */
#[Layout('layouts.app')]
#[Title('Checkout')]
class Checkout extends Component
{
    /**
     * Validasi ulang kursi item trip; mixed agar payload rusak tidak memicu TypeError (500).
     *
     * @return array{valid: bool, errors: array<string, string>}
     */
    #[Renderless]
    public function validateSeats(mixed $items = null): array
    {
        return app(ValidateSeatSelection::class)->handle($items);
    }

    public function render(): View
    {
        return view('livewire.pages.checkout', [
            'serviceFee' => DummyShop::SERVICE_FEE,
            'provinces' => DummyShop::provinces(),
        ]);
    }
}
