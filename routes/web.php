<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    ProfileController,
    PegawaiController,
    PresensiController,
    ProdukController,
    VendorController,
    CoaController,
    BahanBakuController,
    BomController,
    PembelianController,
    PenjualanController,
    ReturController,
    LaporanController
};

// ========================================================
// 🏠 Halaman Utama (Welcome Page)
// ========================================================
Route::view('/', 'welcome')->name('welcome');

// ========================================================
// 🏢 Tentang Perusahaan
// ========================================================
Route::view('/tentang-perusahaan', 'tentang.perusahaan')->name('tentang.perusahaan');

// ========================================================
// 🔐 Middleware: Auth + Verified
// ========================================================
Route::middleware(['auth', 'verified'])->group(function () {

    // ====================================================
    // 📊 Dashboard
    // ====================================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');

    // ====================================================
    // 👤 Profile
    // ====================================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ====================================================
    // 🗂️ MASTER DATA
    // ====================================================
    Route::prefix('master-data')->name('master-data.')->group(function () {

        // Pegawai
        Route::resource('pegawai', PegawaiController::class);

        // Presensi
        Route::resource('presensi', PresensiController::class);

        // Produk
        Route::resource('produk', ProdukController::class);

        // Vendor
        Route::resource('vendor', VendorController::class);

        // COA
        Route::resource('coa', CoaController::class);
        Route::get('coa/generate-kode', [CoaController::class, 'generateKode'])->name('coa.generateKode');

        // BOM
        Route::resource('bom', BomController::class);

        // Bahan Baku (nama route tetap konsisten)
        Route::resource('bahan-baku', BahanBakuController::class)->names([
            'index'   => 'bahan-baku.index',
            'create'  => 'bahan-baku.create',
            'store'   => 'bahan-baku.store',
            'show'    => 'bahan-baku.show',
            'edit'    => 'bahan-baku.edit',
            'update'  => 'bahan-baku.update',
            'destroy' => 'bahan-baku.destroy',
        ]);
    });

    // ====================================================
    // 💸 TRANSAKSI
    // ====================================================
    Route::prefix('transaksi')->name('transaksi.')->group(function () {

        // Pembelian
        Route::resource('pembelian', PembelianController::class);

        // Penjualan
        Route::resource('penjualan', PenjualanController::class);

        // Retur
        Route::resource('retur', ReturController::class);
    });

    // ====================================================
    // 📑 LAPORAN
    // ====================================================
    Route::prefix('laporan')->name('laporan.')->group(function () {

        // Laporan Pembelian
        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');
        Route::get('/pembelian/export/pdf', [LaporanController::class, 'exportPembelianPdf'])->name('pembelian.export.pdf');
        Route::get('/pembelian/export/excel', [LaporanController::class, 'exportPembelianExcel'])->name('pembelian.export.excel');

        // Laporan Penjualan
        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('/penjualan/export/pdf', [LaporanController::class, 'exportPenjualanPdf'])->name('penjualan.export.pdf');
        Route::get('/penjualan/export/excel', [LaporanController::class, 'exportPenjualanExcel'])->name('penjualan.export.excel');

        // Laporan Stok Produk
        Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
        Route::get('/stok/export/pdf', [LaporanController::class, 'exportStokPdf'])->name('stok.export.pdf');
        Route::get('/stok/export/excel', [LaporanController::class, 'exportStokExcel'])->name('stok.export.excel');
    });
});

// ========================================================
// 🔐 Autentikasi (Login, Register, dsb.)
// ========================================================
require __DIR__ . '/auth.php';
