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
        Schema::create('icon_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);

            $table->string('nama_icon', 100); // nama alias untuk ditampilkan atau dicari
            $table->string('kode', 100)->unique()->comment('kode unik, misal "airplane", "hotel", "meal"');

            // bisa simpan salah satu atau keduanya:
            $table->string('url')->nullable()->comment('URL ke file SVG/PNG di public/icon/');
            $table->string('class')->nullable()->comment('class icon, misal lucide-airplane atau fa-solid fa-bed');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('icon_m');
    }
};
