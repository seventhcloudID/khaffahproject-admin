<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SistemAdmin\ModulAplikasiController;
use App\Http\Controllers\SistemAdmin\DashboardController;
use App\Http\Controllers\Transaksi\TransaksiController;
use App\Http\Controllers\Utility\UtilityController;
use App\Http\Controllers\Mitra\PendaftaranMitraController;
use App\Http\Controllers\Dokumen\DokumenController;
use App\Http\Controllers\Produk\PaketCustomController;
use App\Http\Controllers\Produk\LayananPaketRequestController;
use App\Http\Controllers\Produk\TujuanTambahanController;
use App\Http\Controllers\Produk\PaketEdutripController;
use App\Http\Controllers\Produk\PaketHajiController;
use App\Http\Controllers\Produk\PaketUmrahController;
use App\Http\Controllers\User\UsersController;
use App\Http\Controllers\Setting\PageContentController;
use App\Http\Controllers\Setting\BannerController;
use App\Http\Controllers\Master\MasterHotelController;
use App\Http\Controllers\Master\MasterMaskapaiController;
use App\Http\Controllers\Master\MasterKeberangkatanController;
use App\Http\Controllers\Master\MasterKepulanganController;
use App\Http\Controllers\Master\MasterLevelMitraController;
use App\Http\Controllers\Master\MasterRekeningController;
use App\Http\Controllers\Master\LaUmrahOptionController;
use App\Http\Controllers\Master\MasterVisaController;
use App\Http\Controllers\Master\MasterBadalUmrahController;
use App\Http\Controllers\Master\MasterBadalHajiController;

Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong'
    ]);
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::middleware('role:superadmin')->group(function () {

        Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
            Route::get('/data', 'getDashboardData');
        });

        Route::controller(ModulAplikasiController::class)->group(function () {

            Route::post('/get-modul-dinamis', 'getModulDinamis');
            Route::get('/get-master-modul', 'getMasterModul');
            Route::get('/get-master-sub-modul', 'getSubMasterModul');
        });

        Route::prefix('dokumen')->controller(DokumenController::class)->group(function () {
            Route::get('/{id}/preview', 'preview');
            Route::post('/set-status-dokumen', 'setStatusDokumen');
        });

        Route::prefix('utility')->controller(UtilityController::class)->group(function () {
            Route::get('/dropdown/{table}', 'dropdown');
        });

        Route::prefix('master-hotel')->controller(MasterHotelController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/list', 'list');
            Route::get('/kota-options', 'kotaOptions');
            Route::get('/tipe-kamar-options', 'tipeKamarOptions');
            Route::post('/upload-foto', 'uploadFoto');
            Route::delete('/foto/{id}', 'deleteFoto');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        Route::prefix('master-maskapai')->controller(MasterMaskapaiController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/list', 'list');
            Route::get('/kelas-penerbangan-options', 'kelasPenerbanganOptions');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
            Route::delete('/{id}/permanent', 'destroyPermanent');
        });

        Route::prefix('master-keberangkatan')->controller(MasterKeberangkatanController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/list', 'list');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        Route::prefix('master-kepulangan')->controller(MasterKepulanganController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/list', 'list');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        Route::prefix('master-level-mitra')->controller(MasterLevelMitraController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/list', 'list');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        Route::prefix('master-rekening')->controller(MasterRekeningController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/list', 'list');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        Route::prefix('la-umrah-options')->controller(LaUmrahOptionController::class)->group(function () {
            Route::get('/hotels', 'indexHotels');
            Route::get('/maskapai', 'indexMaskapai');
            Route::get('/master-hotels', 'listMasterHotels');
            Route::get('/master-maskapai', 'listMasterMaskapai');
            Route::post('/hotels', 'storeHotel');
            Route::post('/maskapai', 'storeMaskapai');
            Route::delete('/hotels/{id}', 'destroyHotel');
            Route::delete('/maskapai/{id}', 'destroyMaskapai');
        });

        Route::prefix('master-visa')->controller(MasterVisaController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/list', 'list');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        Route::prefix('master-badal-umrah')->controller(MasterBadalUmrahController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/list', 'list');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        Route::prefix('master-badal-haji')->controller(MasterBadalHajiController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/list', 'list');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        Route::prefix('paket-umrah')->controller(PaketUmrahController::class)->group(function () {
            Route::get('/get-paket-umrah', 'getListPaketUmrah');
            Route::get('/get-paket-umrah/{id}', 'getPaketUmrahId');
            Route::post('/create-paket-umrah', 'createPaketUmrah');
            Route::put('/update-paket-umrah/{id}', 'updatePaketUmrah');
            Route::delete('/delete-paket-umrah/{id}', 'deletePaketUmrah');

            Route::post('/upsert-foto', 'upsertFotoPaket');
        });

        Route::prefix('paket-haji')->controller(PaketHajiController::class)->group(function () {
            Route::get('/get-paket-haji', 'getPaketHaji');
            Route::get('/get-paket-haji/{id}', 'getPaketHajiById');
            Route::post('/create-paket-haji', 'createPaketHaji');
            Route::put('/update-paket-haji/{id}', 'updatePaketHaji');
            Route::delete('/delete-paket-haji/{id}', 'deletePaketHaji');
        });

        Route::prefix('paket-edutrip')->controller(PaketEdutripController::class)->group(function () {
            Route::get('/get-paket-edutrip', 'getPaketEdutrip');
            Route::post('/create-paket-edutrip', 'createPaketEdutrip');
            Route::put('/update-paket-edutrip/{id}', 'updatePaketEdutrip');
            Route::delete('/delete-paket-edutrip/{id}', 'deletePaketEdutrip');
        });

        Route::prefix('paket-custom')->controller(PaketCustomController::class)->group(function () {
            Route::get('/get-paket-custom', 'getPaketCustom');
            Route::post('/create-paket-custom', 'createPaketCustom');
            Route::put('/update-paket-custom/{id}', 'updatePaketCustom');
            Route::delete('/delete-paket-custom/{id}', 'deletePaketCustom');
        });

        Route::prefix('layanan-paket-request')->controller(LayananPaketRequestController::class)->group(function () {
            Route::get('/get-list', 'getList');
            Route::put('/update-section/{id}', 'updateSection');
            Route::post('/create-layanan', 'createLayanan');
            Route::put('/update-layanan/{id}', 'updateLayanan');
            Route::delete('/delete-layanan/{id}', 'deleteLayanan');
        });

        Route::prefix('tujuan-tambahan')->controller(TujuanTambahanController::class)->group(function () {
            Route::get('/get-list', 'getList');
            Route::post('/create', 'create');
            Route::put('/update/{id}', 'update');
            Route::delete('/delete/{id}', 'delete');
        });

        Route::prefix('transaksi')->controller(TransaksiController::class)->group(function () {
            Route::get('/preview-file', 'previewFile');
            Route::post('/update-status-transaksi', 'updateStatusTransaksi');
            Route::post('/update-status-pembayaran', 'updateStatusPembayaran');
            Route::post('/verifikasi-bukti-pembayaran', 'verifikasiBuktiPembayaran');

            //peminat edutrip
            Route::get('/get-list-peminat-edutrip', 'getListPeminatEdutrip');
            Route::get('/get-count-peminat-edutrip', 'getCountPeminatEdutrip');
            Route::delete('/delete-peminat-edutrip/{id}', 'deletePeminatEdutrip');

            // pendaftaran haji
            Route::get('/get-list-pendaftaran-haji', 'getListPendaftaranHaji');
            Route::get('/get-count-pendaftaran-haji', 'getCountPendaftaranHaji');
            Route::delete('/delete-pendaftaran-haji/{id}', 'deletePendaftaranHaji');

            // pemesanan umrah
            Route::get('/get-list-pemesanan-umrah', 'getListPemesananUmrah');
            Route::get('/get-count-pemesanan-umrah', 'getCountPemesananUmrah');
            Route::delete('/delete-pemesanan-umrah/{id}', 'deletePemesananUmrah');

            // permintaan custom (product-request / custom umrah / land arrangement)
            Route::get('/get-list-permintaan-custom', 'getListPermintaanCustom');
            Route::get('/get-count-permintaan-custom', 'getCountPermintaanCustom');

            // transaksi komponen (grouping per komponen: Hotel, dll)
            Route::get('/get-list-transaksi-komponen-grouped', 'getListTransaksiKomponenGrouped');

            // daftar refund (pengembalian dana)
            Route::get('/get-list-refund', 'getListRefund');
            Route::get('/get-count-refund', 'getCountRefund');

            // laporan keuangan (satu endpoint: umrah + haji + edutrip)
            Route::get('/get-laporan-keuangan', 'getLaporanKeuangan');
            Route::get('/get-count-laporan-keuangan', 'getCountLaporanKeuangan');
        });

        Route::prefix('mitra')->controller(PendaftaranMitraController::class)->group(function () {

            Route::get('/get-list-semua-mitra', 'getListSemuaMitra');
            Route::post('/proses-mitra', 'prosesMitra');
            Route::get('/get-list-pendaftaran-mitra', 'getListPendaftaranMitra');
            Route::get('/get-count-pendaftaran-mitra', 'getCountPendaftaranMitra');
            Route::get('/get-detail-mitra', 'getDetailMitra');
            Route::post('/setujui-mitra', 'setujuiMitra');
            Route::put('/update-level-mitra', 'updateLevelMitra');
            Route::post('/tolak-mitra', 'tolakMitra');
        });

        Route::prefix('user')->controller(UsersController::class)->group(function () {
            Route::get('/get-list-user-aktif', 'getUserAktif');
            Route::get('/get-list-admin', 'getListAdmin');
            Route::put('/update/{id}', 'updateUser');
            Route::put('/update-subrole/{id}', 'updateSubrole');
        });

        Route::prefix('banner')->controller(BannerController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/lokasi-options', 'lokasiOptions');
            Route::post('/upload-gambar', 'uploadGambar');
            Route::post('/', 'store');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        Route::prefix('setting-page')->controller(PageContentController::class)->group(function () {
            Route::get('/tentang-kami', 'getTentangKamiAdmin');
            Route::put('/tentang-kami', 'updateTentangKami');
            Route::get('/syarat-ketentuan', 'getSyaratKetentuanAdmin');
            Route::put('/syarat-ketentuan', 'updateSyaratKetentuan');
            Route::get('/kebijakan-privasi', 'getKebijakanPrivasiAdmin');
            Route::put('/kebijakan-privasi', 'updateKebijakanPrivasi');
            Route::get('/faq', 'getFaqAdmin');
            Route::put('/faq', 'updateFaq');
            Route::get('/app-settings', 'getAppSettingsAdmin');
            Route::put('/app-settings', 'updateAppSettings');
        });
    });
});
