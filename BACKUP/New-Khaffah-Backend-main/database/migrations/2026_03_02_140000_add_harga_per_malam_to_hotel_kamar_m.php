<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_kamar_m', function (Blueprint $table) {
            $table->decimal('harga_per_malam', 12, 2)->nullable()->after('kapasitas');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_kamar_m', function (Blueprint $table) {
            $table->dropColumn('harga_per_malam');
        });
    }
};
