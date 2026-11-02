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

    /** @return list<array<string, mixed>> */
    public static function trips(): array
    {
        // [id, operator, vehicle, origin, destination, depart, menit, harga, sisa kursi, rating, jemput, antar]
        $rows = [
            [1, 'Cipaganti', 'Hiace Premio', 'Jakarta', 'Bandung', '07:00', 195, 150000, 7, 4.7, 'Pool Cawang', 'Pool Pasteur'],
            [2, 'Daytrans', 'Hiace Commuter', 'Jakarta', 'Bandung', '09:30', 180, 135000, 4, 4.5, 'Pool Blok M', 'Pool Dipatiukur'],
            [3, 'Baraya', 'Elf Long', 'Jakarta', 'Bandung', '13:00', 210, 110000, 10, 4.2, 'Pool Grogol', 'Pool Surapati'],
            [4, 'Xtrans', 'Hiace Premio', 'Bandung', 'Jakarta', '06:00', 200, 150000, 3, 4.8, 'Pool Cihampelas', 'Pool Kuningan'],
            [5, 'Joglosemar', 'Executive Bus', 'Jakarta', 'Yogyakarta', '19:00', 540, 285000, 12, 4.6, 'Terminal Lebak Bulus', 'Pool Jombor'],
            [6, 'Rajawali', 'Hiace Premio', 'Yogyakarta', 'Semarang', '08:00', 210, 120000, 5, 4.4, 'Pool Janti', 'Pool Banyumanik'],
            [7, 'Nusantara', 'Executive Bus', 'Semarang', 'Surabaya', '20:30', 420, 230000, 9, 4.3, 'Terminal Terboyo', 'Terminal Bungurasih'],
            [8, 'Tirtaja', 'Hiace Commuter', 'Surabaya', 'Malang', '10:00', 150, 95000, 6, 4.5, 'Pool Darmo', 'Pool Dinoyo'],
            [9, 'Golden Bird', 'Elf Long', 'Malang', 'Surabaya', '15:00', 160, 90000, 2, 4.1, 'Pool Sukun', 'Pool Gubeng'],
            [10, 'Sumber Alam', 'Executive Bus', 'Yogyakarta', 'Jakarta', '18:00', 560, 290000, 8, 4.7, 'Pool Ringroad', 'Pool Pulogadung'],
        ];

        // Nama rute induk per trip (sub-rute), dipakai judul halaman detail.
        $routeNames = [];
        foreach (self::routes() as $route) {
            foreach ($route['trip_ids'] as $tripId) {
                $routeNames[$tripId] ??= $route['name'];
            }
        }

        return array_map(
            fn (array $row) => self::makeTrip(...$row) + ['route_name' => $routeNames[$row[0]] ?? null],
            $rows,
        );
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

    /** @return list<array{slug: string, name: string, trip_ids: list<int>}> rute bernama, tiap trip = sub-rute */
    public static function routes(): array
    {
        return [
            ['slug' => 'papandayan', 'name' => 'Papandayan', 'trip_ids' => [1, 2, 3, 4]],
            ['slug' => 'bromo', 'name' => 'Bromo', 'trip_ids' => [8, 9]],
            ['slug' => 'prambanan', 'name' => 'Prambanan', 'trip_ids' => [5, 10]],
            ['slug' => 'karimunjawa', 'name' => 'Karimunjawa', 'trip_ids' => [6, 7]],
        ];
    }

    /** @return array{slug: string, name: string, trip_ids: list<int>}|null */
    public static function findRoute(string $slug): ?array
    {
        foreach (self::routes() as $route) {
            if ($route['slug'] === $slug) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @param  array{trip_ids: list<int>}  $route
     * @return list<array<string, mixed>>
     */
    public static function routeTrips(array $route): array
    {
        return array_values(array_filter(
            self::trips(),
            fn (array $trip) => in_array($trip['id'], $route['trip_ids'], true),
        ));
    }

    /** @param  array{trip_ids: list<int>}  $route  harga sub-rute termurah */
    public static function routeMinPrice(array $route): int
    {
        return min(array_column(self::routeTrips($route), 'price'));
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

    /** Static 13-seat layout for every trip: 'C' = crew, 'D' = driver, null = aisle, digits = seat. */
    public const SEAT_LAYOUT = [
        ['C', null, null, 'D'],
        [null, '1', '2', '3'],
        ['4', null, '5', '6'],
        ['7', null, '8', '9'],
        ['10', '11', '12', '13'],
    ];

    /** @return list<list<string|null>> */
    public static function seatLayout(): array
    {
        return self::SEAT_LAYOUT;
    }

    /**
     * @param  list<list<string|null>>  $layout
     * @return list<string> kode kursi, urut baris
     */
    public static function seatCodes(array $layout): array
    {
        return array_values(array_filter(
            array_merge(...$layout),
            fn (?string $cell) => $cell !== null && preg_match('/^[0-9]{1,2}$/', $cell) === 1,
        ));
    }

    /** @return list<string> kursi terisi, deterministik per trip+tanggal, urut numerik */
    public static function occupiedSeats(int $tripId, string $date): array
    {
        $trip = self::findTrip($tripId);
        if ($trip === null) {
            return [];
        }

        $seats = self::seatCodes(self::seatLayout());
        $count = max(0, count($seats) - $trip['seats_left']);

        // md5 dulu: crc32 linear, tanpa itu pola kursi nyaris sama antar tanggal.
        $rank = fn (string $seat) => crc32(md5("{$tripId}|{$date}|{$seat}"));
        usort($seats, fn (string $a, string $b) => $rank($a) <=> $rank($b) ?: strnatcmp($a, $b));
        $occupied = array_slice($seats, 0, $count);
        usort($occupied, 'strnatcmp');

        return $occupied;
    }

    /**
     * @param  array<string, mixed>  $trip
     * @return array{layout: list<list<string|null>>, occupied: list<string>}
     */
    public static function seatMap(array $trip, string $date): array
    {
        return [
            'layout' => self::seatLayout(),
            'occupied' => self::occupiedSeats($trip['id'], $date),
        ];
    }

    /** @return list<string> 38 provinsi, urut abjad */
    public static function provinces(): array
    {
        return [
            'Aceh', 'Bali', 'Banten', 'Bengkulu', 'DI Yogyakarta', 'DKI Jakarta', 'Gorontalo', 'Jambi',
            'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'Kalimantan Barat', 'Kalimantan Selatan',
            'Kalimantan Tengah', 'Kalimantan Timur', 'Kalimantan Utara', 'Kepulauan Bangka Belitung',
            'Kepulauan Riau', 'Lampung', 'Maluku', 'Maluku Utara', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur',
            'Papua', 'Papua Barat', 'Papua Barat Daya', 'Papua Pegunungan', 'Papua Selatan', 'Papua Tengah',
            'Riau', 'Sulawesi Barat', 'Sulawesi Selatan', 'Sulawesi Tengah', 'Sulawesi Tenggara',
            'Sulawesi Utara', 'Sumatera Barat', 'Sumatera Selatan', 'Sumatera Utara',
        ];
    }

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
        float $rating,
        string $pickupName,
        string $dropoffName,
    ): array {
        [$hour, $minute] = array_map('intval', explode(':', $depart));
        $arriveTotal = $hour * 60 + $minute + $minutes;
        $arrive = sprintf('%02d:%02d', intdiv($arriveTotal, 60) % 24, $arriveTotal % 60);

        $itinerary = [['title' => $pickupName, 'description' => "Titik kumpul di {$pickupName}, {$origin}. Mohon tiba 15 menit lebih awal untuk pengecekan tiket dan bagasi."]];
        if ($minutes >= 300) {
            $itinerary[] = ['title' => 'Rest area', 'description' => 'Berhenti sekitar 30 menit untuk istirahat, makan, dan ke toilet sebelum perjalanan dilanjutkan.'];
        }
        $itinerary[] = ['title' => $dropoffName, 'description' => "Perjalanan berakhir di {$dropoffName}, {$destination}. Kru membantu menurunkan bagasi Anda."];

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
            'rating' => $rating,
            // Dummy deterministik: tiap trip ke-3 private, sisanya open trip.
            'type' => $id % 3 === 0 ? 'private' : 'open',
            'description' => "Perjalanan bersama {$operator}. Kabin dibersihkan sebelum setiap keberangkatan dan bagasi maksimal 20 kg per penumpang.",
            'itinerary' => $itinerary,
            'pickups' => [['id' => $id * 10 + 1, 'name' => $pickupName, 'time' => $depart, 'address' => "{$pickupName}, {$origin}"]],
            'dropoffs' => [['id' => $id * 10 + 2, 'name' => $dropoffName, 'time' => $arrive, 'address' => "{$dropoffName}, {$destination}"]],
        ];
    }
}
