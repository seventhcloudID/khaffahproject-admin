<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Log durasi request API untuk debugging performa (bootstrap + middleware + controller).
 * Hapus atau nonaktifkan di production jika tidak diperlukan.
 */
class LogApiTiming
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $ms = round((microtime(true) - $start) * 1000);
        Log::channel('single')->info('[Laravel API timing]', [
            'path' => $request->path(),
            'method' => $request->method(),
            'totalMs' => $ms,
        ]);
        return $response;
    }
}
