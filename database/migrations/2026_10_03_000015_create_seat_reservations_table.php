<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seat_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->string('seat_number', 5);
            $table->foreignId('booking_id')->nullable()->index()->comment('null selama status held (belum jadi booking)')->constrained()->cascadeOnDelete();
            $table->string('hold_token', 64)->nullable()->index()->comment('Token sesi pemilik hold sebelum booking dibuat');
            $table->string('status', 10)->default('held')->comment('held | booked');
            $table->dateTime('held_until')->nullable()->index()->comment('Wajib saat held; null saat booked (kedaluwarsa ikut bookings.expires_at)');
            $table->timestamps();
            // Kunci anti double-booking: kursi yang dilepas DIHAPUS barisnya, jadi unique ini cukup.
            $table->unique(['schedule_id', 'seat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_reservations');
    }
};
