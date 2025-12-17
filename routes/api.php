<?php

use App\Http\Controllers\Api\BopApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// BOP API Routes
Route::prefix('bop')->group(function () {
    Route::post('/update-aktual', [BopApiController::class, 'updateAktual'])->name('api.bop.update-aktual');
});

// Jabatan API Routes
Route::prefix('jabatan')->group(function () {
    Route::get('/{id}', [\App\Http\Controllers\Api\JabatanController::class, 'show'])->name('api.jabatan.show');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Asset related API routes
Route::prefix('asets')->middleware('auth:sanctum')->group(function () {
    // CRUD Aset
    Route::get('/', [\App\Http\Controllers\Api\AsetController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\AsetController::class, 'store']);
    Route::get('/{aset}', [\App\Http\Controllers\Api\AsetController::class, 'show']);
    Route::put('/{aset}', [\App\Http\Controllers\Api\AsetController::class, 'update']);
    Route::delete('/{aset}', [\App\Http\Controllers\Api\AsetController::class, 'destroy']);

    // Depreciation Schedule
    Route::post('/{aset}/generate-schedule', [\App\Http\Controllers\Api\AsetController::class, 'generateSchedule']);
    Route::post('/{aset}/save-schedule', [\App\Http\Controllers\Api\AsetController::class, 'saveSchedule']);
    Route::get('/{aset}/depreciation-schedules', [\App\Http\Controllers\Api\AsetController::class, 'depreciationSchedules']);
});

// Depreciation Schedule routes
Route::prefix('depreciation-schedules')->middleware('auth:sanctum')->group(function () {
    Route::post('/{schedule}/post', [\App\Http\Controllers\Api\AsetController::class, 'postSchedule']);
    Route::post('/{schedule}/reverse', [\App\Http\Controllers\Api\AsetController::class, 'reverseSchedule']);
});

// Kategori options (public)
Route::get('/aset/kategori', [\App\Http\Controllers\Api\AsetController::class, 'getKategoriByJenis']);

// Presensi API Routes
Route::get('/presensi/jam-kerja', function (Request $request) {
    $pegawaiId = $request->input('pegawai_id');
    $month = $request->input('month');
    $year = $request->input('year');
    
    $totalJam = \App\Models\Presensi::where('pegawai_id', $pegawaiId)
        ->whereMonth('tgl_presensi', $month)
        ->whereYear('tgl_presensi', $year)
        ->sum('jumlah_jam');
    
    return response()->json(['total_jam' => $totalJam]);
});
