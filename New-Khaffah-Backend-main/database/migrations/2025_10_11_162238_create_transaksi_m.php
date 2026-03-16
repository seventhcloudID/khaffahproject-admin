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
        Schema::create('transaksi_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);

            // Data pemesan
            $table->foreignId('gelar_id')->nullable()->constrained('gelar_m')->nullOnDelete();
            $table->string('nama_lengkap', 255);
            $table->string('no_whatsapp', 20);
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi_m')->nullOnDelete();
            $table->foreignId('kota_id')->nullable()->constrained('kota_m')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan_m')->nullOnDelete();
            $table->text('alamat_lengkap')->nullable();
            $table->text('deskripsi')->nullable();

            // Master jenis transaksi (haji / umrah / edukasi / dsb)
            $table->foreignId('jenis_transaksi_id')->constrained('jenis_transaksi_m')->cascadeOnDelete();

            // Produk snapshot
            $table->unsignedBigInteger('produk_id')->nullable();
            $table->foreignId('keberangkatan_id')->nullable()->constrained('paket_umrah_keberangkatan_t')->nullOnDelete();
            $table->json('snapshot_produk')->nullable();

            // Data jamaah tambahan
            $table->json('jamaah_data')->nullable()->comment('berisi list peserta/jamaah dalam transaksi ini');

            // Pembayaran
            $table->string('kode_transaksi', 50)->nullable()->unique();
            $table->boolean('is_with_payment')->default(true);
            $table->decimal('total_biaya', 15, 2)->nullable();

            // Status
            $table->foreignId('status_pembayaran_id')->nullable()->constrained('status_pembayaran_m')->nullOnDelete();
            $table->foreignId('status_transaksi_id')->nullable()->constrained('status_transaksi_m')->nullOnDelete();

            // Nomor pembayaran (untuk referensi moota atau validasi)
            $table->string('nomor_pembayaran', 30)->unique()->nullable()
                ->comment('kode referensi umum untuk setiap transaksi, digunakan saat matching moota');

            $table->timestamp('tanggal_transaksi')->useCurrent();
            $table->timestamps();

            $table->index(['kode_transaksi', 'nomor_pembayaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_m');
    }
};