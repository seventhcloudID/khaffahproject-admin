<?php
// "Lebih baik banyak pikiran, daripada gak pernah berpikir."
// krisnn

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Mitra\PendaftaranMitraController;
use App\Http\Controllers\Mitra\MitraDashboardController;

use App\Http\Controllers\Produk\PaketHajiController;
use App\Http\Controllers\Produk\PaketEdutripController;
use App\Http\Controllers\Produk\PaketUmrahController;

use App\Http\Controllers\User\JamaahController;
use App\Http\Controllers\User\BankAccountController;
use App\Http\Controllers\User\UsersController;
use App\Http\Controllers\Dokumen\DokumenController;
use App\Http\Controllers\User\RefundController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\Utility\WebhookController;
use App\Http\Controllers\Utility\UtilityController;
use App\Http\Controllers\Mitra\LandArrangementController;
use App\Http\Controllers\Produk\RequestProductController;
use App\Http\Controllers\Produk\PaketCustomController;
use App\Http\Controllers\Produk\LayananPaketRequestController;



// Tanpa Auth

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);


Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);


Route::prefix('/utility')->controller(UtilityController::class)->group(function () {
    Route::get('/gelar', 'getGelar');
    Route::get('/provinsi', 'getProvinsi');
    Route::get('/kota', 'getKota');
    Route::get('/kecamatan', 'getKecamatan');
    
    Route::get('/keberangkatan', 'getKeberangkatan');
    Route::get('/bandara', 'getBandara');
    Route::get('/hotels', 'getHotels');
    Route::get('/maskapai', 'getMaskapai');
    Route::get('/tujuan-tambahan', 'getTujuanTambahan');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::post('/me/photo', [AuthController::class, 'updateProfilePhoto']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::prefix('paket-umrah')->controller(PaketUmrahController::class)->group(function () {
    Route::get('/list-paket', 'getListPaketUmrah');
    Route::get('/paket', 'getPaketUmrah');
    Route::get('/review', 'getReview');
    Route::get('/tipe', 'getTipePaketUmrah');
});

Route::prefix('edutrip')->controller(PaketEdutripController::class)->group(function () {
    Route::get('/paket', 'getPaketEdutrip');
});

Route::prefix('haji')->controller(PaketHajiController::class)->group(function () {
    Route::get('/paket', 'getPaketHaji');
});

// Paket custom - public list untuk halaman product-request
Route::prefix('paket-custom')->controller(PaketCustomController::class)->group(function () {
    Route::get('/list', 'listForProductRequest');
});

// Layanan paket request - public list untuk tampilan Pelayanan Wajib & Layanan Tambahan
Route::prefix('layanan-paket-request')->controller(LayananPaketRequestController::class)->group(function () {
    Route::get('/list', 'listForDisplay');
});

Route::post('/webhook/moota', [WebhookController::class, 'handleMoota']);

// Public: konten halaman (tanpa auth)
Route::get('/setting-page/tentang-kami', [\App\Http\Controllers\Setting\PageContentController::class, 'getTentangKami']);
Route::get('/setting-page/syarat-ketentuan', [\App\Http\Controllers\Setting\PageContentController::class, 'getSyaratKetentuan']);
Route::get('/setting-page/kebijakan-privasi', [\App\Http\Controllers\Setting\PageContentController::class, 'getKebijakanPrivasi']);
Route::get('/setting-page/faq', [\App\Http\Controllers\Setting\PageContentController::class, 'getFaq']);
Route::get('/setting-page/app-settings', [\App\Http\Controllers\Setting\PageContentController::class, 'getAppSettings']);

// Public: list banner (untuk frontend landing)
Route::get('/banner', [\App\Http\Controllers\Setting\BannerController::class, 'listPublic']);

//pendaftaran mitra

Route::prefix('mitra')->controller(PendaftaranMitraController::class)->group(function () {
    Route::post('/daftar-mitra', 'daftarMitra');
});

//end pendaftaran mitra

//End Tanpa Auth

// API khusus superadmin (Monitoring Operasional: daftar admin & ubah subrole)
Route::middleware(['auth:sanctum', 'role:superadmin'])->prefix('sistem-admin')->group(function () {
    Route::get('user/get-list-admin', [UsersController::class, 'getListAdmin']);
    Route::put('user/update-subrole/{id}', [UsersController::class, 'updateSubrole']);
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::middleware('role:user|mitra|superadmin')->group(function () {

        // --- Haji ---
        Route::prefix('haji')->controller(PaketHajiController::class)->group(function () {
            Route::get('/transaksi', 'indexTransaksiHaji');
            Route::post('/pesan', 'pesanPaketHaji');
            
            // Kelola Submission Haji
            Route::get('/submission', 'getListSubmissionHaji');
            Route::get('/submission/{id}', 'getDetailSubmissionHaji');
            Route::put('/submission/{id}/update-status', 'updateStatusSubmissionHaji');
        });

        // --- Handle Submission Form Haji (Mitra) ---
        Route::prefix('mitra/haji')->controller(PaketHajiController::class)->group(function () {
            Route::post('/submit-form', 'handleSubmissionFormHaji');
        });

        // --- Edutrip ---
        Route::prefix('edutrip')->controller(PaketEdutripController::class)->group(function () {
            Route::get('/transaksi', 'indexTransaksiEdutrip');
            Route::post('/pesan', 'pesanPaketEdutrip');
            
            // Kelola Submission Edutrip
            Route::get('/submission', 'getListSubmissionEdutrip');
            Route::get('/submission/{id}', 'getDetailSubmissionEdutrip');
            Route::put('/submission/{id}/update-status', 'updateStatusSubmissionEdutrip');
        });

        // --- Paket Umrah ---
        Route::prefix('paket-umrah')->controller(PaketUmrahController::class)->group(function () {
            Route::get('/transaksi', 'indexTransaksiUmrah');
            Route::get('/preview', 'previewTipePaketUmrah');
            Route::post('/pesan', 'pesanPaketUmrah');
            Route::post('/upload-bukti', 'uploadBuktiPembayaran');
            Route::post('/pesan-bundling-edutrip', 'pesanPaketUmrahBundlingEdutrip');
        });

        // --- Dokumen (preview untuk user sendiri: jamaah, dll) ---
        Route::get('dokumen/{id}/preview', [DokumenController::class, 'preview'])->where('id', '[0-9]+');

        // --- Jamaah ---
        Route::prefix('jamaah')->controller(JamaahController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{jamaah}', 'show');
            Route::put('/{jamaah}', 'update');
            Route::delete('/{jamaah}', 'destroy');
        });

        // --- Rekening Bank (Account) ---
        Route::prefix('account/banks')->controller(BankAccountController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::delete('/{id}', 'destroy');
        });

        // --- Pengembalian Dana (Refund) ---
        Route::prefix('account/refund')->controller(RefundController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
        });

        // --- Pesanan Saya (Orders) ---
        Route::get('account/orders', [OrderController::class, 'index']);
        Route::get('account/orders/{id}', [OrderController::class, 'show']);
        Route::post('account/orders/{id}/upload-bukti', [OrderController::class, 'uploadBukti']);

        // --- Dashboard Mitra (statistik dari DB) ---
        Route::get('mitra/dashboard', [MitraDashboardController::class, 'dashboard']);

        // --- Daftar pesanan mitra (transaksi yang dibuat sebagai mitra, terpisah dari account/orders) ---
        Route::get('mitra/orders', [OrderController::class, 'indexMitra']);

        // --- Pengembalian dana mitra (hanya transaksi dibuat sebagai mitra) ---
        Route::get('mitra/refund', [RefundController::class, 'indexMitra']);

        // --- Land Arrangement (Mitra) ---
        Route::prefix('mitra/land-arrangement')->controller(LandArrangementController::class)->group(function () {
            Route::post('/grup', 'requestLandArrangementGrup');
            Route::post('/private', 'requestLandArrangementPrivate');
            Route::post('/pemesanan-reguler', 'pesanPaketLAReguler');
        });

        // --- Request Product (Umrah Custom / LA Custom - Form Requirements) ---
        Route::post('/request-products', [RequestProductController::class, 'store']);
    });
});
