
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

// Pelanggan E-Commerce
use App\Http\Controllers\Pelanggan\DashboardController as PelangganDashboardController;
use App\Http\Controllers\Pelanggan\CartController;
use App\Http\Controllers\Pelanggan\CheckoutController;
use App\Http\Controllers\Pelanggan\OrderController as PelangganOrderController;
use App\Http\Controllers\Pelanggan\FavoriteController as PelangganFavoriteController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\Auth\RegisterController;

// Pegawai Pembelian
use App\Http\Controllers\PegawaiPembelian\DashboardController as PegawaiPembelianDashboardController;
use App\Http\Controllers\PegawaiPembelian\BahanBakuController as PegawaiPembelianBahanBakuController;
use App\Http\Controllers\PegawaiPembelian\VendorController as PegawaiPembelianVendorController;
use App\Http\Controllers\PegawaiPembelian\PembelianController as PegawaiPembelianPembelianController;

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

// Route password sudah ada di auth.php, tidak perlu duplikat


// ====================================================================
// SEMUA ROUTE YANG HANYA BISA DIAKSES SETELAH LOGIN
// ====================================================================
Route::middleware('auth')->group(function () {

    // ================================================================
    // DASHBOARD (Admin & Owner Only)
    // ================================================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('role:admin,owner')->name('dashboard');

    // ================================================================
    // PELANGGAN E-COMMERCE ROUTES
    // ================================================================
    Route::prefix('pelanggan')->name('pelanggan.')->group(function () {
        // Dashboard - Katalog Produk
        Route::get('/dashboard', [PelangganDashboardController::class, 'index'])->name('dashboard');
        
        // Cart
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::put('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
        Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
        
        // Checkout
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
        
        // Orders
        Route::get('/orders', [PelangganOrderController::class, 'index'])->name('orders');
        Route::get('/orders/{order}', [PelangganOrderController::class, 'show'])->name('orders.show');

        // Favorites
        Route::get('/favorites', [PelangganFavoriteController::class, 'index'])->name('favorites');
        Route::post('/favorites/toggle', [PelangganFavoriteController::class, 'toggle'])->name('favorites.toggle');

        // Returns (Retur Penjualan)
        Route::get('/returns', [\App\Http\Controllers\Pelanggan\ReturnController::class, 'index'])->name('returns.index');
        Route::get('/returns/create', [\App\Http\Controllers\Pelanggan\ReturnController::class, 'create'])->name('returns.create');
        Route::post('/returns', [\App\Http\Controllers\Pelanggan\ReturnController::class, 'store'])->name('returns.store');

        // Reviews
        Route::post('/reviews', [\App\Http\Controllers\Pelanggan\ReviewController::class, 'store'])->name('reviews.store');
        Route::put('/reviews/{id}', [\App\Http\Controllers\Pelanggan\ReviewController::class, 'update'])->name('reviews.update');
    });

    // Midtrans Callback (tidak perlu auth karena dari server Midtrans)
    Route::post('/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');

    // ================================================================
    // PEGAWAI PEMBELIAN BAHAN BAKU ROUTES
    // ================================================================
    Route::prefix('pegawaipembelianbahanbaku')->name('pegawai-pembelian.')->middleware('role:pegawai_pembelian')->group(function () {
        // Dashboard
        Route::get('/dashboard', [PegawaiPembelianDashboardController::class, 'index'])->name('dashboard');
        
        // Bahan Baku (CRUD)
        Route::resource('bahan-baku', PegawaiPembelianBahanBakuController::class)->names([
            'index' => 'bahan-baku.index',
            'create' => 'bahan-baku.create',
            'store' => 'bahan-baku.store',
            'show' => 'bahan-baku.show',
            'edit' => 'bahan-baku.edit',
            'update' => 'bahan-baku.update',
            'destroy' => 'bahan-baku.destroy',
        ]);
        
        // Vendor (CRUD)
        Route::resource('vendor', PegawaiPembelianVendorController::class)->names([
            'index' => 'vendor.index',
            'create' => 'vendor.create',
            'store' => 'vendor.store',
            'show' => 'vendor.show',
            'edit' => 'vendor.edit',
            'update' => 'vendor.update',
            'destroy' => 'vendor.destroy',
        ]);
        
        // Pembelian (CRUD)
        Route::resource('pembelian', PegawaiPembelianPembelianController::class)->names([
            'index' => 'pembelian.index',
            'create' => 'pembelian.create',
            'store' => 'pembelian.store',
            'show' => 'pembelian.show',
            'edit' => 'pembelian.edit',
            'update' => 'pembelian.update',
            'destroy' => 'pembelian.destroy',
        ]);
        
        // Retur Pembelian (CRUD)
        Route::prefix('retur')->name('retur.')->group(function() {
            Route::get('/', [ReturController::class, 'indexPembelian'])->name('index');
            Route::get('/create', [ReturController::class, 'createPembelian'])->name('create');
            Route::post('/', [ReturController::class, 'storePembelian'])->name('store');
            Route::get('/{id}', [ReturController::class, 'showPembelian'])->name('show');
            Route::delete('/{id}', [ReturController::class, 'destroyPembelian'])->name('destroy');
        });
        
        // Laporan
        Route::get('/laporan/pembelian', [LaporanController::class, 'pembelian'])->name('laporan.pembelian');
        Route::get('/laporan/retur', [LaporanController::class, 'laporanRetur'])->name('laporan.retur');
    });

    // ================================================================
    // ASSET (redirect to master-data aset)
    // ================================================================
    Route::get('/aset', function() {
        return redirect()->route('master-data.aset.index');
    });
    Route::get('/aset/{id}', function($id) {
        return redirect()->route('master-data.aset.show', $id);
    });

    // ================================================================
    // MASTER DATA (Admin & Owner Only)
    // ================================================================
    Route::prefix('master-data')->name('master-data.')->middleware('role:admin,owner')->group(function () {
        // Pelanggan
        Route::resource('pelanggan', \App\Http\Controllers\MasterData\PelangganController::class)->except(['create', 'store']);
        
        // Bahan Baku
        Route::resource('bahan-baku', BahanBakuController::class);
        Route::resource('coa', CoaController::class);
        Route::get('coa/generate-kode', [CoaController::class, 'generateKode'])->name('coa.generate-kode');
        Route::resource('aset', AsetController::class);
        Route::get('aset-kategori-by-jenis', [AsetController::class, 'getKategoriByJenis'])->name('aset.kategori-by-jenis');
        Route::resource('jabatan', JabatanController::class);
        Route::resource('pegawai', PegawaiController::class);
        // Presensi routes
        Route::resource('presensi', PresensiController::class);
        Route::resource('vendor', VendorController::class);
        Route::resource('satuan', SatuanController::class);
        
        // Produk routes with proper naming
        Route::prefix('produk')->name('produk.')->group(function () {
            Route::get('/', [ProdukController::class, 'index'])->name('index');
            Route::get('/create', [ProdukController::class, 'create'])->name('create');
            Route::get('/print-barcode-all', [ProdukController::class, 'printBarcodeAll'])->name('print-barcode-all');
            Route::post('/', [ProdukController::class, 'store'])->name('store');
            Route::get('/{produk}', [ProdukController::class, 'show'])->name('show');
            Route::get('/{produk}/edit', [ProdukController::class, 'edit'])->name('edit');
            Route::get('/{produk}/print-barcode', [ProdukController::class, 'printBarcode'])->name('print-barcode');
            Route::put('/{produk}', [ProdukController::class, 'update'])->name('update');
            Route::delete('/{produk}', [ProdukController::class, 'destroy'])->name('destroy');
        });
        
        // BOP Routes
        Route::prefix('bop')->name('bop.')->group(function () {
            Route::get('/', [\App\Http\Controllers\MasterData\BopController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\MasterData\BopController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\MasterData\BopController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [\App\Http\Controllers\MasterData\BopController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\MasterData\BopController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\MasterData\BopController::class, 'destroy'])->name('destroy');
        });
        
        // BOP Budget Routes
        Route::prefix('bop-budget')->name('bop-budget.')->group(function () {
            Route::get('/', [BopBudgetController::class, 'index'])->name('index');
            Route::get('/create', [BopBudgetController::class, 'create'])->name('create');
            Route::post('/', [BopBudgetController::class, 'store'])->name('store');
            Route::get('/{bopBudget}/edit', [BopBudgetController::class, 'edit'])->name('edit');
            Route::put('/{bopBudget}', [BopBudgetController::class, 'update'])->name('update');
            Route::delete('/{bopBudget}', [BopBudgetController::class, 'destroy'])->name('destroy');
        });
        // BOM Routes
        Route::prefix('bom')->name('bom.')->group(function () {
            Route::get('calculate/{produkId}', [BomController::class, 'calculateBomCost'])->name('calculate');
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
        
        // Proses Produksi Routes (Process Costing)
        Route::prefix('proses-produksi')->name('proses-produksi.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ProsesProduksiController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\ProsesProduksiController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\ProsesProduksiController::class, 'store'])->name('store');
            Route::get('/{prosesProduksi}', [\App\Http\Controllers\ProsesProduksiController::class, 'show'])->name('show');
            Route::get('/{prosesProduksi}/edit', [\App\Http\Controllers\ProsesProduksiController::class, 'edit'])->name('edit');
            Route::put('/{prosesProduksi}', [\App\Http\Controllers\ProsesProduksiController::class, 'update'])->name('update');
            Route::delete('/{prosesProduksi}', [\App\Http\Controllers\ProsesProduksiController::class, 'destroy'])->name('destroy');
        });
        
        // Komponen BOP Routes (Overhead Components)
        Route::prefix('komponen-bop')->name('komponen-bop.')->group(function () {
            Route::get('/', [\App\Http\Controllers\KomponenBopController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\KomponenBopController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\KomponenBopController::class, 'store'])->name('store');
            Route::get('/{komponenBop}', [\App\Http\Controllers\KomponenBopController::class, 'show'])->name('show');
            Route::get('/{komponenBop}/edit', [\App\Http\Controllers\KomponenBopController::class, 'edit'])->name('edit');
            Route::put('/{komponenBop}', [\App\Http\Controllers\KomponenBopController::class, 'update'])->name('update');
            Route::delete('/{komponenBop}', [\App\Http\Controllers\KomponenBopController::class, 'destroy'])->name('destroy');
        });
    });


    // ================================================================
    // TRANSAKSI (Admin & Owner Only)
    // ================================================================
    Route::prefix('transaksi')->name('transaksi.')->middleware('role:admin,owner')->group(function () {
        // ============================================================
        // ✅ PEMBAYARAN BEBAN (Expense Payment)
        // ============================================================
        Route::prefix('pembayaran-beban')->name('pembayaran-beban.')->group(function() {
            Route::get('/', [ExpensePaymentController::class, 'index'])->name('index');
            Route::get('/create', [ExpensePaymentController::class, 'create'])->name('create');
            Route::post('/', [ExpensePaymentController::class, 'store'])->name('store');
            Route::get('/{id}', [ExpensePaymentController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [ExpensePaymentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ExpensePaymentController::class, 'update'])->name('update');
            Route::delete('/{id}', [ExpensePaymentController::class, 'destroy'])->name('destroy');
            Route::get('/print/{id}', [ExpensePaymentController::class, 'print'])->name('print');
        });

        // Alias route untuk backward compatibility - LANGSUNG KE CONTROLLER
        Route::prefix('expense-payment')->group(function() {
            Route::get('/', [ExpensePaymentController::class, 'index']);
            Route::get('/create', [ExpensePaymentController::class, 'create']);
            Route::post('/', [ExpensePaymentController::class, 'store']);
            Route::get('/{id}', [ExpensePaymentController::class, 'show']);
            Route::get('/{id}/edit', [ExpensePaymentController::class, 'edit']);
            Route::put('/{id}', [ExpensePaymentController::class, 'update']);
            Route::delete('/{id}', [ExpensePaymentController::class, 'destroy']);
        });

        // ============================================================
        // ✅ PELUNASAN UTANG (AP Settlement)
        // ============================================================
        Route::prefix('pelunasan-utang')->name('pelunasan-utang.')->group(function() {
            Route::get('/', [ApSettlementController::class, 'index'])->name('index');
            Route::get('/create', [ApSettlementController::class, 'create'])->name('create');
            Route::post('/', [ApSettlementController::class, 'store'])->name('store');
            Route::get('/{id}', [ApSettlementController::class, 'show'])->name('show');
            Route::get('/print/{id}', [ApSettlementController::class, 'print'])->name('print');
            Route::delete('/{id}', [ApSettlementController::class, 'destroy'])->name('destroy');
            Route::get('/get-pembelian/{id}', [ApSettlementController::class, 'getPembelian'])->name('get-pembelian');
        });

        // ============================================================
        // ✅ PENGGAJIAN
        // ============================================================
        Route::prefix('penggajian')->name('penggajian.')->group(function() {
            Route::get('/', [PenggajianController::class, 'index'])->name('index');
            Route::get('/create', [PenggajianController::class, 'create'])->name('create');
            Route::post('/', [PenggajianController::class, 'store'])->name('store');
            Route::get('/{id}', [PenggajianController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PenggajianController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PenggajianController::class, 'update'])->name('update');
            Route::delete('/{id}', [PenggajianController::class, 'destroy'])->name('destroy');
            Route::get('/print/{id}', [PenggajianController::class, 'print'])->name('print');
        });

        // ============================================================
        // ✅ PEMBELIAN
        // ============================================================
        Route::prefix('pembelian')->name('pembelian.')->group(function() {
            Route::get('/', [PembelianController::class, 'index'])->name('index');
            Route::get('/create', [PembelianController::class, 'create'])->name('create');
            Route::post('/', [PembelianController::class, 'store'])->name('store');
            Route::get('/{pembelian}', [PembelianController::class, 'show'])->name('show');
            Route::get('/{pembelian}/edit', [PembelianController::class, 'edit'])->name('edit');
            Route::put('/{pembelian}', [PembelianController::class, 'update'])->name('update');
            Route::delete('/{pembelian}', [PembelianController::class, 'destroy'])->name('destroy');
        });

        // ============================================================
        // ✅ PENJUALAN
        // ============================================================
        Route::resource('penjualan', PenjualanController::class);
        Route::get('penjualan/barcode/{barcode}', [PenjualanController::class, 'findByBarcode'])->name('penjualan.barcode');

        // ============================================================
        // ✅ RETUR
        // ============================================================
        Route::resource('retur', ReturController::class);
        Route::post('retur/{id}/approve', [ReturController::class, 'approve'])->name('retur.approve');
        Route::post('retur/{id}/post', [ReturController::class, 'post'])->name('retur.post');
        
        // Retur Pembelian
        Route::prefix('retur-pembelian')->name('retur-pembelian.')->group(function() {
            Route::get('/', [ReturController::class, 'indexPembelian'])->name('index');
            Route::get('/create', [ReturController::class, 'createPembelian'])->name('create');
            Route::post('/', [ReturController::class, 'storePembelian'])->name('store');
            Route::get('/{id}', [ReturController::class, 'showPembelian'])->name('show');
            Route::delete('/{id}', [ReturController::class, 'destroyPembelian'])->name('destroy');
        });
        
        // Retur Penjualan
        Route::prefix('retur-penjualan')->name('retur-penjualan.')->group(function() {
            Route::get('/', [ReturController::class, 'indexPenjualan'])->name('index');
            Route::get('/create', [ReturController::class, 'createPenjualan'])->name('create');
            Route::post('/', [ReturController::class, 'storePenjualan'])->name('store');
            Route::get('/{id}', [ReturController::class, 'showPenjualan'])->name('show');
            Route::delete('/{id}', [ReturController::class, 'destroyPenjualan'])->name('destroy');
        });



        // ============================================================
        // ✅ PRODUKSI
        // ============================================================
        Route::prefix('produksi')->name('produksi.')->group(function() {
            Route::get('/', [ProduksiController::class, 'index'])->name('index');
            Route::get('/create', [ProduksiController::class, 'create'])->name('create');
            Route::post('/', [ProduksiController::class, 'store'])->name('store');
            Route::get('/{id}', [ProduksiController::class, 'show'])->name('show');
            Route::post('/{id}/complete', [ProduksiController::class, 'complete'])->name('complete');
            Route::delete('/{id}', [ProduksiController::class, 'destroy'])->name('destroy');
        });

        // Route expense-payment sudah ada di atas dengan prefix pembayaran-beban
        // Tidak perlu duplikat

        // ============================================================
        // ✅ PELUNASAN UTANG
        // ============================================================
        Route::prefix('ap-settlement')->name('ap-settlement.')->group(function() {
            Route::get('/', [ApSettlementController::class, 'index'])->name('index');
            Route::get('/create', [ApSettlementController::class, 'create'])->name('create');
            Route::post('/', [ApSettlementController::class, 'store'])->name('store');
            Route::get('/{id}', [ApSettlementController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [ApSettlementController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ApSettlementController::class, 'update'])->name('update');
            Route::delete('/{id}', [ApSettlementController::class, 'destroy'])->name('destroy');
            Route::get('/print/{id}', [ApSettlementController::class, 'print'])->name('print');
        });

    });


    // ================================================================
    // LAPORAN (Admin & Owner Only)
    // ================================================================
    Route::prefix('laporan')->name('laporan.')->middleware('role:admin,owner')->group(function() {
        // Laporan Stok
        Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
        
        // Laporan Pembelian
        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');
        Route::get('/pembelian/{id}/invoice', [LaporanController::class, 'invoicePembelian'])->name('pembelian.invoice');
        Route::get('/pembelian/export', [LaporanController::class, 'exportPembelian'])->name('pembelian.export');
        Route::get('/export/pembelian', [LaporanController::class, 'exportPembelian'])->name('export.pembelian');
        
        // Laporan Penjualan
        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('/penjualan/{id}/invoice', [LaporanController::class, 'invoice'])->name('penjualan.invoice');
        Route::get('/penjualan/export', [LaporanController::class, 'exportPenjualan'])->name('penjualan.export');
        Route::get('/export/penjualan', [LaporanController::class, 'exportPenjualan'])->name('export.penjualan');
        
        // Laporan Retur
        Route::get('/retur', [LaporanController::class, 'laporanRetur'])->name('retur');
        
        // Laporan Penggajian
        Route::get('/penggajian', [LaporanController::class, 'laporanPenggajian'])->name('penggajian');
        Route::get('/pembayaran-beban', [LaporanController::class, 'laporanPembayaranBeban'])->name('pembayaran-beban');
        Route::get('/pelunasan-utang', [LaporanController::class, 'laporanPelunasanUtang'])->name('pelunasan-utang');
        Route::get('/aliran-kas', [LaporanController::class, 'laporanAliranKas'])->name('aliran-kas');
        
        // Laporan Kas & Bank
        Route::get('/kas-bank', [\App\Http\Controllers\LaporanKasBankController::class, 'index'])->name('kas-bank');
        Route::get('/kas-bank/export-pdf', [\App\Http\Controllers\LaporanKasBankController::class, 'exportPdf'])->name('kas-bank.export-pdf');
        Route::get('/kas-bank/export-excel', [\App\Http\Controllers\LaporanKasBankController::class, 'exportExcel'])->name('kas-bank.export-excel');
        Route::get('/kas-bank/{coaId}/detail-masuk', [\App\Http\Controllers\LaporanKasBankController::class, 'getDetailMasuk'])->name('kas-bank.detail-masuk');
        Route::get('/kas-bank/{coaId}/detail-keluar', [\App\Http\Controllers\LaporanKasBankController::class, 'getDetailKeluar'])->name('kas-bank.detail-keluar');
        
        // Laporan Aset
        Route::get('/penyusutan-aset', [\App\Http\Controllers\AsetDepreciationController::class, 'index'])->name('penyusutan.aset');
        Route::get('/penyusutan-aset/{id}', [\App\Http\Controllers\AsetDepreciationController::class, 'show'])->name('penyusutan.aset.show');
        Route::post('/penyusutan-aset/post-monthly', [\App\Http\Controllers\AsetDepreciationController::class, 'postMonthly'])->name('penyusutan.aset.post');
        
        // Ekspor Laporan
        Route::get('/export/retur', function() {
            return app()->call('App\Http\Controllers\LaporanController@laporanRetur', ['export' => 'pdf']);
        })->name('export.retur');
        
        Route::get('/export/penggajian', function() {
            return app()->call('App\Http\Controllers\LaporanController@laporanPenggajian', ['export' => 'pdf']);
        })->name('export.penggajian');
        
        Route::get('/export/pembayaran-beban', function() {
            return app()->call('App\Http\Controllers\LaporanController@laporanPembayaranBeban', ['export' => 'pdf']);
        })->name('export.pembayaran-beban');
        
        Route::get('/export/pelunasan-utang', function() {
            return app()->call('App\Http\Controllers\LaporanController@laporanPelunasanUtang', ['export' => 'pdf']);
        })->name('export.pelunasan-utang');
    });

    // ================================================================
    // AKUNTANSI (Admin & Owner Only)
    // ================================================================
    Route::prefix('akuntansi')->name('akuntansi.')->middleware('role:admin,owner')->group(function () {
        Route::get('/jurnal-umum', [\App\Http\Controllers\AkuntansiController::class, 'jurnalUmum'])->name('jurnal-umum');
        Route::get('/jurnal-umum/export-pdf', [\App\Http\Controllers\AkuntansiController::class, 'jurnalUmumExportPdf'])->name('jurnal-umum.export-pdf');
        Route::get('/jurnal-umum/export-excel', [\App\Http\Controllers\AkuntansiController::class, 'jurnalUmumExportExcel'])->name('jurnal-umum.export-excel');
        Route::get('/buku-besar', [\App\Http\Controllers\AkuntansiController::class, 'bukuBesar'])->name('buku-besar');
        Route::get('/buku-besar/export-excel', [\App\Http\Controllers\AkuntansiController::class, 'bukuBesarExportExcel'])->name('buku-besar.export-excel');
        Route::get('/neraca-saldo', [\App\Http\Controllers\AkuntansiController::class, 'neracaSaldo'])->name('neraca-saldo');
        Route::get('/laba-rugi', [\App\Http\Controllers\AkuntansiController::class, 'labaRugi'])->name('laba-rugi');
    });

    // ================================================================
    // COA PERIOD MANAGEMENT
    // ================================================================
    Route::post('/coa-period/{periodId}/post', [\App\Http\Controllers\CoaPeriodController::class, 'postPeriod'])->name('coa-period.post');
    Route::post('/coa-period/{periodId}/reopen', [\App\Http\Controllers\CoaPeriodController::class, 'reopenPeriod'])->name('coa-period.reopen');


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
