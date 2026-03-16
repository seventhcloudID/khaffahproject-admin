<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('la_umrah_hotel_t', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotel_m')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('la_umrah_hotel_t');
    }
};
