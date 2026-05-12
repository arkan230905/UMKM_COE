<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;
use App\Models\JournalLine;

class CheckHPPCoaType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:hpp-coa-type {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check HPP COA type and laba rugi classification';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== CHECKING HPP COA TYPE AND LABA RUGI CLASSIFICATION ===");
        
        // Check COA 560 (HPP)
        $hppCoa = Coa::where('kode_akun', '560')
            ->where('user_id', $userId)
            ->first();
            
        if ($hppCoa) {
            $this->info("✅ COA 560 Found:");
            $this->info("  Kode: {$hppCoa->kode_akun}");
            $this->info("  Nama: {$hppCoa->nama_akun}");
            $this->info("  Tipe Akun: '{$hppCoa->tipe_akun}'");
            $this->info("  Saldo Normal: '{$hppCoa->saldo_normal}'");
            $this->info("  User ID: {$hppCoa->user_id}");
        } else {
            $this->error("❌ COA 560 not found for user {$userId}");
            return Command::FAILURE;
        }
        
        // Check if HPP appears in journal lines
        $this->info("\n📊 HPP Journal Lines:");
        $hppJournalLines = JournalLine::whereHas('coa', function($query) use ($userId) {
            $query->where('kode_akun', '560')->where('user_id', $userId);
        })->with('entry', 'coa')
        ->get();
        
        $this->info("Total HPP journal lines: " . $hppJournalLines->count());
        
        foreach ($hppJournalLines as $line) {
            $this->info("  Entry ID: {$line->entry->id}, Date: {$line->entry->tanggal}");
            $this->info("  Debit: {$line->debit}, Credit: {$line->credit}");
        }
        
        // Simulate laba rugi logic
        $this->info("\n🔍 LABA RUGI LOGIC SIMULATION:");
        
        // Get all COAs for user
        $coas = Coa::where('user_id', $userId)->get();
        
        $pendapatanCount = 0;
        $bebanCount = 0;
        $hppInPendapatan = false;
        $hppInBeban = false;
        
        foreach ($coas as $coa) {
            if (in_array($coa->tipe_akun, ['Revenue', 'revenue', 'Pendapatan'])) {
                $pendapatanCount++;
                if ($coa->kode_akun == '560') {
                    $hppInPendapatan = true;
                }
            }
            
            if (in_array($coa->tipe_akun, ['Expense', 'expense', 'Beban', 'Biaya'])) {
                $bebanCount++;
                if ($coa->kode_akun == '560') {
                    $hppInBeban = true;
                }
            }
        }
        
        $this->info("Total Pendapatan COAs: {$pendapatanCount}");
        $this->info("Total Beban COAs: {$bebanCount}");
        $this->info("HPP in Pendapatan: " . ($hppInPendapatan ? '❌ YES' : '✅ NO'));
        $this->info("HPP in Beban: " . ($hppInBeban ? '✅ YES' : '❌ NO'));
        
        if ($hppInPendapatan) {
            $this->error("❌ PROBLEM: HPP classified as Pendapatan!");
            $this->info("Solution: Update COA 560 tipe_akun to 'Expense'");
        } else {
            $this->info("✅ HPP correctly classified as Beban");
        }
        
        $this->info("\n=== CHECK COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
