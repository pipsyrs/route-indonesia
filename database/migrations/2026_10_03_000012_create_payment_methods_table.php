<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('va_bca | va_mandiri | gopay | qris | ...');
            $table->string('name', 100);
            $table->string('short_name', 10); // label logo ringkas, mis. "BCA"
            $table->string('group_name', 30)->comment('Virtual Account | E-Wallet | QRIS');
            $table->unsignedInteger('fee')->default(0)->comment('Biaya tambahan Rupiah');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'group_name', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
