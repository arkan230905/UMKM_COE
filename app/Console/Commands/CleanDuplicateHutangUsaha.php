<?php

namespace App\Console\Commands;

use App\Models\Coa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateHutangUsaha extends Command
{
    protected $signature = 'coa:clean-hutang-usaha';
    protected $description = 'Clean duplicate Hutang Usaha COA accounts, keeping only 2101';

    public function handle()
    {
        $this->info('🔍 Checking for duplicate Hutang Usaha accounts...');
        
        // Get all Hutang Usaha accounts
        $hutangUsahas = Coa::where('nama_akun', 'Hutang Usaha')
            ->orderBy('kode_akun')
            ->get();
        
        $this->info("Found " . $hutangUsahas->count() . " Hutang Usaha accounts:");
        foreach ($hutangUsahas as $coa) {
            $this->line("  - ID: {$coa->id}, Kode: {$coa->kode_akun}, Nama: {$coa->nama_akun}");
        }
        
        if ($hutangUsahas->count() <= 1) {
            $this->info('✅ No duplicates found.');
            return 0;
        }
        
        // Keep only 2101, delete others
        $toKeep = $hutangUsahas->where('kode_akun', '2101')->first();
        
        if (!$toKeep) {
            $this->error('❌ COA 2101 not found. Cannot proceed.');
            return 1;
        }
        
        $this->info("\n📌 Keeping: ID {$toKeep->id}, Kode: {$toKeep->kode_akun}");
        
        // Delete duplicates
        $toDelete = $hutangUsahas->where('id', '!=', $toKeep->id);
        
        if ($toDelete->isEmpty()) {
            $this->info('✅ No duplicates to delete.');
            return 0;
        }
        
        $this->warn("\n⚠️  Will delete " . $toDelete->count() . " duplicate accounts:");
        foreach ($toDelete as $coa) {
            $this->line("  - ID: {$coa->id}, Kode: {$coa->kode_akun}");
        }
        
        if (!$this->confirm('Continue with deletion?')) {
            $this->info('Cancelled.');
            return 0;
        }
        
        // Delete duplicates
        foreach ($toDelete as $coa) {
            try {
                $coa->delete();
                $this->line("  ✓ Deleted ID {$coa->id}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to delete ID {$coa->id}: " . $e->getMessage());
            }
        }
        
        $this->info("\n✅ Cleanup completed!");
        
        // Verify
        $remaining = Coa::where('nama_akun', 'Hutang Usaha')->count();
        $this->info("Remaining Hutang Usaha accounts: {$remaining}");
        
        return 0;
    }
}
