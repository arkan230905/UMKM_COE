<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produksi;
use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class CheckProductionJournals extends Command
{
    protected $signature = 'produksi:check-journals {produksi_id?}';
    protected $description = 'Check production journal entries and validate COA mappings';

    public function handle()
    {
        $produksiId = $this->argument('produksi_id');
        
        if ($produksiId) {
            $this->checkSingleProduction($produksiId);
        } else {
            $this->checkAllProductions();
        }
    }

    private function checkSingleProduction($produksiId)
    {
        $produksi = Produksi::with('produk')->find($produksiId);
        
        if (!$produksi) {
            $this->error("Produksi ID {$produksiId} not found");
            return;
        }

        $this->info("=== PRODUKSI #{$produksi->id} ===");
        $this->line("Produk: {$produksi->produk->nama_produk}");
        $this->line("Tanggal: {$produksi->tanggal->format('Y-m-d')}");
        $this->line("Status: {$produksi->status}");
        $this->line("Total BBB: Rp " . number_format($produksi->total_bahan, 0, ',', '.'));
        $this->line("Total BTKL: Rp " . number_format($produksi->total_btkl, 0, ',', '.'));
        $this->line("Total BOP: Rp " . number_format($produksi->total_bop, 0, ',', '.'));
        $this->line("Total Biaya: Rp " . number_format($produksi->total_biaya, 0, ',', '.'));
        $this->newLine();

        // Check if journals exist
        $journals = JurnalUmum::where('referensi', $produksi->id)
            ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
            ->with('coa')
            ->orderBy('tipe_referensi')
            ->orderBy('id')
            ->get();

        if ($journals->isEmpty()) {
            $this->error("❌ NO JOURNAL ENTRIES FOUND!");
            $this->line("Expected journal types: produksi_bbb, produksi_btkl, produksi_bop, produksi_transfer");
            return;
        }

        $this->info("✅ Found {$journals->count()} journal entries");
        $this->newLine();

        // Group by type
        $byType = $journals->groupBy('tipe_referensi');

        foreach ($byType as $type => $entries) {
            $this->line("--- {$type} ---");
            $totalDebit = 0;
            $totalKredit = 0;

            foreach ($entries as $entry) {
                $coaInfo = $entry->coa ? "{$entry->coa->kode_akun} - {$entry->coa->nama_akun}" : "COA NOT FOUND";
                $debit = $entry->debit > 0 ? "Rp " . number_format($entry->debit, 0, ',', '.') : "";
                $kredit = $entry->kredit > 0 ? "Rp " . number_format($entry->kredit, 0, ',', '.') : "";
                
                $this->line("  {$coaInfo}");
                $this->line("    D: {$debit}  K: {$kredit}");
                $this->line("    Ket: {$entry->keterangan}");
                
                $totalDebit += $entry->debit;
                $totalKredit += $entry->kredit;
            }

            $this->line("  TOTAL - D: Rp " . number_format($totalDebit, 0, ',', '.') . 
                       "  K: Rp " . number_format($totalKredit, 0, ',', '.'));
            
            if (abs($totalDebit - $totalKredit) > 0.01) {
                $this->error("  ⚠️  NOT BALANCED! Difference: Rp " . number_format(abs($totalDebit - $totalKredit), 2, ',', '.'));
            } else {
                $this->info("  ✅ BALANCED");
            }
            $this->newLine();
        }

        // Check required COAs exist
        $this->checkRequiredCOAs($produksi->user_id);
    }

    private function checkAllProductions()
    {
        $this->info("=== RECENT COMPLETED PRODUCTIONS ===");
        
        $productions = Produksi::where('status', 'selesai')
            ->with('produk')
            ->orderBy('waktu_selesai_produksi', 'desc')
            ->limit(10)
            ->get();

        if ($productions->isEmpty()) {
            $this->warn("No completed productions found");
            return;
        }

        foreach ($productions as $produksi) {
            $journalCount = JurnalUmum::where('referensi', $produksi->id)
                ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
                ->count();

            $status = $journalCount > 0 ? "✅ {$journalCount} entries" : "❌ NO JOURNALS";
            
            $this->line("ID {$produksi->id}: {$produksi->produk->nama_produk} - {$produksi->tanggal->format('Y-m-d')} - {$status}");
        }

        $this->newLine();
        $this->line("Use 'php artisan produksi:check-journals <id>' to see details");
    }

    private function checkRequiredCOAs($userId)
    {
        $this->newLine();
        $this->info("=== REQUIRED COAs CHECK ===");
        
        $requiredCOAs = [
            '1141' => 'Persediaan Bahan Baku',
            '1171' => 'Pers. Barang Dalam Proses - BBB',
            '1172' => 'Pers. Barang Dalam Proses - BTKL',
            '1173' => 'Pers. Barang Dalam Proses - BOP',
            '211' => 'Hutang Gaji',
            '1161' => 'Persediaan Barang Jadi',
        ];

        foreach ($requiredCOAs as $kode => $nama) {
            $coa = Coa::where('kode_akun', $kode)
                ->where('user_id', $userId)
                ->first();

            if ($coa) {
                $this->info("  ✅ {$kode} - {$coa->nama_akun}");
            } else {
                $this->error("  ❌ {$kode} - {$nama} NOT FOUND");
            }
        }
    }
}
