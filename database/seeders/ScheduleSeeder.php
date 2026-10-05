<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Rute populer + jadwal semua rute populer untuk 7 hari ke depan (relatif ke tanggal seed).
 * Jadwal Jakarta → Bandung besok di-insert paling awal sehingga ber-id 1..8 = id trip dummy
 * di PageController (hanya berlaku pada DB fresh; seeder lain tetap memakai lookup, bukan id).
 */
class ScheduleSeeder extends Seeder
{
    public const DAYS_AHEAD = 7;

    /** [asal, tujuan, estimasi durasi menit, harga mulai dari]. */
    private const ROUTES = [
        ['Jakarta', 'Bandung', 180, 110000], ['Surabaya', 'Malang', 120, 85000],
        ['Yogyakarta', 'Semarang', 210, 95000], ['Jakarta', 'Cirebon', 240, 140000],
        ['Bandung', 'Tasikmalaya', 180, 90000], ['Solo', 'Yogyakarta', 90, 60000],
    ];

    /**
     * Jadwal harian per rute: [plat kendaraan, jam berangkat, jam tiba, harga].
     * Jakarta → Bandung sama dengan trip dummy 1..8 (urutan = id trip).
     */
    private const TRIPS = [
        'Jakarta-Bandung' => [
            ['B 7012 NTA', '05:00', '08:15', 150000],
            ['D 7731 PS', '06:30', '09:45', 125000],
            ['B 7455 JT', '08:00', '11:30', 110000],
            ['AB 7108 ME', '10:15', '13:15', 185000],
            ['B 7013 NTA', '13:00', '16:20', 150000],
            ['B 7999 ST', '15:30', '19:00', 135000],
            ['D 7732 PS', '18:45', '22:00', 125000],
            ['AB 7109 ME', '21:00', '00:10', 195000],
        ],
        'Surabaya-Malang' => [
            ['B 7455 JT', '06:00', '08:00', 85000],
            ['AB 7108 ME', '11:00', '13:10', 120000],
            ['B 7999 ST', '17:00', '19:00', 100000],
        ],
        'Yogyakarta-Semarang' => [
            ['AB 7109 ME', '07:00', '10:30', 130000],
            ['B 7455 JT', '13:30', '17:00', 95000],
            ['D 7731 PS', '19:00', '22:30', 105000],
        ],
        'Jakarta-Cirebon' => [
            ['B 7012 NTA', '06:00', '10:00', 160000],
            ['B 7999 ST', '09:30', '13:30', 140000],
            ['B 7013 NTA', '16:00', '20:00', 160000],
        ],
        'Bandung-Tasikmalaya' => [
            ['D 7731 PS', '07:30', '10:30', 90000],
            ['D 7732 PS', '12:00', '15:00', 90000],
            ['B 7455 JT', '16:30', '19:30', 95000],
        ],
        'Solo-Yogyakarta' => [
            ['AB 7108 ME', '06:30', '08:00', 75000],
            ['B 7013 NTA', '10:00', '11:30', 60000],
            ['AB 7109 ME', '14:00', '15:30', 75000],
            ['B 7012 NTA', '18:00', '19:30', 60000],
        ],
    ];

    /** Selisih menit titik jemput ke-n dari jam berangkat (titik antar dihitung mundur dari jam tiba). */
    private const STOP_GAP_MINUTES = [0, 20, 45];

    public function run(): void
    {
        $now = now();
        $stamp = ['created_at' => $now, 'updated_at' => $now];
        $cityId = DB::table('cities')->pluck('id', 'name');
        $vehicleId = DB::table('vehicles')->pluck('id', 'plate_number');
        // Titik per kota, urut id (urutan insert di MasterDataSeeder).
        $locationIdsByCity = DB::table('locations')->orderBy('id')->get(['id', 'city_id'])
            ->groupBy('city_id')->map(fn ($rows) => $rows->pluck('id')->all());

        DB::table('routes')->insert(array_map(fn ($row) => [
            'origin_city_id' => $cityId[$row[0]], 'destination_city_id' => $cityId[$row[1]],
            'estimated_duration_minutes' => $row[2], 'base_price' => $row[3], 'is_popular' => true,
        ] + $stamp, self::ROUTES));

        $routes = DB::table('routes')->get(['id', 'origin_city_id', 'destination_city_id'])
            ->keyBy(fn ($route) => "{$route->origin_city_id}-{$route->destination_city_id}");

        // Hari dulu, lalu rute: jadwal Jakarta → Bandung besok selalu ter-insert pertama.
        for ($day = 1; $day <= self::DAYS_AHEAD; $day++) {
            $date = Carbon::today()->addDays($day);

            foreach (self::TRIPS as $routeKey => $trips) {
                [$origin, $destination] = explode('-', $routeKey);
                $route = $routes["{$cityId[$origin]}-{$cityId[$destination]}"];
                $pickupLocationIds = $locationIdsByCity[$route->origin_city_id];
                $dropoffLocationIds = $locationIdsByCity[$route->destination_city_id];

                foreach ($trips as [$plateNumber, $depart, $arrive, $price]) {
                    $departureAt = $date->copy()->setTimeFromTimeString($depart);
                    $arrivalAt = $date->copy()->setTimeFromTimeString($arrive);
                    if ($arrivalAt->lessThan($departureAt)) {
                        $arrivalAt->addDay();
                    }

                    $scheduleId = DB::table('schedules')->insertGetId([
                        'route_id' => $route->id, 'vehicle_id' => $vehicleId[$plateNumber],
                        'departure_at' => $departureAt, 'arrival_at' => $arrivalAt,
                        'price' => $price, 'status' => 'scheduled',
                    ] + $stamp);

                    DB::table('schedule_stops')->insert([
                        ...$this->stops($scheduleId, 'pickup', $pickupLocationIds, $departureAt, $stamp),
                        ...$this->stops($scheduleId, 'dropoff', $dropoffLocationIds, $arrivalAt, $stamp),
                    ]);
                }
            }
        }
    }

    /**
     * Pickup: titik pertama tepat jam berangkat, berikutnya +20/+45 menit.
     * Dropoff: titik terakhir tepat jam tiba, sebelumnya −20/−45 menit.
     *
     * @param  list<int>  $locationIds
     * @return list<array<string, mixed>>
     */
    private function stops(int $scheduleId, string $type, array $locationIds, Carbon $anchor, array $stamp): array
    {
        $count = count($locationIds);

        return array_map(function ($locationId, $index) use ($scheduleId, $type, $anchor, $count, $stamp) {
            $stopAt = $type === 'pickup'
                ? $anchor->copy()->addMinutes(self::STOP_GAP_MINUTES[$index])
                : $anchor->copy()->subMinutes(self::STOP_GAP_MINUTES[$count - 1 - $index]);

            return [
                'schedule_id' => $scheduleId, 'location_id' => $locationId,
                'type' => $type, 'stop_at' => $stopAt, 'sort_order' => $index + 1,
            ] + $stamp;
        }, $locationIds, array_keys($locationIds));
    }
}
