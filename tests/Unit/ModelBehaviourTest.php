<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Enums\ScheduleStatus;
use App\Models\Booking;
use App\Models\Operator;
use App\Models\PaymentMethod;
use App\Models\Promo;
use App\Models\Schedule;
use App\Models\SeatReservation;
use App\Models\VehicleType;
use App\Support\TripPresenter;
use Illuminate\Database\LazyLoadingViolationException;
use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

/** Accessor, mutator, dan scope model (butuh DB seed untuk scope). */
class ModelBehaviourTest extends TestCase
{
    use SeededDatabase;

    public function test_it_should_list_passenger_seats_without_driver_and_aisle_cells(): void
    {
        $type = new VehicleType(['seat_layout' => [['D', null, null, '1'], ['2', null, '3', '4']]]);

        $this->assertSame(['1', '2', '3', '4'], $type->seat_numbers);
    }

    public function test_it_should_return_empty_seat_list_when_layout_is_missing(): void
    {
        $this->assertSame([], (new VehicleType)->seat_numbers);
    }

    public function test_it_should_compute_seats_left_from_with_count_when_seats_taken_is_loaded(): void
    {
        $seatsLeft = Schedule::withSeatsTaken()
            ->with('vehicle.vehicleType')
            ->whereIn('id', $this->jakartaBandungTripIds())
            ->orderBy('departure_at')
            ->get()
            ->pluck('seats_left')
            ->all();

        $this->assertSame([4, 7, 2, 6, 9, 12, 1, 5], $seatsLeft);
    }

    public function test_it_should_compute_seats_left_from_loaded_relation_when_count_is_not_loaded(): void
    {
        $schedule = Schedule::with(['vehicle.vehicleType', 'activeSeatReservations'])->findOrFail($this->tripId(1));

        $this->assertSame(4, $schedule->seats_left);
    }

    public function test_it_should_compute_duration_across_midnight(): void
    {
        $this->assertSame(190, $this->schedule(8)->duration_minutes);
        $this->assertSame(195, $this->schedule(1)->duration_minutes);
    }

    public function test_it_should_cast_status_columns_to_enums(): void
    {
        $this->assertSame(ScheduleStatus::Scheduled, $this->schedule(1)->status);
        $this->assertSame(BookingStatus::PendingPayment, Booking::byCode('RID-4QX8ZT')->firstOrFail()->status);
        $this->assertSame(4.8, Operator::where('name', 'Nusa Trans')->firstOrFail()->rating_average);
    }

    public function test_it_should_only_include_future_scheduled_trips_in_bookable_scope(): void
    {
        $ids = $this->jakartaBandungTripIds();
        Schedule::findOrFail($ids[1])->update(['departure_at' => now()->subMinute()]);
        Schedule::findOrFail($ids[2])->update(['status' => ScheduleStatus::Cancelled]);

        $bookable = Schedule::bookable()->whereIn('id', $ids)->pluck('id')->all();

        $this->assertNotContains($ids[1], $bookable);
        $this->assertNotContains($ids[2], $bookable);
        $this->assertCount(6, $bookable);
    }

    public function test_it_should_limit_on_date_scope_to_the_whole_given_day(): void
    {
        $route = $this->routeBetween('Jakarta', 'Bandung');

        $this->assertSame(8, $route->schedules()->onDate(now()->addDay())->count());
        $this->assertSame(0, $route->schedules()->onDate(now()->addDays(30))->count());
    }

    public function test_it_should_treat_booked_and_unexpired_holds_as_active_seat_reservations(): void
    {
        $scheduleId = $this->tripId(3);
        $before = SeatReservation::active()->where('schedule_id', $scheduleId)->count();
        SeatReservation::create(['schedule_id' => $scheduleId, 'seat_number' => '3', 'status' => 'held', 'hold_token' => 'a', 'held_until' => now()->subSecond()]);
        SeatReservation::create(['schedule_id' => $scheduleId, 'seat_number' => '10', 'status' => 'held', 'hold_token' => 'b', 'held_until' => now()->addMinutes(5)]);

        $this->assertSame($before + 1, SeatReservation::active()->where('schedule_id', $scheduleId)->count());
    }

    public function test_it_should_only_return_currently_valid_promos_in_active_scope(): void
    {
        $this->keepSeededPromosValid();
        $this->assertSame(3, Promo::active()->count());

        Promo::where('code', 'JALANHEMAT')->update(['valid_until' => now()->subDay()]);
        Promo::where('code', 'QRISHEMAT')->update(['valid_from' => now()->addDay()]);

        $this->assertSame(['WEEKENDMUDIK'], Promo::active()->pluck('code')->all());
    }

    public function test_it_should_uppercase_promo_code_when_assigned(): void
    {
        $this->assertSame('HEMAT10', (new Promo(['code' => '  hemat10 ']))->code);
    }

    public function test_it_should_normalize_contact_phone_and_email_when_assigned(): void
    {
        $booking = new Booking([
            'contact_phone' => '+62 812-3456-7890',
            'contact_email' => '  Budi@Example.COM ',
        ]);

        $this->assertSame('081234567890', $booking->getAttributes()['contact_phone']);
        $this->assertSame('budi@example.com', $booking->getAttributes()['contact_email']);
        $this->assertSame('081234567890', Booking::normalizePhone('6281234567890'));
    }

    public function test_it_should_find_booking_by_code_ignoring_case_and_spaces(): void
    {
        $this->assertTrue(Booking::byCode('  rid-7k2m9p ')->exists());
        $this->assertFalse(Booking::byCode('RID-XXXXXX')->exists());
    }

    public function test_it_should_order_active_payment_methods_by_sort_order(): void
    {
        PaymentMethod::where('code', 'ovo')->update(['is_active' => false]);

        $codes = PaymentMethod::active()->pluck('code')->all();

        $this->assertSame('va_bca', $codes[0]);
        $this->assertSame('qris', end($codes));
        $this->assertNotContains('ovo', $codes);
    }

    public function test_it_should_format_approximate_hours_with_half_hour_rounding(): void
    {
        $this->assertSame('± 3 jam', TripPresenter::formatApproxHours(180));
        $this->assertSame('± 3,5 jam', TripPresenter::formatApproxHours(210));
        $this->assertSame('± 1,5 jam', TripPresenter::formatApproxHours(90));
        $this->assertSame('± 4 jam', TripPresenter::formatApproxHours(240));
    }

    public function test_it_should_throw_on_lazy_loading_outside_production(): void
    {
        $this->expectException(LazyLoadingViolationException::class);

        Schedule::whereIn('id', $this->jakartaBandungTripIds())->get()->first()->vehicle;
    }
}
