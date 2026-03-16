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
        Schema::create('modul_aplikasi_s', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->integer('urutan')->default(0);
            $table->string('nama_modul', 255);
            $table->foreignId('icon_id')->nullable()->constrained('icon_m')->nullOnDelete();
            $table->string('fa_icon_class', 255)->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modul_aplikasi_s');
    }
};
