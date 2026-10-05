<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_type_id')->index()->constrained()->restrictOnDelete();
            $table->string('plate_number', 15)->unique(); // mis. "B 7012 NTA"
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
