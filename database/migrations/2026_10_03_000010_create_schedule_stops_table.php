<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->index()->constrained()->restrictOnDelete();
            $table->string('type', 10)->comment('pickup | dropoff');
            $table->dateTime('stop_at')->comment('Perkiraan waktu kendaraan tiba di titik ini');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['schedule_id', 'type', 'location_id']);
            $table->index(['schedule_id', 'type', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_stops');
    }
};
