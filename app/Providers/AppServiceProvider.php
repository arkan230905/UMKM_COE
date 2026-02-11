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
        
        // Real-time stock tracking observers
        PembelianDetail::observe(PembelianDetailObserver::class);
        ProduksiDetail::observe(ProduksiDetailObserver::class);
        PenjualanDetail::observe(PenjualanDetailObserver::class);

        // View composer global untuk semua tampilan
        View::composer('*', function ($view) {
            $view->with([
                'totalPegawai'   => Pegawai::count(),
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
