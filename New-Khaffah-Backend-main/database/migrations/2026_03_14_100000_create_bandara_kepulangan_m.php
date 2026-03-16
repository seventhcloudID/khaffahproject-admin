<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bandara_kepulangan_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('kode', 10)->comment('Kode IATA bandara tujuan, e.g. JED, MED, TIF');
            $table->string('nama', 255)->comment('Nama bandara / kota tujuan');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bandara_kepulangan_m');
    }
};
