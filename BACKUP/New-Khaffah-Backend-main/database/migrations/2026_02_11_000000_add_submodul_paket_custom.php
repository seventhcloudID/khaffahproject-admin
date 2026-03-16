<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambah submodul "Paket Request" di menu Paket (admin) - untuk support halaman product-request.
     */
    public function up(): void
    {
        $paketModulId = DB::table('modul_aplikasi_s')->where('nama_modul', 'Paket')->value('id');
        if (!$paketModulId) {
            return;
        }

        $oldRecord = DB::table('sub_modul_aplikasi_s')
            ->where('modul_id', $paketModulId)
            ->where('url', '/Paket/Master-Custom')
            ->first();

        if ($oldRecord) {
            DB::table('sub_modul_aplikasi_s')
                ->where('id', $oldRecord->id)
                ->update([
                    'nama_sub_modul' => 'Paket Request',
                    'url' => '/paket-request',
                    'updated_at' => now(),
                ]);
            return;
        }

        $exists = DB::table('sub_modul_aplikasi_s')
            ->where('modul_id', $paketModulId)
            ->where('url', '/paket-request')
            ->exists();

        if (!$exists) {
            DB::table('sub_modul_aplikasi_s')->insert([
                'modul_id' => $paketModulId,
                'is_active' => true,
                'urutan' => 4,
                'nama_sub_modul' => 'Paket Request',
                'url' => '/paket-request',
                'fa_icon_class' => 'fa-solid fa-puzzle-piece',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sub_modul_aplikasi_s')
            ->where('url', '/paket-request')
            ->where('nama_sub_modul', 'Paket Request')
            ->delete();
    }
};
