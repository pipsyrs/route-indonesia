<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('Selalu UPPERCASE');
            $table->string('title', 150);
            $table->string('description')->nullable();
            $table->string('discount_type', 20)->comment('percent | fixed | cashback');
            $table->unsignedInteger('discount_value')->comment('percent: 1-100; fixed/cashback: Rupiah');
            $table->unsignedInteger('max_discount')->nullable()->comment('Batas potongan Rupiah untuk tipe percent');
            $table->unsignedInteger('min_transaction')->default(0)->comment('Minimal subtotal Rupiah');
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until');
            $table->json('rules')->nullable()->comment('{"new_customer_only":bool,"departure_weekdays":[ISO 1-7],"payment_method_codes":[...]}');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
