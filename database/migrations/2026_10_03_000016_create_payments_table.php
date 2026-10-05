<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->index()->constrained()->restrictOnDelete();
            $table->unsignedInteger('amount')->comment('Rupiah = bookings.total_amount + payment_methods.fee');
            $table->string('status', 20)->default('pending')->comment('pending | paid | failed | expired');
            $table->string('virtual_account_number', 30)->nullable();
            $table->text('qr_string')->nullable();
            $table->string('deeplink_url', 500)->nullable();
            $table->string('gateway_reference', 100)->nullable()->unique()->comment('ID transaksi dari payment gateway (untuk webhook)');
            $table->dateTime('expires_at');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
