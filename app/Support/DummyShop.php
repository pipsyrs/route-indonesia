<?php

namespace App\Support;

/**
 * Data dummy statis untuk alur katalog, keranjang, dan akun (tanpa DB).
 * Bentuk trip sama dengan TripPresenter::presentTrip() tanpa key `occupied`.
 */
class DummyShop
{
    public const SERVICE_FEE = 5000;

    public const MAX_QTY = 6;

    /** @return list<string> */
    public static function cities(): array
    {
        return ['Jakarta', 'Bandung', 'Yogyakarta', 'Semarang', 'Surabaya', 'Malang'];
    }

    /** @return array<string, string> code => label */
    public static function facilityLabels(): array
    {
        return [
            'ac' => 'AC',
            'wifi' => 'Wi-Fi',
            'charger' => 'Charger',
            'reclining' => 'Kursi reclining',
            'snack' => 'Snack',
            'toilet' => 'Toilet',
            'blanket' => 'Selimut',
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function trips(): array
    {
        // [id, operator, vehicle, origin, destination, depart, menit, harga, sisa kursi, fasilitas, rating, jemput, antar]
        $rows = [
            [1, 'Cipaganti', 'Hiace Premio', 'Jakarta', 'Bandung', '07:00', 195, 150000, 7, ['ac', 'wifi', 'charger'], 4.7, 'Pool Cawang', 'Pool Pasteur'],
            [2, 'Daytrans', 'Hiace Commuter', 'Jakarta', 'Bandung', '09:30', 180, 135000, 4, ['ac', 'charger'], 4.5, 'Pool Blok M', 'Pool Dipatiukur'],
            [3, 'Baraya', 'Elf Long', 'Jakarta', 'Bandung', '13:00', 210, 110000, 10, ['ac'], 4.2, 'Pool Grogol', 'Pool Surapati'],
            [4, 'Xtrans', 'Hiace Premio', 'Bandung', 'Jakarta', '06:00', 200, 150000, 3, ['ac', 'wifi', 'charger', 'reclining'], 4.8, 'Pool Cihampelas', 'Pool Kuningan'],
            [5, 'Joglosemar', 'Executive Bus', 'Jakarta', 'Yogyakarta', '19:00', 540, 285000, 12, ['ac', 'wifi', 'toilet', 'blanket', 'reclining'], 4.6, 'Terminal Lebak Bulus', 'Pool Jombor'],
            [6, 'Rajawali', 'Hiace Premio', 'Yogyakarta', 'Semarang', '08:00', 210, 120000, 5, ['ac', 'charger', 'snack'], 4.4, 'Pool Janti', 'Pool Banyumanik'],
            [7, 'Nusantara', 'Executive Bus', 'Semarang', 'Surabaya', '20:30', 420, 230000, 9, ['ac', 'toilet', 'blanket'], 4.3, 'Terminal Terboyo', 'Terminal Bungurasih'],
            [8, 'Tirtaja', 'Hiace Commuter', 'Surabaya', 'Malang', '10:00', 150, 95000, 6, ['ac', 'charger'], 4.5, 'Pool Darmo', 'Pool Dinoyo'],
            [9, 'Golden Bird', 'Elf Long', 'Malang', 'Surabaya', '15:00', 160, 90000, 2, ['ac'], 4.1, 'Pool Sukun', 'Pool Gubeng'],
            [10, 'Sumber Alam', 'Executive Bus', 'Yogyakarta', 'Jakarta', '18:00', 560, 290000, 8, ['ac', 'wifi', 'toilet', 'snack', 'reclining'], 4.7, 'Pool Ringroad', 'Pool Pulogadung'],
        ];

        return array_map(fn (array $row) => self::makeTrip(...$row), $rows);
    }

    /** @return array<string, mixed>|null */
    public static function findTrip(int $id): ?array
    {
        foreach (self::trips() as $trip) {
            if ($trip['id'] === $id) {
                return $trip;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public static function paymentMethods(): array
    {
        $vaSteps = fn (string $app, string $menu) => [
            "Buka {$app}",
            "Pilih {$menu}",
            'Masukkan nomor Virtual Account',
            'Periksa nama dan total tagihan, lalu konfirmasi',
        ];

        return [
            ['id' => 'bca_va', 'name' => 'BCA Virtual Account', 'short' => 'BCA', 'type' => 'va', 'group' => 'Virtual Account', 'instructions' => $vaSteps('m-BCA', 'm-Transfer > BCA Virtual Account')],
            ['id' => 'bni_va', 'name' => 'BNI Virtual Account', 'short' => 'BNI', 'type' => 'va', 'group' => 'Virtual Account', 'instructions' => $vaSteps('BNI Mobile Banking', 'Transfer > Virtual Account Billing')],
            ['id' => 'mandiri_va', 'name' => 'Mandiri Virtual Account', 'short' => 'Mandiri', 'type' => 'va', 'group' => 'Virtual Account', 'instructions' => $vaSteps("Livin' by Mandiri", 'Bayar > Buat Pembayaran Baru > Multipayment')],
            ['id' => 'qris', 'name' => 'QRIS', 'short' => 'QRIS', 'type' => 'qris', 'group' => 'QRIS', 'instructions' => [
                'Buka aplikasi e-wallet atau mobile banking',
                'Pilih menu Scan / Bayar',
                'Pindai kode QR yang tampil',
                'Periksa total tagihan, lalu konfirmasi',
            ]],
        ];
    }

    /** @return array{name: string, email: string, phone: string, joined: string} */
    public static function user(): array
    {
        return ['name' => 'Budi Santoso', 'email' => 'budi@example.com', 'phone' => '081234567890', 'joined' => '2025-03-12'];
    }

    /** @return list<array<string, mixed>> urut tanggal terbaru */
    public static function orders(): array
    {
        // [code, trip_id, date, qty, method, status]
        $rows = [
            ['RI-7K3M9Q', 1, '2026-10-12', 2, 'BCA Virtual Account', 'paid'],
            ['RI-Q4ZP8T', 5, '2026-10-08', 1, 'QRIS', 'pending'],
            ['RI-H2WN6R', 4, '2026-09-21', 3, 'Mandiri Virtual Account', 'completed'],
            ['RI-C9LX3V', 8, '2026-08-30', 1, 'BNI Virtual Account', 'cancelled'],
            ['RI-M5DY2K', 10, '2026-07-14', 2, 'BCA Virtual Account', 'completed'],
        ];

        $orders = array_map(function (array $row) {
            [$code, $tripId, $date, $qty, $method, $status] = $row;
            $trip = self::findTrip($tripId);

            return [
                'code' => $code,
                'trip_id' => $tripId,
                'origin' => $trip['origin'],
                'destination' => $trip['destination'],
                'date' => $date,
                'depart' => $trip['depart'],
                'operator' => $trip['operator'],
                'qty' => $qty,
                'total' => $trip['price'] * $qty + self::SERVICE_FEE,
                'method' => $method,
                'status' => $status,
            ];
        }, $rows);

        usort($orders, fn (array $a, array $b) => strcmp($b['date'], $a['date']));

        return $orders;
    }

    /** @param  list<string>  $facilities */
    private static function makeTrip(
        int $id,
        string $operator,
        string $vehicle,
        string $origin,
        string $destination,
        string $depart,
        int $minutes,
        int $price,
        int $seatsLeft,
        array $facilities,
        float $rating,
        string $pickupName,
        string $dropoffName,
    ): array {
        [$hour, $minute] = array_map('intval', explode(':', $depart));
        $arriveTotal = $hour * 60 + $minute + $minutes;
        $arrive = sprintf('%02d:%02d', intdiv($arriveTotal, 60) % 24, $arriveTotal % 60);

        return [
            'id' => $id,
            'operator' => $operator,
            'vehicle' => $vehicle,
            'origin' => $origin,
            'destination' => $destination,
            'depart' => $depart,
            'arrive' => $arrive,
            'arrives_next_day' => $arriveTotal >= 24 * 60,
            'duration_minutes' => $minutes,
            'duration' => intdiv($minutes, 60).'j '.($minutes % 60).'m',
            'price' => $price,
            'seats_left' => $seatsLeft,
            'facilities' => $facilities,
            'rating' => $rating,
            'pickups' => [['id' => $id * 10 + 1, 'name' => $pickupName, 'time' => $depart, 'address' => "{$pickupName}, {$origin}"]],
            'dropoffs' => [['id' => $id * 10 + 2, 'name' => $dropoffName, 'time' => $arrive, 'address' => "{$dropoffName}, {$destination}"]],
        ];
    }
}
