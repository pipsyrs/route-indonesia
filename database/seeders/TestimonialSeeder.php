<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Testimoni kurasi tambahan (booking_id null) agar slider review di home punya 3 slide.
 * Idempotent: dikunci pada name + city_name.
 */
class TestimonialSeeder extends Seeder
{
    private const TESTIMONIALS = [
        ['name' => 'Arif Setiawan', 'city_name' => 'Surabaya', 'rating' => 5, 'body' => 'Sopirnya ramah dan nyetirnya halus. Perjalanan Surabaya–Malang jadi nyaman banget.'],
        ['name' => 'Nur Aini Rahma', 'city_name' => 'Yogyakarta', 'rating' => 5, 'body' => 'Harga di aplikasi sama persis dengan yang dibayar, tidak ada biaya tersembunyi.'],
        ['name' => 'Fajar Ramadhan', 'city_name' => 'Jakarta', 'rating' => 4, 'body' => 'Reschedule jadwal gampang lewat CS, responsnya cepat. Armadanya juga masih baru.'],
        ['name' => 'Putri Ayuningtyas', 'city_name' => 'Solo', 'rating' => 5, 'body' => 'Notifikasi pengingat keberangkatan sangat membantu, jadi tidak pernah telat ke pool.'],
        ['name' => 'Hendra Wijaya', 'city_name' => 'Cirebon', 'rating' => 4, 'body' => 'AC dingin, kursi lega, dan ada colokan USB. Cocok buat perjalanan kerja.'],
        ['name' => 'Maya Anggraini', 'city_name' => 'Denpasar', 'rating' => 5, 'body' => 'Pertama kali coba langsung puas. Proses pesan sampai naik mobil semuanya lancar.'],
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::TESTIMONIALS as $row) {
            $exists = DB::table('testimonials')
                ->where('name', $row['name'])
                ->where('city_name', $row['city_name'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('testimonials')->insert($row + [
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
