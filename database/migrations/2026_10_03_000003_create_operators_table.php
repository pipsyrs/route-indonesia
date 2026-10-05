<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->string('logo_path')->nullable(); // path relatif di storage/public
            $table->string('phone', 20)->nullable();
            $table->decimal('rating_average', 2, 1)->default(0)->comment('Agregat 0.0-5.0, diperbarui saat ulasan masuk');
            $table->unsignedInteger('rating_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
