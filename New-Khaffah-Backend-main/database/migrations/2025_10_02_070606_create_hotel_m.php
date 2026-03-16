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
        Schema::create('hotel_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('nama_hotel', 255);
            $table->foreignId('kota_id')
                ->constrained('kota_m')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->string('jarak_ke_masjid', 100);
            $table->decimal('bintang', 2, 1)->nullable();
            $table->text('alamat')->nullable();
            $table->time('jam_checkin_mulai')->nullable();
            $table->time('jam_checkin_berakhir')->nullable();
            $table->time('jam_checkout_mulai')->nullable();
            $table->time('jam_checkout_berakhir')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_umrah_keberangkatan_t');
    }
};
