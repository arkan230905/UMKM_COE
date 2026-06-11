<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produksi;
use App\Models\JurnalUmum;
use Illuminate\Support\Facades\DB;

class FixBopJournals extends Command
{
    protected $signature = 'bop:fix-journals {produksi_id?}';
    protected $description = 'Re-create BOP journal entries for produksi';

    public function handle()
    {
        $produksiId = $this->argument('produksi_id');
        
        if ($produksiId) {
            $produksis = Produksi::where('id', $produksiId)->get();
        } else {
            // Fix all produksi with BOP but incomplete journals
            $this->info("Finding produksi with incomplete BOP journals...");
            $produksis = Produksi::where('total_bop', '>', 0)->get()->filter(function($prod) {
                $totalJournal = JurnalUmum::where('tipe_referensi', 'produksi_bop')
                    ->where('referensi', (string)$prod->id)
                    ->where('kredit', '>', 0)
                    ->sum('kredit');
                
                return abs($totalJournal - $prod->total_bop) > 1; // More than 1 rupiah difference
            });
        }
        
        if ($produksis->count() == 0) {
            $this->info('No produksi found that needs fixing!');
            return 0;
        }
        
        $this->info("Found {$produksis->count()} produksi records to fix.\n");
        
        foreach ($produksis as $prod) {
            $this->info("========================================");
            $this->info("Processing Produksi ID: {$prod->id}");
            $this->info("Produk: {$prod->produk->nama_produk}");
            $this->info("Total BOP: Rp " . number_format($prod->total_bop, 0));
            
            try {
                DB::beginTransaction();
                
                // Delete old BOP journal entries
                $deleted = JurnalUmum::where('tipe_referensi', 'produksi_bop')
                    ->where('referensi', (string)$prod->id)
                    ->delete();
                
                $this->info("Deleted {$deleted} old BOP journal entries");
                
                // Re-create journals using the controller method
                $controller = new \App\Http\Controllers\ProduksiController();
                
                // Get HPP data
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('getHppBreakdownForProduction');
                $method->setAccessible(true);
                $hppData = $method->invoke($controller, $prod->produk_id, $prod->user_id);
                
                // Get tanggal
                $tanggal = $prod->tanggal instanceof \Carbon\Carbon ? 
                    $prod->tanggal : 
                    \Carbon\Carbon::parse($prod->tanggal);
                
                // Re-create BOP journals only
                $this->createBopJournals($prod, $hppData, $prod->qty_produksi, $tanggal, $controller);
                
                DB::commit();
                
                $this->info("✅ BOP journals re-created successfully!");
                
                // Verify
                $newJournals = JurnalUmum::where('tipe_referensi', 'produksi_bop')
                    ->where('referensi', (string)$prod->id)
                    ->where('kredit', '>', 0)
                    ->with('coa')
                    ->get();
                
                $totalKredit = 0;
                foreach ($newJournals as $j) {
                    $totalKredit += $j->kredit;
                    $this->line(sprintf(
                        "  %-10s %-35s  Kredit: %12s",
                        $j->coa->kode_akun ?? 'N/A',
                        substr($j->keterangan, 0, 35),
                        number_format($j->kredit, 0)
                    ));
                }
                
                $this->info("Total Kredit: Rp " . number_format($totalKredit, 0));
                $this->info("Expected BOP: Rp " . number_format($prod->total_bop, 0));
                
                if (abs($totalKredit - $prod->total_bop) <= 1) {
                    $this->info("✅ BALANCED!");
                } else {
                    $this->error("❌ STILL NOT BALANCED! Diff: Rp " . number_format(abs($totalKredit - $prod->total_bop), 0));
                }
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error: " . $e->getMessage());
                $this->error($e->getTraceAsString());
            }
            
            $this->newLine();
        }
        
        return 0;
    }
    
    private function createBopJournals($produksi, $hppData, $qtyProd, $tanggal, $controller)
    {
        $user_id = $produksi->user_id;
        $totalBOP = $produksi->total_bop;
        
        if ($totalBOP <= 0) {
            return;
        }
        
        // Get getCoaIdByKode method
        $reflection = new \ReflectionClass($controller);
        $getCoaMethod = $reflection->getMethod('getCoaIdByKode');
        $getCoaMethod->setAccessible(true);
        
        $getCoaUserMethod = $reflection->getMethod('getCoaIdByKodeForUser');
        $getCoaUserMethod->setAccessible(true);
        
        // DEBIT: Barang Dalam Proses BOP (1173)
        \App\Models\JurnalUmum::create([
            'user_id' => $user_id,
            'coa_id' => $getCoaMethod->invoke($controller, '1173'),
            'tanggal' => $tanggal,
            'keterangan' => 'Alokasi BOP untuk Produksi ' . $produksi->produk->nama_produk,
            'debit' => $totalBOP,
            'kredit' => 0,
            'referensi' => (string) $produksi->id,
            'tipe_referensi' => 'produksi_bop',
            'created_by' => $user_id,
        ]);
        
        // KREDIT: Per komponen BOP
        $totalKreditBOP = 0;
        $skippedComponents = [];
        
        foreach ($hppData['bop_komponen'] as $komponen) {
            $totalKomponen = $komponen['subtotal'] * $qtyProd;
            if ($totalKomponen > 0) {
                $coaKode = $komponen['coa_kode'] ?? null;
                $coaId = null;
                
                if ($coaKode) {
                    $coaId = $getCoaUserMethod->invoke($controller, $coaKode, $user_id);
                }
                
                // Fallback
                if (!$coaId) {
                    $coaId = $getCoaUserMethod->invoke($controller, '530', $user_id)
                           ?? $getCoaUserMethod->invoke($controller, '531', $user_id)
                           ?? $getCoaUserMethod->invoke($controller, '532', $user_id)
                           ?? $getCoaMethod->invoke($controller, '530');
                }
                
                if (!$coaId) {
                    $skippedComponents[] = [
                        'nama' => $komponen['nama_komponen'],
                        'coa_kode' => $coaKode,
                        'subtotal' => $totalKomponen
                    ];
                    $this->warn("  ⚠️  Skipped: {$komponen['nama_komponen']} (COA {$coaKode} not found)");
                    continue;
                }
                
                \App\Models\JurnalUmum::create([
                    'user_id' => $user_id,
                    'coa_id' => $coaId,
                    'tanggal' => $tanggal,
                    'keterangan' => 'BOP - ' . $komponen['nama_komponen'],
                    'debit' => 0,
                    'kredit' => $totalKomponen,
                    'referensi' => (string) $produksi->id,
                    'tipe_referensi' => 'produksi_bop',
                    'created_by' => $user_id,
                ]);
                
                $totalKreditBOP += $totalKomponen;
            }
        }
        
        if (count($skippedComponents) > 0) {
            $this->warn("\n⚠️  {count($skippedComponents)} components were skipped due to missing COA!");
            $this->warn("Please create the required COA accounts to fix this.");
        }
    }
}
