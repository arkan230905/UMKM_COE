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

        Route::resource('pegawai', PegawaiController::class);
        Route::resource('presensi', PresensiController::class);
        Route::resource('produk', ProdukController::class);
        Route::resource('vendor', VendorController::class);

        Route::resource('coa', CoaController::class);
        Route::get('coa/generate-kode', [CoaController::class, 'generateKode'])->name('coa.generateKode');

        Route::resource('bahan-baku', BahanBakuController::class)->names([
            'index'   => 'bahan-baku.index',
            'create'  => 'bahan-baku.create',
            'store'   => 'bahan-baku.store',
            'show'    => 'bahan-baku.show',
            'edit'    => 'bahan-baku.edit',
            'update'  => 'bahan-baku.update',
            'destroy' => 'bahan-baku.destroy',
        ]);

        // ✅ Bill of Material (BOM)
        Route::prefix('bom')->name('bom.')->group(function () {
            Route::get('/', [BomController::class, 'index'])->name('index'); // Halaman utama BOM
            Route::get('/view/{produk_id}', [BomController::class, 'view'])->name('view'); // AJAX tabel BOM
            Route::get('/create', [BomController::class, 'create'])->name('create'); // Form Tambah BOM
            Route::post('/store', [BomController::class, 'store'])->name('store');   // Simpan BOM
        });
    });

    // ====================================================
    // 💸 TRANSAKSI
    // ====================================================
    Route::prefix('transaksi')->name('transaksi.')->group(function () {
        Route::resource('pembelian', PembelianController::class);
        Route::resource('penjualan', PenjualanController::class);
        Route::resource('retur', ReturController::class);
    });

    // ====================================================
    // 📑 LAPORAN
    // ====================================================
    Route::prefix('laporan')->name('laporan.')->group(function () {
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
});

require __DIR__ . '/auth.php';
