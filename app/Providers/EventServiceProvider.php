<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Btkl;
use App\Models\ProsesProduksi;
use App\Models\BopProses;
use App\Models\Produksi;
use App\Models\User;
use App\Services\BomSyncService;
use Illuminate\Support\Facades\Log;
use App\Observers\BahanBakuObserver;
use App\Observers\BahanPendukungObserver;
use App\Observers\PembelianObserver;
use App\Observers\PembelianJournalObserver;
use App\Observers\PenjualanObserver;
use App\Observers\ProduksiObserver;
use App\Observers\BtklObserver;
use App\Observers\UserObserver;

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
        'App\Events\UserRegistered' => [
            'App\Listeners\CreateDefaultUserData',
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
        Pembelian::observe(PembelianJournalObserver::class);
        // Penjualan::observe(PenjualanObserver::class); // DISABLED: Replaced by JournalService::createJournalFromPenjualan
        Produksi::observe(ProduksiObserver::class);
        Btkl::observe(BtklObserver::class);
        User::observe(UserObserver::class);
    }
}
