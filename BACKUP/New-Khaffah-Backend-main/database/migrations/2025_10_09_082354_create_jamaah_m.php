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
        Schema::create('jamaah_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->foreignId('akun_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->string('nomor_identitas')->nullable();
            $table->string('nomor_passpor')->nullable();
            $table->timestamps();

            $table->index(['akun_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jamaah_m');
    }
};
