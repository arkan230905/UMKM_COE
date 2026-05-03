<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\StockBackfillPembelian::class,
        \App\Console\Commands\SyncAccounts::class,
        \App\Console\Commands\FixDepreciationDiscrepancy::class,
        \App\Console\Commands\FixApril2026Depreciation::class,
        \App\Console\Commands\UpdateApril2026JournalValues::class,
        \App\Console\Commands\ValidateKasBankConsistency::class,
        \App\Console\Commands\SyncHPP::class,
        \App\Console\Commands\DebugPembayaranBeban::class,
        \App\Console\Commands\DebugPembayaranBebanDetail::class,
        \App\Console\Commands\DebugBebanOperasional::class,
        \App\Console\Commands\DebugLaporanBudget::class,
        \App\Console\Commands\DebugPembayaranDetail::class,
        \App\Console\Commands\DebugDirectQuery::class,
        \App\Console\Commands\DebugPenjualanJournal::class,
        \App\Console\Commands\CheckDatabase::class,
        \App\Console\Commands\DebugJurnalUmum::class,
        \App\Console\Commands\DebugExactJurnalQuery::class,
        \App\Console\Commands\DebugJurnalFilter::class,
        \App\Console\Commands\FindSaleUser::class,
        \App\Console\Commands\CreateTestSale::class,
        \App\Console\Commands\CheckNewSale::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily consistency check for Kas Bank data
        $schedule->command('kasbank:validate-consistency --days=1 --log')
                ->dailyAt('02:00')
                ->runInBackground()
                ->onSuccess(function () {
                    \Log::info('Daily Kas Bank consistency check completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Daily Kas Bank consistency check failed');
                });
        
        // Weekly comprehensive check
        $schedule->command('kasbank:validate-consistency --days=7 --log')
                ->weeklyOn(1, '03:00') // Monday at 3 AM
                ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
