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
        Schema::create('banner_m', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 255);
            $table->string('teks', 500)->nullable();
            $table->string('gambar', 500)->nullable();
            $table->string('lokasi', 100)->default('home');
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->boolean('is_aktif')->default(true);
            $table->string('link', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_m');
    }
};
