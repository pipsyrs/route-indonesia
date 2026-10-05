<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data master: kota, titik jemput/antar, fasilitas, metode bayar, promo, testimoni.
 * Mencerminkan data dummy di App\Http\Controllers\PageController.
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $stamp = ['created_at' => $now, 'updated_at' => $now];

        $cities = [
            'Jakarta' => 'DKI Jakarta', 'Bandung' => 'Jawa Barat', 'Bogor' => 'Jawa Barat',
            'Cirebon' => 'Jawa Barat', 'Semarang' => 'Jawa Tengah', 'Yogyakarta' => 'DI Yogyakarta',
            'Solo' => 'Jawa Tengah', 'Surabaya' => 'Jawa Timur', 'Malang' => 'Jawa Timur',
            'Denpasar' => 'Bali', 'Purwokerto' => 'Jawa Tengah', 'Tasikmalaya' => 'Jawa Barat',
        ];
        DB::table('cities')->insert(array_map(
            fn ($name, $province) => ['name' => $name, 'slug' => Str::slug($name), 'province' => $province, 'is_active' => true] + $stamp,
            array_keys($cities),
            $cities,
        ));
        $cityId = DB::table('cities')->pluck('id', 'name');

        // Rest Area KM 19 secara administratif di Bekasi; dikaitkan ke Jakarta sebagai kota layanan asal.
        DB::table('locations')->insert([
            ['city_id' => $cityId['Jakarta'], 'name' => 'Pool Cawang', 'address' => 'Jl. MT Haryono No. 12, Jakarta Timur', 'type' => 'pool'] + $stamp,
            ['city_id' => $cityId['Jakarta'], 'name' => 'Pool Kuningan', 'address' => 'Jl. HR Rasuna Said Kav. 5, Jakarta Selatan', 'type' => 'pool'] + $stamp,
            ['city_id' => $cityId['Jakarta'], 'name' => 'Rest Area KM 19', 'address' => 'Tol Jakarta–Cikampek KM 19', 'type' => 'rest_area'] + $stamp,
            ['city_id' => $cityId['Bandung'], 'name' => 'Pool Pasteur', 'address' => 'Jl. Dr. Djunjunan No. 88, Bandung', 'type' => 'pool'] + $stamp,
            ['city_id' => $cityId['Bandung'], 'name' => 'Pool Dipatiukur', 'address' => 'Jl. Dipatiukur No. 45, Bandung', 'type' => 'pool'] + $stamp,
        ]);

        // Dua titik per kota rute populer lain (urutan insert = urutan titik jemput/antar di ScheduleSeeder).
        $otherCities = ['Surabaya', 'Malang', 'Yogyakarta', 'Semarang', 'Cirebon', 'Tasikmalaya', 'Solo'];
        DB::table('locations')->insert(array_merge(...array_map(fn ($city) => [
            ['city_id' => $cityId[$city], 'name' => "Pool {$city} Kota", 'address' => "Pusat Kota {$city}", 'type' => 'pool'] + $stamp,
            ['city_id' => $cityId[$city], 'name' => "Terminal {$city}", 'address' => "Terminal Bus {$city}", 'type' => 'point'] + $stamp,
        ], $otherCities)));

        DB::table('facilities')->insert(array_map(
            fn ($code, $label) => ['code' => $code, 'label' => $label] + $stamp,
            ['ac', 'wifi', 'usb', 'snack', 'reclining', 'toilet'],
            ['AC', 'Wi-Fi', 'Port USB', 'Snack', 'Kursi Rebah', 'Toilet'],
        ));

        $methods = [
            ['va_bca', 'BCA Virtual Account', 'BCA', 'Virtual Account'],
            ['va_mandiri', 'Mandiri Virtual Account', 'MDR', 'Virtual Account'],
            ['va_bni', 'BNI Virtual Account', 'BNI', 'Virtual Account'],
            ['va_bri', 'BRI Virtual Account', 'BRI', 'Virtual Account'],
            ['gopay', 'GoPay', 'GP', 'E-Wallet'],
            ['ovo', 'OVO', 'OVO', 'E-Wallet'],
            ['dana', 'DANA', 'DN', 'E-Wallet'],
            ['shopeepay', 'ShopeePay', 'SP', 'E-Wallet'],
            ['qris', 'QRIS (semua aplikasi pembayaran)', 'QR', 'QRIS'],
        ];
        DB::table('payment_methods')->insert(array_map(fn ($row, $index) => [
            'code' => $row[0], 'name' => $row[1], 'short_name' => $row[2], 'group_name' => $row[3],
            'fee' => 0, 'sort_order' => $index + 1, 'is_active' => true,
        ] + $stamp, $methods, array_keys($methods)));

        DB::table('promos')->insert([
            [
                'code' => 'JALANHEMAT', 'title' => 'Diskon 20% Perjalanan Pertama',
                'description' => 'Maks. potongan Rp 30.000 untuk pengguna baru.',
                'discount_type' => 'percent', 'discount_value' => 20, 'max_discount' => 30000, 'min_transaction' => 0,
                'valid_until' => '2026-12-31 23:59:59', 'rules' => json_encode(['new_customer_only' => true]),
            ] + $stamp,
            [
                'code' => 'WEEKENDMUDIK', 'title' => 'Cashback Rp 25.000 Akhir Pekan',
                'description' => 'Berlaku untuk keberangkatan Jumat–Minggu.',
                'discount_type' => 'cashback', 'discount_value' => 25000, 'max_discount' => null, 'min_transaction' => 0,
                'valid_until' => '2026-11-30 23:59:59', 'rules' => json_encode(['departure_weekdays' => [5, 6, 7]]),
            ] + $stamp,
            [
                'code' => 'QRISHEMAT', 'title' => 'Potongan Rp 10.000 via QRIS',
                'description' => 'Min. transaksi Rp 100.000, semua rute.',
                'discount_type' => 'fixed', 'discount_value' => 10000, 'max_discount' => null, 'min_transaction' => 100000,
                'valid_until' => '2026-11-15 23:59:59', 'rules' => json_encode(['payment_method_codes' => ['qris']]),
            ] + $stamp,
        ]);

        DB::table('testimonials')->insert([
            ['name' => 'Rina Kartika', 'city_name' => 'Bandung', 'rating' => 5, 'body' => 'Pesan tiket Jakarta–Bandung cuma 2 menit. Shuttle-nya tepat waktu dan bersih!', 'is_published' => true] + $stamp,
            ['name' => 'Agus Prasetyo', 'city_name' => 'Malang', 'rating' => 5, 'body' => 'Bisa pilih kursi sendiri, jadi selalu dapat dekat jendela. Mantap.', 'is_published' => true] + $stamp,
            ['name' => 'Dewi Lestari', 'city_name' => 'Semarang', 'rating' => 4, 'body' => 'Pembayaran pakai QRIS praktis, e-ticket langsung masuk email.', 'is_published' => true] + $stamp,
        ]);
    }
}
