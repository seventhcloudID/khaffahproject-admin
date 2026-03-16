<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_visa_m', function (Blueprint $table) {
            $table->id();
            $table->string('nama_layanan', 150);
            $table->string('slug', 80)->unique()->comment('Identifier untuk frontend, e.g. visa, visa-transportasi');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('harga')->default(0)->comment('Harga per pax (IDR)');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_visa_m');
    }
};
