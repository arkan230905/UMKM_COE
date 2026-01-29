<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Observers\BahanBakuObserver;
use App\Observers\BahanPendukungObserver;
use App\Observers\PembelianObserver;
use App\Observers\PenjualanObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        'App\Events\BopUpdated' => [
            'App\Listeners\UpdateBopAktual',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register observers
        BahanBaku::observe(BahanBakuObserver::class);
        BahanPendukung::observe(BahanPendukungObserver::class);
        Pembelian::observe(PembelianObserver::class);
        Penjualan::observe(PenjualanObserver::class);
    }
}
