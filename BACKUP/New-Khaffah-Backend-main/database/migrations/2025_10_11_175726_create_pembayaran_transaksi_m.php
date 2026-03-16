<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_transaksi_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);

            $table->foreignId('transaksi_id')
                ->constrained('transaksi_m')
                ->cascadeOnDelete();

            // Identitas pembayaran
            $table->string('nomor_pembayaran', 30)->nullable()->unique()
                ->comment('kode unik pembayaran yang bisa diisi manual atau auto-generate');
            $table->unsignedSmallInteger('kode_unik')->nullable()
                ->comment('kode unik nominal untuk identifikasi mutasi');
            $table->decimal('nominal_asli', 15, 2)->default(0);
            $table->decimal('nominal_transfer', 15, 2)->default(0)
                ->comment('nominal setelah ditambah kode unik');

            // Mutasi dan moota
            $table->string('moota_reference')->nullable()->unique()
                ->comment('ID mutasi dari moota, untuk hindari duplikasi');
            $table->string('bank_pengirim', 100)->nullable();
            $table->string('nama_pengirim', 150)->nullable();
            $table->string('bank_tujuan', 100)->nullable();
            $table->decimal('amount_mutasi', 15, 2)->nullable()
            ->comment('nominal mutasi aktual dari Moota');
            $table->timestamp('tanggal_transfer')->nullable();
            
            // Status pembayaran
            $table->string('status', 20)->default('pending')
            ->comment('pending, matched, verified, rejected, refunded');
            
            // Audit & verifikasi manual
            $table->string('bukti_pembayaran')->nullable();
            $table->boolean('validasi_manual')->default(false);
            $table->foreignId('verified_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();

            // Indexing for fast lookup
            $table->index(['status', 'kode_unik', 'nominal_transfer']);
            $table->index('transaksi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_transaksi_m');
    }
};
