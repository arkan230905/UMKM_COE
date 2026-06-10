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
        \App\Console\Commands\ResetCoaJasuke::class,
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
        \App\Console\Commands\FixSaleUserIds::class,
        \App\Console\Commands\CheckHPPCoa::class,
        \App\Console\Commands\CreateHPPCoa::class,
        \App\Console\Commands\CheckCoaStructure::class,
        \App\Console\Commands\UpdateExistingHPP::class,
        \App\Console\Commands\CheckPurchaseJournal::class,
        \App\Console\Commands\CreateTestPurchase::class,
        \App\Console\Commands\CheckPembelianStructure::class,
        \App\Console\Commands\CheckPembelianDetailStructure::class,
        \App\Console\Commands\DebugPurchaseJournal::class,
        \App\Console\Commands\CreateMissingPurchaseJournals::class,
        \App\Console\Commands\ComparePurchaseData::class,
        \App\Console\Commands\FixPurchaseTotals::class,
        \App\Console\Commands\VerifyPurchaseFix::class,
        \App\Console\Commands\FixBopCoaMapping::class,
        \App\Console\Commands\FixCoaPeriodBalanceColumn::class,
        \App\Console\Commands\CheckHPPCoaType::class,
        \App\Console\Commands\DebugLabaRugiData::class,
        \App\Console\Commands\DebugRealLabaRugi::class,
        \App\Console\Commands\CheckPendapatanData::class,
        \App\Console\Commands\DebugMinusBeban::class,
        \App\Console\Commands\FixMinusBeban::class,
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
