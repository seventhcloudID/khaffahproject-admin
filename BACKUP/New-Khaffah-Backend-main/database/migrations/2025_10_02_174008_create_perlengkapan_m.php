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
        Schema::create('perlengkapan_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('nama_perlengkapan', 150);
            $table->text('deskripsi')->nullable();
            $table->foreignId('jenis_perlengkapan_id')->constrained('jenis_perlengkapan_m')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perlengkapan_m');
    }
};
