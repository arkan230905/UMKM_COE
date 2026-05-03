<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;
use App\Models\JournalLine;
use App\Models\JournalEntry;

class CheckPendapatanData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:pendapatan-data {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check pendapatan COA and journal data for laba rugi';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== CHECKING PENDAPATAN DATA FOR USER ID: {$userId} ===");
        
        // Get all COAs with Revenue/Pendapatan type
        $pendapatanCoas = Coa::where('user_id', $userId)
            ->where(function($query) {
                $query->where('tipe_akun', 'Revenue')
                      ->orWhere('tipe_akun', 'revenue')
                      ->orWhere('tipe_akun', 'Pendapatan');
            })
            ->get();
            
        $this->info("📊 PENDAPATAN COAs Found: " . $pendapatanCoas->count());
        
        foreach ($pendapatanCoas as $coa) {
            $this->info("  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})");
            $this->info("    Saldo Awal: " . ($coa->saldo_awal ?? 0));
        }
        
        // Check journal entries for pendapatan
        $this->info("\n📊 JOURNAL ENTRIES FOR PENDAPATAN:");
        
        $periode = now()->format('Y-m');
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);
        
        $from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
        
        $this->info("Periode: {$periode} ({$from} to {$to})");
        
        $pendapatanJournalLines = JournalLine::whereHas('entry', function($query) use ($userId, $from, $to) {
            $query->where('user_id', $userId)
                  ->whereBetween('tanggal', [$from, $to]);
        })->whereHas('coa', function($query) {
            $query->where('tipe_akun', 'Revenue')
                  ->orWhere('tipe_akun', 'revenue')
                  ->orWhere('tipe_akun', 'Pendapatan');
        })->with('entry', 'coa')
        ->get();
        
        $this->info("Pendapatan journal lines: " . $pendapatanJournalLines->count());
        
        foreach ($pendapatanJournalLines as $line) {
            $this->info("  Entry ID: {$line->entry->id}, Date: {$line->entry->tanggal}");
            $this->info("  COA: {$line->coa->kode_akun} - {$line->coa->nama_akun}");
            $this->info("  Debit: {$line->debit}, Credit: {$line->credit}");
        }
        
        // Check specifically for COA 41 (Penjualan)
        $this->info("\n🔍 COA 41 (Penjualan) CHECK:");
        $coa41 = Coa::where('kode_akun', '41')->where('user_id', $userId)->first();
        
        if ($coa41) {
            $this->info("✅ COA 41 Found:");
            $this->info("  Nama: {$coa41->nama_akun}");
            $this->info("  Tipe Akun: '{$coa41->tipe_akun}'");
            $this->info("  Saldo Normal: '{$coa41->saldo_normal}'");
            
            // Check journal lines for COA 41
            $coa41Lines = JournalLine::whereHas('entry', function($query) use ($userId, $from, $to) {
                $query->where('user_id', $userId)
                      ->whereBetween('tanggal', [$from, $to]);
            })->where('coa_id', $coa41->id)
            ->with('entry')
            ->get();
            
            $this->info("  Journal Lines: " . $coa41Lines->count());
            foreach ($coa41Lines as $line) {
                $this->info("    Entry ID: {$line->entry->id}, Debit: {$line->debit}, Credit: {$line->credit}");
            }
            
            // Calculate balance for COA 41
            $saldoAwal = (float)($coa41->saldo_awal ?? 0);
            $totalDebit = $coa41Lines->sum('debit');
            $totalKredit = $coa41Lines->sum('credit');
            
            // COA 41 should have credit normal balance
            $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
            
            $this->info("  Saldo Awal: Rp " . number_format($saldoAwal, 2));
            $this->info("  Total Debit: Rp " . number_format($totalDebit, 2));
            $this->info("  Total Credit: Rp " . number_format($totalKredit, 2));
            $this->info("  Saldo Akhir: Rp " . number_format($saldoAkhir, 2));
            
            if ($saldoAkhir == 0) {
                $this->info("  ❌ Saldo akhir = 0, tidak akan muncul di laba rugi");
            } else {
                $this->info("  ✅ Saldo akhir != 0, seharusnya muncul di laba rugi");
            }
            
        } else {
            $this->error("❌ COA 41 (Penjualan) not found for user {$userId}");
        }
        
        $this->info("\n=== CHECK COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
