<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // mis. "Hiace Premio"
            $table->unsignedTinyInteger('capacity')->comment('Jumlah kursi penumpang = jumlah sel bertipe string nomor di seat_layout');
            $table->json('seat_layout')->comment('Array baris, tiap baris 4 sel: "D" = sopir, null = lorong/kosong, string = nomor kursi');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
