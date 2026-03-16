<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subrole_modul_aplikasi_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_role_id')->constrained('subrole_m')->cascadeOnDelete();
            $table->foreignId('modul_id')->constrained('modul_aplikasi_s')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subrole_modul_aplikasi_s');
    }
};
