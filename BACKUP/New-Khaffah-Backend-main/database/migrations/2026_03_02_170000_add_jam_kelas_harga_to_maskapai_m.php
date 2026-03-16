<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maskapai_m', function (Blueprint $table) {
            $table->time('jam_keberangkatan')->nullable()->after('logo_url');
            $table->time('jam_sampai')->nullable()->after('jam_keberangkatan');
            $table->foreignId('kelas_penerbangan_id')->nullable()->after('jam_sampai')->constrained('kelas_penerbangan_m')->nullOnDelete();
            $table->decimal('harga_tiket_per_orang', 14, 2)->nullable()->after('kelas_penerbangan_id');
        });
    }

    public function down(): void
    {
        Schema::table('maskapai_m', function (Blueprint $table) {
            $table->dropForeign(['kelas_penerbangan_id']);
            $table->dropColumn(['jam_keberangkatan', 'jam_sampai', 'kelas_penerbangan_id', 'harga_tiket_per_orang']);
        });
    }
};
