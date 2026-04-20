<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class CleanDuplicateCoa extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'coa:clean-duplicates {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean duplicate COA accounts, keeping the oldest record for each duplicate';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for duplicate COA accounts...');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }

        // Check for duplicate kode_akun
        $duplicateKodes = Coa::select('kode_akun')
            ->groupBy('kode_akun')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('kode_akun');

        $deletedCount = 0;

        if ($duplicateKodes->count() > 0) {
            $this->error("❌ Found duplicate kode_akun: {$duplicateKodes->count()}");
            
            foreach ($duplicateKodes as $kode) {
                $accounts = Coa::where('kode_akun', $kode)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $this->line("   Kode: {$kode} - Found {$accounts->count()} records");
                
                // Keep the first (oldest) record, delete the rest
                $toKeep = $accounts->first();
                $toDelete = $accounts->skip(1);
                
                $this->line("     Keeping: ID {$toKeep->id} - {$toKeep->nama_akun} (Created: {$toKeep->created_at})");
                
                foreach ($toDelete as $duplicate) {
                    $this->line("     " . ($dryRun ? 'Would delete' : 'Deleting') . ": ID {$duplicate->id} - {$duplicate->nama_akun} (Created: {$duplicate->created_at})");
                    
                    if (!$dryRun) {
                        // Check if this COA is used in any transactions
                        $usageCount = DB::table('jurnal_umums')->where('coa_id', $duplicate->id)->count();
                        
                        if ($usageCount > 0) {
                            $this->warn("       ⚠️  Cannot delete ID {$duplicate->id} - used in {$usageCount} journal entries");
                            $this->warn("       💡 Consider merging journal entries to the kept record first");
                        } else {
                            $duplicate->delete();
                            $deletedCount++;
                            $this->line("       ✅ Deleted successfully");
                        }
                    }
                }
            }
        } else {
            $this->info('✅ No duplicate kode_akun found');
        }

        // Check for duplicate nama_akun (but different kode_akun)
        $duplicateNames = Coa::select('nama_akun')
            ->groupBy('nama_akun')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('nama_akun');

        if ($duplicateNames->count() > 0) {
            $this->warn("⚠️  Found duplicate nama_akun: {$duplicateNames->count()}");
            $this->warn("    These have different kode_akun but same names - manual review recommended:");
            
            foreach ($duplicateNames as $name) {
                $accounts = Coa::where('nama_akun', $name)->get();
                $this->line("   Name: {$name}");
                foreach ($accounts as $acc) {
                    $this->line("     Kode: {$acc->kode_akun}, ID: {$acc->id}");
                }
            }
        } else {
            $this->info('✅ No duplicate nama_akun found');
        }

        $totalRecords = Coa::count();
        $this->info("📊 Total COA records: {$totalRecords}");
        
        if (!$dryRun && $deletedCount > 0) {
            $this->info("🗑️  Deleted {$deletedCount} duplicate records");
            $this->info("💡 Run 'php artisan coa:update-seeder --force' to update the seeder");
        } elseif ($dryRun && $duplicateKodes->count() > 0) {
            $this->info("💡 Run without --dry-run to actually clean duplicates");
        }

        return Command::SUCCESS;
    }
}