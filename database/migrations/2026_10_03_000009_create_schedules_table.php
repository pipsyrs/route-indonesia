<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->index()->constrained()->restrictOnDelete(); // operator diambil dari vehicles.operator_id
            $table->dateTime('departure_at')->comment('Waktu lokal (Asia/Jakarta)');
            $table->dateTime('arrival_at')->comment('Bisa lewat tengah malam; durasi = arrival_at - departure_at');
            $table->unsignedInteger('price')->comment('Rupiah per kursi');
            $table->string('status', 20)->default('scheduled')->comment('scheduled | departed | cancelled');
            $table->timestamps();
            $table->softDeletes();
            // Query utama pencarian: route + rentang tanggal keberangkatan.
            $table->index(['route_id', 'departure_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
