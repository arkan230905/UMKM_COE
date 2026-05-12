<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalLine;
use App\Models\Coa;
use App\Models\JurnalUmum;

class UpdateExistingHPP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:existing-hpp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing HPP journal entries from COA 51 to COA 560';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== UPDATING EXISTING HPP JOURNAL ENTRIES ===");
        
        // Get COA 51 (old) and COA 560 (new)
        $oldCoa = Coa::where('kode_akun', '51')->first();
        $newCoa = Coa::where('kode_akun', '560')->first();
        
        if (!$oldCoa) {
            $this->info("❌ COA 51 not found");
            return Command::FAILURE;
        }
        
        if (!$newCoa) {
            $this->info("❌ COA 560 not found");
            return Command::FAILURE;
        }
        
        $this->info("Old COA: {$oldCoa->kode_akun} - {$oldCoa->nama_akun}");
        $this->info("New COA: {$newCoa->kode_akun} - {$newCoa->nama_akun}");
        
        // Update journal_lines
        $journalLines = JournalLine::where('coa_id', $oldCoa->id)->get();
        $this->info("Found {$journalLines->count()} journal lines to update");
        
        $updatedLines = 0;
        foreach ($journalLines as $line) {
            $line->coa_id = $newCoa->id;
            $line->save();
            $updatedLines++;
            $this->info("✅ Updated Journal Line ID: {$line->id}");
        }
        
        // Update jurnal_umum entries
        $jurnalUmumEntries = JurnalUmum::where('coa_id', $oldCoa->id)->get();
        $this->info("Found {$jurnalUmumEntries->count()} jurnal umum entries to update");
        
        $updatedJurnalUmum = 0;
        foreach ($jurnalUmumEntries as $ju) {
            $ju->coa_id = $newCoa->id;
            $ju->save();
            $updatedJurnalUmum++;
            $this->info("✅ Updated Jurnal Umum ID: {$ju->id}");
        }
        
        $this->info("\n=== SUMMARY ===");
        $this->info("Updated Journal Lines: {$updatedLines}");
        $this->info("Updated Jurnal Umum: {$updatedJurnalUmum}");
        
        // Verification
        $this->info("\n=== VERIFICATION ===");
        $remainingOldLines = JournalLine::where('coa_id', $oldCoa->id)->count();
        $newLines = JournalLine::where('coa_id', $newCoa->id)->count();
        
        $this->info("Remaining COA 51 lines: {$remainingOldLines}");
        $this->info("Total COA 560 lines: {$newLines}");
        
        $this->info("\n=== UPDATE COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
