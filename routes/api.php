<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini kamu bisa mendefinisikan route untuk API aplikasi kamu.
| Route ini otomatis dimuat oleh RouteServiceProvider dan semuanya
| akan memiliki prefix "api".
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Asset related API routes
Route::prefix('aset')->group(function () {
    Route::get('/kategori', [\App\Http\Controllers\Api\AsetController::class, 'getKategoriByJenis']);
});
