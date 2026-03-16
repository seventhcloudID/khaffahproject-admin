<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_harga_t', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_kamar_id')->constrained('hotel_kamar_m')->onDelete('cascade');
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->decimal('harga_per_malam', 12, 2);
            $table->boolean('is_flat')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hotel_kamar_id', 'periode_mulai', 'periode_selesai'], 'idx_hotel_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_harga_t');
    }
};
