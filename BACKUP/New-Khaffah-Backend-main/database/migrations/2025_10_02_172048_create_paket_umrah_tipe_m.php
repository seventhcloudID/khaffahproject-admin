<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paket_umrah_tipe_m', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_umrah_id')->constrained('paket_umrah_m')->cascadeOnDelete();
            $table->foreignId('tipe_kamar_id')->constrained('tipe_kamar_m')->onDelete('cascade');

            $table->boolean('is_active')->default(true);
            $table->decimal('harga_per_pax', 15, 2);
            $table->integer('kapasitas_total')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_umrah_tipe_m');
    }
};
