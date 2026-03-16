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
        Schema::create('kecamatan_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            // relasi ke provinsi
            $table->foreignId('kota_id')
                  ->constrained('kota_m')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->string('kode')->nullable()->index()->comment('kode kecamatan (opsional)');
            $table->string('nama_kecamatan')->index();
            $table->string('slug')->nullable()->unique();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kota_id', 'nama_kecamatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kecamatan_m');
    }
};
