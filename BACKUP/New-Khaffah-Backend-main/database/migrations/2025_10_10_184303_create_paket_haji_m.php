<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('paket_haji_m')) {
            return; // Tabel sudah ada (sisa migrasi gagal sebelumnya), lewati agar migrate bisa lanjut
        }

        Schema::create('paket_haji_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);

            // Informasi dasar paket
            $table->string('nama_paket', 255);
            $table->decimal('biaya_per_pax', 15, 2)->comment('Biaya per orang (rupiah)');
            $table->text('deskripsi')->nullable();

            // Akomodasi: array JSON (list hotel per kota). Tidak ada deskripsi per item.
            $table->json('akomodasi')->nullable()->comment('Array akomodasi per destinasi (kota, nama_hotel, rating, jarak, dll)');

            // DESKRIPSI AKOMODASI: hanya 1 teks per paket
            $table->text('deskripsi_akomodasi')->nullable()->comment('Deskripsi umum akomodasi untuk paket ini (1 per paket)');

            // Waktu tunggu (tahun)
            $table->integer('waktu_tunggu_min')->default(5)->comment('Tahun paling cepat');
            $table->integer('waktu_tunggu_max')->default(7)->comment('Tahun paling lama');
            $table->text('deskripsi_waktu_tunggu')->nullable();

            // Fasilitas tambahan: array JSON (item + icon or icon_id)
            $table->json('fasilitas_tambahan')->nullable()->comment('Array fasilitas (nama + icon_id/icon_path)');
            // DESKRIPSI FASILITAS: hanya 1 teks per paket
            $table->text('deskripsi_fasilitas')->nullable()->comment('Deskripsi umum fasilitas untuk paket ini (1 per paket)');

            $table->timestamps();
        });

        // Hanya di PostgreSQL: index GIN dan CHECK constraint (MySQL/MariaDB tidak mendukung GIN)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX paket_haji_akomodasi_gin ON paket_haji_m USING gin (akomodasi);');
            DB::statement('CREATE INDEX paket_haji_fasilitas_gin ON paket_haji_m USING gin (fasilitas_tambahan);');
            DB::statement('ALTER TABLE paket_haji_m ADD CONSTRAINT chk_waktu_tunggu CHECK (waktu_tunggu_min <= waktu_tunggu_max);');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_haji_m');
    }
};
