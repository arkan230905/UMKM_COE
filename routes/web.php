
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\WelcomeController;

// ====================================================================
// AUTHENTICATION ROUTES
// ====================================================================
Route::get('/', [WelcomeController::class, '__invoke'])->name('welcome');

Auth::routes(['verify' => true]);

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
use App\Http\Controllers\BopBudgetController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\JabatanController;

// Transaksi
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\ExpensePaymentController;
use App\Http\Controllers\ApSettlementController;

// Laporan
use App\Http\Controllers\LaporanController;

// Profile
use App\Http\Controllers\ProfileController;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Perusahaan
use App\Http\Controllers\PerusahaanController;

// Akuntansi
use App\Http\Controllers\AkuntansiController;

// Produksi
use App\Http\Controllers\ProduksiController;

// Asset
use App\Http\Controllers\AssetController;

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
    // ASSET (example-style simple module)
    // ================================================================
    Route::resource('/aset', AssetController::class)->names('aset');
    Route::get('/aset/{asset}/depreciation', [AssetController::class, 'calculateDepreciation'])->name('aset.depreciation');

    // ================================================================
    // MASTER DATA
    // ================================================================
    Route::prefix('master-data')->name('master-data.')->group(function () {
        // Bahan Baku
        Route::resource('bahan-baku', BahanBakuController::class);
        Route::resource('coa', CoaController::class);
        Route::get('coa/generate-kode', [CoaController::class, 'generateKode'])->name('coa.generate-kode');
        Route::resource('aset', AsetController::class);
        Route::resource('jabatan', JabatanController::class);
        Route::resource('pegawai', PegawaiController::class);
        Route::resource('presensi', PresensiController::class);
        Route::resource('vendor', VendorController::class);
        Route::resource('satuan', SatuanController::class);
        Route::resource('produk', ProdukController::class);
        Route::resource('bop', BopController::class);
        Route::post('bop/recalc', [BopController::class, 'recalc'])->name('bop.recalc');
        Route::resource('bop-budget', BopBudgetController::class)->names('bop-budget');
        // BOM Routes
        Route::prefix('bom')->name('bom.')->group(function () {
            Route::get('by-produk/{id}', [BomController::class, 'view'])->name('view-by-produk');
            Route::post('by-produk/{id}', [BomController::class, 'updateByProduk'])->name('update-by-produk');
            Route::get('generate-kode', [BomController::class, 'generateKodeBom'])->name('generate-kode');
            
            // Resource routes with explicit names to avoid conflicts
            Route::get('/', [BomController::class, 'index'])->name('index');
            Route::get('/create', [BomController::class, 'create'])->name('create');
            Route::post('/', [BomController::class, 'store'])->name('store');
            Route::get('/{bom}', [BomController::class, 'show'])->name('show');
            Route::get('/{bom}/print', [BomController::class, 'print'])->name('print');
            Route::get('/{bom}/edit', [BomController::class, 'edit'])->name('edit');
            Route::put('/{bom}', [BomController::class, 'update'])->name('update');
            Route::delete('/{bom}', [BomController::class, 'destroy'])->name('destroy');
        });
    });


    // ================================================================
    // TRANSAKSI
    // ================================================================
    Route::prefix('transaksi')->name('transaksi.')->group(function () {

        // ============================================================
        // ✅ PEMBELIAN
        // ============================================================
        Route::resource('pembelian', PembelianController::class);

        // ============================================================
        // ✅ PENJUALAN
        // ============================================================
        Route::resource('penjualan', PenjualanController::class);

        // ============================================================
        // ✅ RETUR
        // ============================================================
        Route::resource('retur', ReturController::class);
        Route::post('retur/{id}/approve', [ReturController::class, 'approve'])->name('retur.approve');
        Route::post('retur/{id}/post', [ReturController::class, 'post'])->name('retur.post');

        // ============================================================
        // ✅ PENGGAJIAN
        // ============================================================
        Route::prefix('penggajian')->name('penggajian.')->group(function() {
            Route::get('/', [PenggajianController::class, 'index'])->name('index');
            Route::get('/create', [PenggajianController::class, 'create'])->name('create');
            Route::post('/', [PenggajianController::class, 'store'])->name('store');
            Route::delete('/{id}', [PenggajianController::class, 'destroy'])->name('destroy');
            Route::get('/print/{id}', [PenggajianController::class, 'print'])->name('print');
        });

        // ============================================================
        // ✅ PRODUKSI
        // ============================================================
        Route::prefix('produksi')->name('produksi.')->group(function() {
            Route::get('/', [ProduksiController::class, 'index'])->name('index');
            Route::get('/create', [ProduksiController::class, 'create'])->name('create');
            Route::post('/', [ProduksiController::class, 'store'])->name('store');
            Route::get('/{id}', [ProduksiController::class, 'show'])->name('show');
            Route::delete('/{id}', [ProduksiController::class, 'destroy'])->name('destroy');
        });

        // ============================================================
        // ✅ PEMBAYARAN BEBAN
        // ============================================================
        Route::prefix('expense-payment')->name('expense-payment.')->group(function() {
            Route::get('/', [ExpensePaymentController::class, 'index'])->name('index');
            Route::get('/create', [ExpensePaymentController::class, 'create'])->name('create');
            Route::post('/', [ExpensePaymentController::class, 'store'])->name('store');
            Route::get('/{id}', [ExpensePaymentController::class, 'show'])->name('show');
            Route::get('/print/{id}', [ExpensePaymentController::class, 'print'])->name('print');
        });

        // ============================================================
        // ✅ PELUNASAN UTANG
        // ============================================================
        Route::prefix('ap-settlement')->name('ap-settlement.')->group(function() {
            Route::get('/', [ApSettlementController::class, 'index'])->name('index');
            Route::get('/create', [ApSettlementController::class, 'create'])->name('create');
            Route::post('/', [ApSettlementController::class, 'store'])->name('store');
            Route::get('/{id}', [ApSettlementController::class, 'show'])->name('show');
            Route::get('/print/{id}', [ApSettlementController::class, 'print'])->name('print');
        });

    });


    // ================================================================
    // LAPORAN
    // ================================================================
    Route::prefix('laporan')->name('laporan.')->group(function () {
        // Laporan Utama
        Route::get('/stok', [LaporanController::class, 'laporanStok'])->name('stok');
        
        // Laporan Produksi
        Route::get('/produksi', [LaporanController::class, 'produksi'])->name('produksi');
        
        // Laporan Transaksi
        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('/penjualan/{id}/invoice', [LaporanController::class, 'invoicePenjualan'])->name('penjualan.invoice');
        
        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');
        Route::get('/pembelian/{id}/invoice', [LaporanController::class, 'invoicePembelian'])->name('pembelian.invoice');
        
        // Laporan Baru
        Route::get('/retur', [LaporanController::class, 'laporanRetur'])->name('retur');
        Route::get('/penggajian', [LaporanController::class, 'laporanPenggajian'])->name('penggajian');
        Route::get('/pembayaran-beban', [LaporanController::class, 'laporanPembayaranBeban'])->name('pembayaran-beban');
        Route::get('/pelunasan-utang', [LaporanController::class, 'laporanPelunasanUtang'])->name('pelunasan-utang');
        
        // Laporan Aset
        Route::get('/penyusutan-aset', [\App\Http\Controllers\AsetDepreciationController::class, 'index'])->name('penyusutan.aset');
        Route::get('/penyusutan-aset/{id}', [\App\Http\Controllers\AsetDepreciationController::class, 'show'])->name('penyusutan.aset.show');
        Route::post('/penyusutan-aset/post-monthly', [\App\Http\Controllers\AsetDepreciationController::class, 'postMonthly'])->name('penyusutan.aset.post');
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
