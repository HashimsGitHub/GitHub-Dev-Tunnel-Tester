<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$withoutSession = [
    Illuminate\Cookie\Middleware\EncryptCookies::class,
    Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    Illuminate\Session\Middleware\StartSession::class,
    Illuminate\View\Middleware\ShareErrorsFromSession::class,
    Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
];

Route::get('/', function (Request $request) {
    return response()->view('dashboard', [
        'snapshot' => [
            'method' => $request->method(),
            'host' => $request->getHttpHost(),
            'protocol' => strtoupper($request->getScheme()),
            'ip' => $request->getClientIp() ?? 'Unavailable',
            'userAgent' => $request->userAgent() ?? 'Unavailable',
            'time' => now()->toIso8601String(),
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
        ],
    ])->header('Cache-Control', 'no-store, private');
})->withoutMiddleware($withoutSession)->name('dashboard');

Route::get('/health', function (Request $request) {
    return response()->json([
        'status' => 'ok',
        'app' => 'Tunnel Gauge',
        'laravel' => app()->version(),
        'php' => PHP_VERSION,
        'time' => now()->toIso8601String(),
        'request' => [
            'method' => $request->method(),
            'host' => $request->getHttpHost(),
            'protocol' => strtoupper($request->getScheme()),
            'ip' => $request->getClientIp() ?? 'Unavailable',
        ],
    ])->header('Cache-Control', 'no-store, private');
})->withoutMiddleware($withoutSession)->name('health');

Route::get('/benchmark', function (Request $request) {
    $iterations = min(max($request->integer('iterations', 100_000), 1_000), 500_000);
    $startedAt = hrtime(true);
    $digest = 'tunnel-gauge';

    for ($index = 0; $index < $iterations; $index++) {
        $digest = hash('sha256', $digest.':'.$index);
    }

    return response()->json([
        'iterations' => $iterations,
        'durationMs' => round((hrtime(true) - $startedAt) / 1_000_000, 3),
        'checksum' => substr($digest, 0, 16),
        'memoryMb' => round(memory_get_peak_usage(true) / 1_048_576, 2),
    ])->header('Cache-Control', 'no-store, private');
})->withoutMiddleware($withoutSession)->name('benchmark');
