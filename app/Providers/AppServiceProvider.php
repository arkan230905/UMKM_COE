<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
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

        if (! Collection::hasMacro('links')) {
            Collection::macro('links', function (...$parameters) {
                Log::warning('links() called on Collection without pagination.', [
                    'collection_class' => static::class,
                    'count' => $this->count(),
                    'route_name' => optional(request()->route())->getName(),
                    'request_path' => request()->path(),
                ]);

                return '';
            });
        }
    }
}
