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
        Schema::create('mitra_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // data identitas mitra
            $table->string('nama_lengkap');
            $table->string('nik', 50);

            // alamat
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi_m')->nullOnDelete();
            $table->foreignId('kota_id')->nullable()->constrained('kota_m')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan_m')->nullOnDelete();
            $table->text('alamat_lengkap');

            // legal usaha
            $table->string('nomor_ijin_usaha', 100)->nullable();
            $table->date('masa_berlaku_ijin_usaha')->nullable();

            //status
            $table->foreignId('status_id')->nullable()->constrained('status_m')->nullOnDelete();

            // audit verifikasi
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan')->nullable();
            $table->text('alasan_ditolak')->nullable();

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_m');
    }
};
