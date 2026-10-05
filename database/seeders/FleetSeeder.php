<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Operator, tipe kendaraan (+ fasilitas & denah kursi), dan armada.
 */
class FleetSeeder extends Seeder
{
    /**
     * Denah 14 kursi (sama dengan PageController::seatMap()), dinormalisasi 4 kolom per baris.
     * "D" = sopir, null = lorong/kosong, string = nomor kursi.
     */
    public const SEAT_LAYOUT_14 = [
        ['D', null, null, '1'],
        ['2', null, '3', '4'],
        ['5', null, '6', '7'],
        ['8', null, '9', '10'],
        ['11', '12', '13', '14'],
    ];

    public function run(): void
    {
        $now = now();
        $stamp = ['created_at' => $now, 'updated_at' => $now];

        $operators = [
            ['Nusa Trans', 4.8, 1250], ['Parahyangan Shuttle', 4.6, 980], ['Jaya Travel', 4.3, 410],
            ['Merapi Express', 4.9, 1530], ['Samudra Trans', 4.5, 620],
        ];
        DB::table('operators')->insert(array_map(fn ($row) => [
            'name' => $row[0], 'slug' => Str::slug($row[0]), 'rating_average' => $row[1],
            'rating_count' => $row[2], 'is_verified' => true,
        ] + $stamp, $operators));
        $operatorId = DB::table('operators')->pluck('id', 'name');

        // Semua tipe memakai denah 14 kursi agar selaras dengan data dummy frontend.
        $vehicleTypes = [
            'Hiace Premio' => ['ac', 'wifi', 'usb'],
            'Elf Long' => ['ac', 'usb'],
            'Hiace Commuter' => ['ac'],
            'Hiace Premio Luxury' => ['ac', 'wifi', 'usb', 'snack', 'reclining'],
            'Big Bus Executive' => ['ac', 'wifi', 'toilet', 'reclining'],
        ];
        $facilityId = DB::table('facilities')->pluck('id', 'code');
        foreach ($vehicleTypes as $name => $facilities) {
            $typeId = DB::table('vehicle_types')->insertGetId([
                'name' => $name, 'capacity' => 14, 'seat_layout' => json_encode(self::SEAT_LAYOUT_14),
            ] + $stamp);
            DB::table('facility_vehicle_type')->insert(array_map(
                fn ($code) => ['vehicle_type_id' => $typeId, 'facility_id' => $facilityId[$code]],
                $facilities,
            ));
        }
        $typeId = DB::table('vehicle_types')->pluck('id', 'name');

        // Satu kendaraan per jadwal dummy (urutan = id trip 1..8).
        $vehicles = [
            ['Nusa Trans', 'Hiace Premio', 'B 7012 NTA'],
            ['Parahyangan Shuttle', 'Elf Long', 'D 7731 PS'],
            ['Jaya Travel', 'Hiace Commuter', 'B 7455 JT'],
            ['Merapi Express', 'Hiace Premio Luxury', 'AB 7108 ME'],
            ['Nusa Trans', 'Hiace Premio', 'B 7013 NTA'],
            ['Samudra Trans', 'Big Bus Executive', 'B 7999 ST'],
            ['Parahyangan Shuttle', 'Elf Long', 'D 7732 PS'],
            ['Merapi Express', 'Hiace Premio Luxury', 'AB 7109 ME'],
        ];
        DB::table('vehicles')->insert(array_map(fn ($row) => [
            'operator_id' => $operatorId[$row[0]], 'vehicle_type_id' => $typeId[$row[1]], 'plate_number' => $row[2],
        ] + $stamp, $vehicles));
    }
}
