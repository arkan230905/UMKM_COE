<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Jabatan;
use App\Observers\JabatanObserver;

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
        Jabatan::observe(JabatanObserver::class);

        // View composer global untuk semua tampilan
        View::composer('*', function ($view) {
            try {
                $totalPegawai = Pegawai::count();
            } catch (\Exception $e) {
                $totalPegawai = 0;
            }
            
            try {
                $totalPresensi = Presensi::count();
            } catch (\Exception $e) {
                $totalPresensi = 0;
            }
            
            try {
                $totalProduk = Produk::count();
            } catch (\Exception $e) {
                $totalProduk = 0;
            }
            
            try {
                $totalVendor = Vendor::count();
            } catch (\Exception $e) {
                $totalVendor = 0;
            }
            
            try {
                $totalBahanBaku = BahanBaku::count();
            } catch (\Exception $e) {
                $totalBahanBaku = 0;
            }
            
            try {
                $totalSatuan = Satuan::count();
            } catch (\Exception $e) {
                $totalSatuan = 0;
            }
            
            try {
                $totalCOA = Coa::count();
            } catch (\Exception $e) {
                $totalCOA = 0;
            }
            
            $view->with([
                'totalPegawai'   => $totalPegawai,
                'totalPresensi'  => $totalPresensi,
                'totalProduk'    => $totalProduk,
                'totalVendor'    => $totalVendor,
                'totalBahanBaku' => $totalBahanBaku,
                'totalSatuan'    => $totalSatuan,
                'totalCOA'       => $totalCOA,
            ]);
        });

        // View composer untuk layout pelanggan (cart count)
        View::composer('layouts.pelanggan', \App\Http\View\Composers\CartComposer::class);
    }
}
