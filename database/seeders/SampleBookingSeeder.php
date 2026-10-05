<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Pesanan contoh agar kursi terisi pada jadwal Jakarta → Bandung besok (trip 1..8) sama dengan
 * `occupied` di PageController, plus 3 pesanan untuk uji halaman Cek Pesanan.
 * Id jadwal dicari lewat rute + tanggal + urutan jam berangkat, bukan di-hardcode.
 */
class SampleBookingSeeder extends Seeder
{
    private const SERVICE_FEE = 5000;

    /** Urutan kursi terisi (sama dengan PageController). */
    private const OCCUPIED_ORDER = ['1', '2', '5', '6', '8', '9', '11', '12', '13', '14', '4', '7', '10'];

    /** Sisa kursi per trip dummy 1..8 (urutan jam berangkat jadwal Jakarta → Bandung besok). */
    private const SEATS_LEFT = [1 => 4, 2 => 7, 3 => 2, 4 => 6, 5 => 9, 6 => 12, 7 => 1, 8 => 5];

    public function run(): void
    {
        $scheduleIdByTrip = $this->jakartaBandungTomorrowScheduleIds();

        foreach (self::SEATS_LEFT as $trip => $seatsLeft) {
            $scheduleId = $scheduleIdByTrip[$trip];
            $occupied = array_slice(self::OCCUPIED_ORDER, 0, max(0, 14 - $seatsLeft));

            foreach (array_chunk($occupied, 3) as $chunk => $seats) {
                $code = 'RID-'.strtoupper(substr(md5("seed-{$trip}-{$chunk}"), 0, 6));
                $status = 'paid';
                $contact = ['Penumpang Contoh', "contoh{$trip}{$chunk}@example.com", '081200000'.sprintf('%03d', $trip * 10 + $chunk)];

                // Pesanan untuk uji Cek Pesanan (kode tetap, didokumentasikan di 02-schema.md).
                if ($trip === 1 && $chunk === 0) {
                    [$code, $contact] = ['RID-7K2M9P', ['Budi Santoso', 'budi@example.com', '081234567890']];
                }
                if ($trip === 4 && $chunk === 0) {
                    [$code, $status, $contact] = ['RID-4QX8ZT', 'pending_payment', ['Siti Rahma', 'siti@example.com', '081298765432']];
                }

                $this->createBooking($scheduleId, $seats, $code, $status, $contact, 'va_bca');
            }
        }

        // Pesanan kedaluwarsa: kursi sudah dilepas (tidak ada seat_reservations).
        $this->createBooking($scheduleIdByTrip[2], ['3'], 'RID-9HB3WD', 'expired', ['Andi Wijaya', 'andi@example.com', '081311112222'], 'qris');
    }

    /**
     * Id jadwal Jakarta → Bandung besok, di-key nomor trip 1..8 (urut jam berangkat).
     *
     * @return array<int, int>
     */
    private function jakartaBandungTomorrowScheduleIds(): array
    {
        $cityId = DB::table('cities')->whereIn('name', ['Jakarta', 'Bandung'])->pluck('id', 'name');
        $routeId = DB::table('routes')
            ->where('origin_city_id', $cityId['Jakarta'])
            ->where('destination_city_id', $cityId['Bandung'])
            ->value('id');
        $tomorrow = Carbon::tomorrow();

        $ids = DB::table('schedules')
            ->where('route_id', $routeId)
            ->whereBetween('departure_at', [$tomorrow, $tomorrow->copy()->endOfDay()])
            ->orderBy('departure_at')
            ->pluck('id')
            ->all();

        return array_combine(range(1, count($ids)), $ids);
    }

    private function createBooking(int $scheduleId, array $seats, string $code, string $status, array $contact, string $paymentMethodCode): void
    {
        $now = now();
        $createdAt = $status === 'pending_payment' ? $now->copy()->subMinutes(5) : $now->copy()->subDays(2);
        $expiresAt = $createdAt->copy()->addMinutes(30);

        $price = DB::table('schedules')->where('id', $scheduleId)->value('price');
        $stops = DB::table('schedule_stops')->where('schedule_id', $scheduleId)->orderBy('sort_order');
        $pickupStopId = (clone $stops)->where('type', 'pickup')->value('id');
        $dropoffStopId = (clone $stops)->where('type', 'dropoff')->value('id');

        $subtotal = $price * count($seats);
        $total = $subtotal + self::SERVICE_FEE;
        $stamp = ['created_at' => $createdAt, 'updated_at' => $createdAt];

        $bookingId = DB::table('bookings')->insertGetId([
            'booking_code' => $code, 'schedule_id' => $scheduleId,
            'pickup_stop_id' => $pickupStopId, 'dropoff_stop_id' => $dropoffStopId,
            'contact_name' => $contact[0], 'contact_email' => $contact[1], 'contact_phone' => $contact[2],
            'subtotal' => $subtotal, 'service_fee' => self::SERVICE_FEE, 'discount_amount' => 0, 'total_amount' => $total,
            'status' => $status, 'expires_at' => $expiresAt,
        ] + $stamp);

        DB::table('passengers')->insert(array_map(fn ($seat, $index) => [
            'booking_id' => $bookingId,
            'name' => $index === 0 ? $contact[0] : "{$contact[0]} ".($index + 1),
            'seat_number' => $seat,
        ] + $stamp, $seats, array_keys($seats)));

        if (in_array($status, ['pending_payment', 'paid'], true)) {
            DB::table('seat_reservations')->insert(array_map(fn ($seat) => [
                'schedule_id' => $scheduleId, 'seat_number' => $seat, 'booking_id' => $bookingId,
                'status' => 'booked', 'held_until' => null,
            ] + $stamp, $seats));
        }

        DB::table('payments')->insert([
            'booking_id' => $bookingId, 'payment_method_id' => DB::table('payment_methods')->where('code', $paymentMethodCode)->value('id'), 'amount' => $total,
            'status' => match ($status) {
                'paid' => 'paid', 'expired' => 'expired', default => 'pending'
            },
            'virtual_account_number' => str_starts_with($paymentMethodCode, 'va_') ? '8808'.substr($contact[2], -8).sprintf('%04d', $bookingId) : null,
            'qr_string' => $paymentMethodCode === 'qris' ? "00020101021226DUMMYQRIS{$code}" : null,
            'expires_at' => $expiresAt,
            'paid_at' => $status === 'paid' ? Carbon::parse($createdAt)->addMinutes(7) : null,
        ] + $stamp);
    }
}
