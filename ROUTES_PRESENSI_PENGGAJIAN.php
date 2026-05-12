<?php

/**
 * Routes untuk Sistem Presensi dan Penggajian
 * 
 * Tambahkan routes ini ke file routes/web.php
 * 
 * Pastikan sudah import controller:
 * use App\Http\Controllers\PresensiController;
 * use App\Http\Controllers\PenggajianController;
 */

// ============================================
// PRESENSI ROUTES
// ============================================
Route::prefix('transaksi/presensi')->name('presensi.')->middleware(['auth'])->group(function () {
    // Daftar presensi
    Route::get('/', [PresensiController::class, 'index'])
        ->name('index')
        ->middleware('can:view,App\Models\Presensi');

    // Form input presensi
    Route::get('/create', [PresensiController::class, 'create'])
        ->name('create')
        ->middleware('can:create,App\Models\Presensi');

    // Simpan presensi baru
    Route::post('/', [PresensiController::class, 'store'])
        ->name('store')
        ->middleware('can:create,App\Models\Presensi');

    // Detail presensi
    Route::get('/{id}', [PresensiController::class, 'show'])
        ->name('show')
        ->middleware('can:view,presensi');

    // Form edit presensi
    Route::get('/{id}/edit', [PresensiController::class, 'edit'])
        ->name('edit')
        ->middleware('can:update,presensi');

    // Update presensi
    Route::put('/{id}', [PresensiController::class, 'update'])
        ->name('update')
        ->middleware('can:update,presensi');

    // Hapus presensi
    Route::delete('/{id}', [PresensiController::class, 'destroy'])
        ->name('destroy')
        ->middleware('can:delete,presensi');

    // API: Ambil rekap presensi bulanan
    Route::get('/api/rekap/{pegawaiId}/{bulan}/{tahun}', [PresensiController::class, 'getRekapBulanan'])
        ->name('rekap');

    // API: Ambil detail presensi periode
    Route::get('/api/detail/{pegawaiId}/{bulan}/{tahun}', [PresensiController::class, 'getDetailPeriode'])
        ->name('detail-periode');

    // Bulk import presensi
    Route::post('/bulk-import', [PresensiController::class, 'bulkImport'])
        ->name('bulk-import')
        ->middleware('can:create,App\Models\Presensi');
});

// ============================================
// PENGGAJIAN ROUTES
// ============================================
Route::prefix('transaksi/penggajian')->name('penggajian.')->middleware(['auth'])->group(function () {
    // Daftar penggajian (riwayat)
    Route::get('/', [PenggajianController::class, 'index'])
        ->name('index')
        ->middleware('can:view,App\Models\Penggajian');

    // Form generate penggajian
    Route::get('/generate', [PenggajianController::class, 'generateForm'])
        ->name('generate-form')
        ->middleware('can:create,App\Models\Penggajian');

    // Generate penggajian bulanan
    Route::post('/generate', [PenggajianController::class, 'generate'])
        ->name('generate')
        ->middleware('can:create,App\Models\Penggajian');

    // Detail penggajian
    Route::get('/{id}', [PenggajianController::class, 'show'])
        ->name('show')
        ->middleware('can:view,penggajian');

    // Tandai sebagai lunas
    Route::post('/{id}/mark-as-paid', [PenggajianController::class, 'markAsPaid'])
        ->name('mark-as-paid')
        ->middleware('can:update,penggajian');

    // Print slip gaji
    Route::get('/{id}/print-slip', [PenggajianController::class, 'printSlip'])
        ->name('print-slip')
        ->middleware('can:view,penggajian');

    // API: Summary penggajian periode
    Route::get('/api/summary', [PenggajianController::class, 'summary'])
        ->name('summary');

    // Export penggajian ke Excel
    Route::get('/export', [PenggajianController::class, 'export'])
        ->name('export')
        ->middleware('can:view,App\Models\Penggajian');
});

// ============================================
// DASHBOARD PEGAWAI (Optional)
// ============================================
Route::prefix('dashboard/pegawai')->name('dashboard.pegawai.')->middleware(['auth'])->group(function () {
    // Ringkasan presensi & gaji bulan ini
    Route::get('/summary', function () {
        $pegawaiId = auth()->user()->pegawai_id ?? null;
        
        if (!$pegawaiId) {
            return redirect()->route('dashboard');
        }

        $penggajianService = app(\App\Services\PenggajianService::class);
        $rekap = $penggajianService->getRekapPegawaiCurrentMonth($pegawaiId);
        $estimasiGaji = $penggajianService->getEstimasiGajiCurrentMonth($pegawaiId);

        return view('dashboard.pegawai.summary', compact('rekap', 'estimasiGaji'));
    })->name('summary');
});

/**
 * CATATAN IMPLEMENTASI:
 * 
 * 1. Tambahkan import di atas file routes/web.php:
 *    use App\Http\Controllers\PresensiController;
 *    use App\Http\Controllers\PenggajianController;
 * 
 * 2. Jika menggunakan authorization (Gate/Policy):
 *    - Buat Policy untuk Presensi dan Penggajian
 *    - Atau hapus middleware 'can:...' jika tidak menggunakan
 * 
 * 3. Jika menggunakan role-based access:
 *    - Tambahkan middleware 'role:owner' atau 'role:admin'
 *    - Contoh: ->middleware(['auth', 'role:owner'])
 * 
 * 4. API routes bisa dipindahkan ke routes/api.php jika diperlukan
 * 
 * 5. Untuk bulk import, pastikan sudah install Laravel Excel:
 *    composer require maatwebsite/excel
 */
