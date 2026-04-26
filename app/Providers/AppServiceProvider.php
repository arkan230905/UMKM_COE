<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Jabatan;
use App\Observers\JabatanObserver;
use App\Models\BahanBaku;
use App\Observers\BahanBakuObserver;
use App\Models\BahanPendukung;
use App\Observers\BahanPendukungObserver;
use App\Models\Btkl;
use App\Observers\BtklObserver;
use App\Models\BopProses;
use App\Observers\BopProsesObserver;
use App\Services\BiayaBahanService;

// Import semua model yang digunakan untuk sidebar
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Produk;
use App\Observers\ProdukObserver;
use App\Models\PembelianDetail;
use App\Observers\PembelianDetailObserver;
use App\Models\ProduksiDetail;
use App\Observers\ProduksiDetailObserver;
use App\Models\PenjualanDetail;
use App\Observers\PenjualanDetailObserver;
use App\Models\Vendor;
use App\Models\Satuan;
use App\Models\Coa;
use App\Observers\ConversionConsistencyObserver;
use App\Models\User;
use App\Observers\PelangganUserObserver;
use App\Observers\UserObserver;
use App\Models\Pembelian;
use App\Observers\PembelianObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BiayaBahanService::class, function ($app) {
            return new BiayaBahanService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Jabatan::observe(JabatanObserver::class);
        BahanBaku::observe(BahanBakuObserver::class);
        BahanPendukung::observe(BahanPendukungObserver::class);
        Btkl::observe(BtklObserver::class);
        BopProses::observe(BopProsesObserver::class);
        Produk::observe(ProdukObserver::class);
        
        // Conversion consistency observers for stock report
        BahanBaku::observe(ConversionConsistencyObserver::class);
        BahanPendukung::observe(ConversionConsistencyObserver::class);
        Produk::observe(ConversionConsistencyObserver::class);
        
        // Real-time stock tracking observers
        PembelianDetail::observe(PembelianDetailObserver::class);
        Pembelian::observe(PembelianObserver::class);
        ProduksiDetail::observe(ProduksiDetailObserver::class);
        PenjualanDetail::observe(PenjualanDetailObserver::class);

        // Auto-update BOM prices when stock movements occur (based on stock report calculations)
        // DISABLED: StockMovement::observe(StockMovementBomObserver::class);

        // Sync pelanggan dari users ke pelanggans
        User::observe(PelangganUserObserver::class);
        
        // Initialize master data untuk user baru
        User::observe(UserObserver::class);

        // View composer global untuk semua tampilan
        View::composer('*', function ($view) {
            $view->with([
                'totalPegawai'   => 0,
                'totalPresensi'  => Presensi::count(),
                'totalProduk'    => Produk::count(),
                'totalVendor'    => Vendor::count(),
                'totalBahanBaku' => BahanBaku::count(),
                'totalSatuan'    => Satuan::count(),
                'totalCOA'       => Coa::count(),
            ]);
        });

        // View composer untuk layout pelanggan (cart count)
        View::composer('layouts.pelanggan', \App\Http\View\Composers\CartComposer::class);
    }
}
