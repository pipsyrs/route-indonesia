<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_city_id')->constrained('cities')->restrictOnDelete();
            $table->foreignId('destination_city_id')->index()->constrained('cities')->restrictOnDelete();
            $table->unsignedSmallInteger('estimated_duration_minutes');
            $table->unsignedInteger('base_price')->comment('Rupiah; harga "mulai dari" untuk kartu rute populer');
            $table->boolean('is_popular')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
            // Satu baris per pasangan asal-tujuan; juga index pencarian berdasarkan asal.
            $table->unique(['origin_city_id', 'destination_city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
