<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    ProfileController,
    PegawaiController,
    PresensiController,
    ProdukController,
    SatuanController,
    VendorController,
    CoaController,
    BahanBakuController,
    BomController,
    BopController,
    PembelianController,
    PenjualanController,
    PenggajianController,
    ReturController,
    LaporanController
};

// ========================================================
// 🏠 Halaman Utama
// ========================================================
Route::view('/', 'welcome')->name('welcome');

// ========================================================
// 📊 Dashboard & Tentang Perusahaan
// ========================================================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Tentang Perusahaan
    Route::get('/tentang-perusahaan', [DashboardController::class, 'tentangPerusahaan'])
        ->name('tentang-perusahaan');
    Route::post('/tentang-perusahaan/update', [DashboardController::class, 'updatePerusahaan'])
        ->name('tentang-perusahaan.update');
});

// ========================================================
// 👤 Profile
// ========================================================
Route::middleware(['auth', 'verified'])
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

// ========================================================
// 🗂️ Master Data
// ========================================================
Route::middleware(['auth', 'verified'])
    ->prefix('master-data')
    ->name('master-data.')
    ->group(function () {
        Route::resource('pegawai', PegawaiController::class);
        Route::resource('presensi', PresensiController::class);
        Route::resource('produk', ProdukController::class);
        Route::resource('satuan', SatuanController::class);
        Route::resource('vendor', VendorController::class);
        Route::resource('coa', CoaController::class);
        Route::resource('bahan-baku', BahanBakuController::class);

        // ✅ Bill of Material (BOM)
        Route::prefix('bom')->name('bom.')->group(function () {
            Route::get('/', [BomController::class, 'index'])->name('index');
            Route::get('/view/{produk_id}', [BomController::class, 'view'])->name('view');
            Route::get('/create', [BomController::class, 'create'])->name('create');
            Route::post('/store', [BomController::class, 'store'])->name('store');
        });

        // ✅ BOP tanpa tambah data (create & store dihapus)
        Route::resource('bop', BopController::class)->except(['create', 'store']);

        // ✅ Route khusus COA
        Route::get('coa/generate-kode', [CoaController::class, 'generateKode'])->name('coa.generateKode');
    });

// ========================================================
// 💸 Transaksi
// ========================================================
Route::middleware(['auth', 'verified'])
    ->prefix('transaksi')
    ->name('transaksi.')
    ->group(function () {
        Route::resource('pembelian', PembelianController::class);
        Route::resource('penjualan', PenjualanController::class);
        Route::resource('retur', ReturController::class);
    });

// ========================================================
// 📑 Laporan
// ========================================================
Route::middleware(['auth', 'verified'])
    ->prefix('laporan')
    ->name('laporan.')
    ->group(function () {
        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');
        Route::get('/pembelian/export/pdf', [LaporanController::class, 'exportPembelianPdf'])->name('pembelian.export.pdf');
        Route::get('/pembelian/export/excel', [LaporanController::class, 'exportPembelianExcel'])->name('pembelian.export.excel');

        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('/penjualan/export/pdf', [LaporanController::class, 'exportPenjualanPdf'])->name('penjualan.export.pdf');
        Route::get('/penjualan/export/excel', [LaporanController::class, 'exportPenjualanExcel'])->name('penjualan.export.excel');

        Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
        Route::get('/stok/export/pdf', [LaporanController::class, 'exportStokPdf'])->name('stok.export.pdf');
        Route::get('/stok/export/excel', [LaporanController::class, 'exportStokExcel'])->name('stok.export.excel');
    });

// ========================================================
// 🔐 Autentikasi
// ========================================================
require __DIR__ . '/auth.php';
