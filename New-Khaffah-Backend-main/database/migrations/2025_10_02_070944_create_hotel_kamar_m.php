<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_kamar_m', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotel_m')->onDelete('cascade');
            $table->foreignId('tipe_kamar_id')->constrained('tipe_kamar_m')->onDelete('cascade');
            $table->string('nama_kamar', 255);
            $table->integer('kapasitas')->default(1)->comment('jumlah orang per kamar');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hotel_id', 'tipe_kamar_id'], 'uniq_hotel_tipe_kamar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_kamar_m');
    }
};