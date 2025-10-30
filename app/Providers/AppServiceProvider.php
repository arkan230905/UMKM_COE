<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

// Import semua model yang digunakan untuk sidebar
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Produk;
use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\Satuan;
use App\Models\Coa;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
    }
}
