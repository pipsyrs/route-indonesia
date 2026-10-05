<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\SeatReservation;
use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

/**
 * Auth case fase ini: semua route publik, tanpa login (N/A).
 * Penggantinya: halaman publik tidak boleh membocorkan PII pesanan orang lain.
 */
class PublicDataExposureTest extends TestCase
{
    use SeededDatabase;

    public function test_it_should_not_expose_seeded_booking_pii_when_rendering_public_pages(): void
    {
        // Halaman /akun* sengaja menampilkan user dummy (DummyShop::user), jadi tidak dicek di sini.
        $pages = [
            '/', '/katalog?from=Jakarta&to=Bandung&date='.$this->tomorrow(), '/katalog/1',
            '/keranjang', '/checkout', '/pembayaran', '/pembayaran/selesai', '/masuk', '/daftar', '/cek-pesanan',
        ];

        foreach ($pages as $page) {
            $this->get($page)
                ->assertOk()
                ->assertDontSee('budi@example.com')
                ->assertDontSee('081234567890')
                ->assertDontSee('Budi Santoso')
                ->assertDontSee('RID-7K2M9P')
                ->assertDontSee('DUMMYQRIS');
        }
    }

    public function test_it_should_hide_pii_and_secrets_when_models_are_serialized(): void
    {
        $booking = Booking::byCode('rid-7k2m9p')->firstOrFail();
        $passenger = Passenger::where('booking_id', $booking->id)->firstOrFail();
        $payment = Payment::where('booking_id', Booking::byCode('RID-9HB3WD')->value('id'))->firstOrFail();
        $reservation = SeatReservation::create([
            'schedule_id' => $this->tripId(3), 'seat_number' => '10', 'status' => 'held',
            'hold_token' => 'secret-token', 'held_until' => now()->addMinutes(5),
        ]);

        $this->assertArrayNotHasKey('contact_email', $booking->toArray());
        $this->assertArrayNotHasKey('contact_phone', $booking->toArray());
        $this->assertStringNotContainsString('budi@example.com', $booking->toJson());
        $this->assertArrayNotHasKey('phone', $passenger->toArray());
        $this->assertArrayNotHasKey('qr_string', $payment->toArray());
        $this->assertArrayNotHasKey('gateway_reference', $payment->toArray());
        $this->assertArrayNotHasKey('hold_token', $reservation->fresh()->toArray());
    }
}
