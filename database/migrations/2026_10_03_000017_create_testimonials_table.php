<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete(); // null untuk testimoni kurasi/dummy
            $table->string('name', 100);
            $table->string('city_name', 100)->comment('Teks bebas kota asal pemberi testimoni');
            $table->unsignedTinyInteger('rating')->comment('1-5');
            $table->text('body');
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
