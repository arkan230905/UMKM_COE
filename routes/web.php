<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// ====================================================================
// CONTROLLERS
// ====================================================================

// Dashboard
use App\Http\Controllers\DashboardController;

// Master Data
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\BopController;
use App\Http\Controllers\BomController;

// Transaksi
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\PenggajianController;

// Laporan
use App\Http\Controllers\LaporanController;

// Profile
use App\Http\Controllers\ProfileController;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Perusahaan
use App\Http\Controllers\PerusahaanController;


// ====================================================================
// HALAMAN UTAMA
// ====================================================================
Route::get('/', function () {
    return view('welcome');
})->name('welcome');


// ====================================================================
// AUTH ROUTES
// ====================================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Minimal route untuk lupa password
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    return back()->with('status', 'Link reset password telah dikirim (simulasi).');
})->name('password.email');


// ====================================================================
// SEMUA ROUTE YANG HANYA BISA DIAKSES SETELAH LOGIN
// ====================================================================
Route::middleware('auth')->group(function () {

    // ================================================================
    // DASHBOARD
    // ================================================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // ================================================================
    // MASTER DATA
    // ================================================================
    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::resource('pegawai', PegawaiController::class);
        Route::resource('presensi', PresensiController::class);
        Route::resource('produk', ProdukController::class);
        Route::resource('vendor', VendorController::class);
        Route::resource('bahan-baku', BahanBakuController::class);
        Route::resource('satuan', SatuanController::class);
        Route::resource('coa', CoaController::class);
        Route::get('coa/generate-kode', [CoaController::class, 'generateKode'])->name('coa.generate-kode');
        Route::resource('bop', BopController::class);
        Route::post('bop/recalc', [BopController::class, 'recalc'])->name('bop.recalc');
        // Specific view route must be defined before resource to avoid being captured by 'bom/{bom}' show
        Route::get('bom/by-produk/{id}', [BomController::class, 'view'])->name('bom.view');
        Route::post('bom/by-produk/{id}', [BomController::class, 'updateByProduk'])->name('bom.updateByProduk');
        Route::resource('bom', BomController::class);
    });


    // ================================================================
    // TRANSAKSI
    // ================================================================
    Route::prefix('transaksi')->name('transaksi.')->group(function () {

        // Pembelian, Penjualan, Retur
        Route::resource('pembelian', PembelianController::class);
        Route::resource('penjualan', PenjualanController::class);
        Route::resource('retur', ReturController::class);

        // ============================================================
        // ✅ PENGGAJIAN
        // ============================================================
        Route::get('/penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
        Route::get('/penggajian/create', [PenggajianController::class, 'create'])->name('penggajian.create');
        Route::post('/penggajian', [PenggajianController::class, 'store'])->name('penggajian.store');
        Route::delete('/penggajian/{id}', [PenggajianController::class, 'destroy'])->name('penggajian.destroy');

        // ============================================================
        // ✅ PRODUKSI
        // ============================================================
        Route::prefix('produksi')->name('produksi.')->group(function() {
            Route::get('/', [\App\Http\Controllers\ProduksiController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\ProduksiController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\ProduksiController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\ProduksiController::class, 'show'])->name('show');
        });
    });


    // ================================================================
    // LAPORAN
    // ================================================================
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/stok', [LaporanController::class, 'laporanStok'])->name('stok');
        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('/penjualan/{id}/invoice', [LaporanController::class, 'invoicePenjualan'])->name('penjualan.invoice');
        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');
        Route::get('/pembelian/{id}/invoice', [LaporanController::class, 'invoicePembelian'])->name('pembelian.invoice');
    });

    // ================================================================
    // AKUNTANSI
    // ================================================================
    Route::prefix('akuntansi')->name('akuntansi.')->group(function () {
        Route::get('/jurnal-umum', [\App\Http\Controllers\AkuntansiController::class, 'jurnalUmum'])->name('jurnal-umum');
        Route::get('/buku-besar', [\App\Http\Controllers\AkuntansiController::class, 'bukuBesar'])->name('buku-besar');
        Route::get('/neraca-saldo', [\App\Http\Controllers\AkuntansiController::class, 'neracaSaldo'])->name('neraca-saldo');
        Route::get('/laba-rugi', [\App\Http\Controllers\AkuntansiController::class, 'labaRugi'])->name('laba-rugi');
    });


    // ================================================================
    // PROFIL ADMIN
    // ================================================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profil-admin');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profil-admin.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profil-admin.destroy');


    // ================================================================
    // TENTANG PERUSAHAAN
    // ================================================================
    Route::get('/tentang-perusahaan', [PerusahaanController::class, 'edit'])->name('tentang-perusahaan');
    Route::post('/tentang-perusahaan/update', [PerusahaanController::class, 'update'])->name('tentang-perusahaan.update');
});
