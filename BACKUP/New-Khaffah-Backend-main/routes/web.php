<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Fallback: serve file dari storage/app/public (untuk shared hosting tanpa symlink)
|--------------------------------------------------------------------------
| Jika php artisan storage:link tidak bisa dijalankan, server akan 404 pada
| /storage/... Route ini menangani request tersebut dan mengirim file dari
| disk 'public'. Pastikan request /storage/* diarahkan ke index.php (mis. try_files).
*/
$serveStorage = function (string $path) {
    $path = str_replace('\\', '/', $path);
    if (preg_match('/\.\.|[\/\\\\]\.\./', $path)) {
        abort(404);
    }
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return Storage::disk('public')->response($path);
};

// Fallback saat symlink tidak ada: request /storage/* bisa diarahkan ke index.php oleh server
Route::get('/storage/{path}', $serveStorage)->where('path', '.*')->name('storage.serve');

// Alternatif untuk shared hosting: URL ini selalu lewat Laravel (pakai di .env APP_STORAGE_VIA_ROUTE=serve)
Route::get('/serve/public/{path}', $serveStorage)->where('path', '.*')->name('storage.serve.public');
