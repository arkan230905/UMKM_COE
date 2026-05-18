<?php

// ====================================================================
// PELANGGAN FALLBACK ROUTES - Redirect dari URL lama ke URL baru
// ====================================================================
// File ini harus di-include di routes/web.php sebelum multi-tenant routes
// Routes ini TIDAK menggunakan middleware set.perusahaan karena controller
// sudah menangani pencarian perusahaan sendiri

Route::prefix('pelanggan')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Pelanggan\FallbackController::class, 'dashboard']);
    Route::get('/login', [\App\Http\Controllers\Pelanggan\FallbackController::class, 'login']);
    Route::get('/cart', [\App\Http\Controllers\Pelanggan\FallbackController::class, 'cart']);
    Route::get('/{path?}', [\App\Http\Controllers\Pelanggan\FallbackController::class, 'catchAll'])->where('path', '.*');
});
