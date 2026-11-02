<?php

namespace Tests\Unit;

use App\Support\DummyShop;
use PHPUnit\Framework\TestCase;

class DummyShopSeatTest extends TestCase
{
    public function test_it_should_give_every_trip_capacity_above_seats_left(): void
    {
        foreach (DummyShop::trips() as $trip) {
            $capacity = count(DummyShop::seatCodes(DummyShop::seatLayout()));

            $this->assertGreaterThanOrEqual($trip['seats_left'] + 1, $capacity, "trip {$trip['id']}");
        }
    }

    public function test_it_should_use_static_13_seat_layout_with_crew_and_driver(): void
    {
        $layout = DummyShop::seatLayout();

        $this->assertSame(array_map('strval', range(1, 13)), DummyShop::seatCodes($layout));
        $this->assertSame(['C', null, null, 'D'], $layout[0]);
        $this->assertCount(1, array_unique(array_map('count', $layout)));
    }

    public function test_it_should_pick_occupied_seats_deterministically(): void
    {
        foreach (DummyShop::trips() as $trip) {
            $codes = DummyShop::seatCodes(DummyShop::seatLayout());
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
            'layout' => DummyShop::seatLayout(),
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
