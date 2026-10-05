<?php

namespace Tests\Feature\Concerns;

use App\Models\City;
use App\Models\Promo;
use App\Models\Schedule;
use App\Models\TravelRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * DB test di-migrate + seed sekali, lalu tiap test berjalan dalam transaksi
 * (RefreshDatabase), jadi perubahan data di satu test tidak bocor ke test lain.
 */
trait SeededDatabase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * Id jadwal Jakarta → Bandung besok, di-key nomor trip 1..8 (urut jam berangkat).
     *
     * @return array<int, int>
     */
    protected function jakartaBandungTripIds(?string $date = null): array
    {
        $ids = $this->routeBetween('Jakarta', 'Bandung')
            ->schedules()
            ->onDate(now()->parse($date ?? now()->addDay()->toDateString()))
            ->orderBy('departure_at')
            ->pluck('id')
            ->all();

        return array_combine(range(1, count($ids)), $ids);
    }

    protected function routeBetween(string $origin, string $destination): TravelRoute
    {
        $cityIds = City::pluck('id', 'name');

        return TravelRoute::between($cityIds[$origin], $cityIds[$destination])->firstOrFail();
    }

    protected function tripId(int $trip): int
    {
        return $this->jakartaBandungTripIds()[$trip];
    }

    protected function schedule(int $trip): Schedule
    {
        return Schedule::findOrFail($this->tripId($trip));
    }

    /**
     * Promo seed memakai valid_until tetap (2026-11-15 s.d. 2026-12-31). Agar test tidak kedaluwarsa,
     * geser ke now() + 30.. hari dengan urutan valid_until yang sama seperti seed.
     * (travelTo tidak dipakai: jadwal di-seed relatif ke waktu nyata, sehingga "besok" ikut bergeser.)
     */
    protected function keepSeededPromosValid(): void
    {
        Promo::orderBy('valid_until')->get()->each(
            fn (Promo $promo, int $rank) => $promo->update(['valid_until' => now()->addDays(30 + $rank)->endOfDay()]),
        );
    }

    protected function tomorrow(): string
    {
        return now()->addDay()->toDateString();
    }
}
