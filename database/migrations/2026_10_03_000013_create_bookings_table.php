<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 10)->unique()->comment('Format RID-XXXXXX, 6 karakter A-Z0-9 uppercase');
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete(); // null = guest checkout
            $table->foreignId('schedule_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('pickup_stop_id')->constrained('schedule_stops')->restrictOnDelete();
            $table->foreignId('dropoff_stop_id')->constrained('schedule_stops')->restrictOnDelete();
            $table->foreignId('promo_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('contact_name', 100);
            $table->string('contact_email', 150);
            $table->string('contact_phone', 20)->comment('Dinormalisasi ke format 08xxxxxxxxxx');
            // Snapshot harga saat pemesanan (Rupiah) - tidak berubah walau harga jadwal/promo berubah.
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('service_fee');
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('total_amount')->comment('subtotal + service_fee - discount_amount');
            $table->string('status', 20)->default('pending_payment')->comment('pending_payment | paid | expired | cancelled | completed');
            $table->dateTime('expires_at')->comment('Batas bayar = created_at + 30 menit');
            $table->timestamps();
            // Scheduler: cari booking pending yang sudah lewat expires_at.
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
