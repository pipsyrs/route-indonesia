<?php

namespace Tests\Unit;

use App\Support\DummyShop;
use Database\Seeders\FleetSeeder;
use PHPUnit\Framework\TestCase;

class DummyShopSeatTest extends TestCase
{
    public function test_it_should_give_every_trip_capacity_above_seats_left(): void
    {
        foreach (DummyShop::trips() as $trip) {
            $capacity = count(DummyShop::seatCodes(DummyShop::seatLayout($trip['vehicle'])));

            $this->assertGreaterThanOrEqual($trip['seats_left'] + 1, $capacity, "trip {$trip['id']}");
        }
    }

    public function test_it_should_build_uniform_layouts_with_unique_seat_codes(): void
    {
        foreach (['Hiace Premio' => 10, 'Hiace Commuter' => 14, 'Elf Long' => 18, 'Executive Bus' => 32] as $vehicle => $capacity) {
            $layout = DummyShop::seatLayout($vehicle);
            $codes = DummyShop::seatCodes($layout);

            $this->assertLessThanOrEqual(15, count($layout), $vehicle);
            $this->assertCount(1, array_unique(array_map('count', $layout)), $vehicle);
            $this->assertContains(count($layout[0]), [4, 5], $vehicle);
            $this->assertSame(array_map('strval', range(1, $capacity)), $codes, $vehicle);
        }

        $this->assertSame(FleetSeeder::SEAT_LAYOUT_14, DummyShop::seatLayout('Hiace Commuter'));
    }

    public function test_it_should_pick_occupied_seats_deterministically(): void
    {
        foreach (DummyShop::trips() as $trip) {
            $codes = DummyShop::seatCodes(DummyShop::seatLayout($trip['vehicle']));
            $occupied = DummyShop::occupiedSeats($trip['id'], '2026-12-01');

            $this->assertCount(count($codes) - $trip['seats_left'], $occupied, "trip {$trip['id']}");
            $this->assertSame($occupied, DummyShop::occupiedSeats($trip['id'], '2026-12-01'));
            $this->assertSame([], array_diff($occupied, $codes));
            $this->assertSame(array_values(array_unique($occupied)), $occupied);

            $sorted = $occupied;
            usort($sorted, 'strnatcmp');
            $this->assertSame($sorted, $occupied);
        }

        $this->assertNotSame(DummyShop::occupiedSeats(5, '2026-12-01'), DummyShop::occupiedSeats(5, '2026-12-02'));
        $this->assertSame([], DummyShop::occupiedSeats(999, '2026-12-01'));
    }

    public function test_it_should_combine_layout_and_occupied_in_seat_map(): void
    {
        $trip = DummyShop::findTrip(1);

        $this->assertSame([
            'layout' => DummyShop::seatLayout('Hiace Premio'),
            'occupied' => DummyShop::occupiedSeats(1, '2026-12-01'),
        ], DummyShop::seatMap($trip, '2026-12-01'));
    }

    public function test_it_should_list_38_sorted_unique_provinces(): void
    {
        $provinces = DummyShop::provinces();
        $sorted = $provinces;
        sort($sorted, SORT_STRING);

        $this->assertCount(38, array_unique($provinces));
        $this->assertSame($sorted, $provinces);
        $this->assertContains('DKI Jakarta', $provinces);
        $this->assertContains('Papua Barat Daya', $provinces);
    }
}
