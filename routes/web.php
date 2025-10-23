<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ReturController;

// ========================================================
// 🏠 Halaman Utama (Welcome Page)
// ========================================================
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// ========================================================
// 🏢 Tentang Perusahaan
// ========================================================
Route::get('/tentang-perusahaan', function () {
    return view('tentang-perusahaan'); // Pastikan file ini ada di resources/views/tentang-perusahaan.blade.php
})->name('tentang.perusahaan');

// ========================================================
// 📊 Dashboard (Menu Utama Admin)
// ========================================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
});

// ========================================================
// 👤 Profile (Edit Profil User)
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
        // CRUD Data Pegawai
        Route::resource('pegawai', PegawaiController::class);

        // CRUD Presensi Pegawai
        Route::resource('presensi', PresensiController::class);

        // CRUD Produk
        Route::resource('produk', ProdukController::class);

        // CRUD Vendor
        Route::resource('vendor', VendorController::class);

        // CRUD Chart of Account (COA)
        Route::resource('coa', CoaController::class);

        // CRUD Bahan Baku
        Route::resource('bahan-baku', BahanBakuController::class);

        // CRUD Bill of Material (BOM)
        Route::resource('bom', BomController::class);
    });

// ========================================================
// 💸 Transaksi
// ========================================================
Route::middleware(['auth', 'verified'])
    ->prefix('transaksi')
    ->name('transaksi.')
    ->group(function () {
        // CRUD Pembelian Barang
        Route::resource('pembelian', PembelianController::class);

        // CRUD Penjualan Produk
        Route::resource('penjualan', PenjualanController::class);

        // CRUD Retur (Barang Kembali)
        Route::resource('retur', ReturController::class);
    });

// ========================================================
// 🔐 Autentikasi (Login, Register, Forgot Password, dll)
// ========================================================
require __DIR__ . '/auth.php';
