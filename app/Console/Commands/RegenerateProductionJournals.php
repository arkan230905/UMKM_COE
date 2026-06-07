<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produksi;
use App\Models\JurnalUmum;
use App\Models\HargaPokokProduksiBop;
use App\Models\Coa;

class RegenerateProductionJournals extends Command
{
    protected $signature = 'production:regenerate-journals {--production_id=}';
    protected $description = 'Regenerate production journals with detailed BOP components';

    public function handle()
    {
        $this->info('========================================');
        $this->info('Regenerate Production Journals');
        $this->info('========================================');
        
        $productionId = $this->option('production_id');
        
        if ($productionId) {
            $produksis = Produksi::where('id', $productionId)->get();
            if ($produksis->isEmpty()) {
                $this->error("Production ID {$productionId} not found!");
                return 1;
            }
        } else {
            $produksis = Produksi::where('status', '!=', 'draft')->get();
        }
        
        $this->info("Found {$produksis->count()} production records to process\n");
        
        $regenerated = 0;
        $skipped = 0;
        
        foreach ($produksis as $produksi) {
            $this->info("Processing: Produksi #{$produksi->id} - {$produksi->produk->nama_produk}");
            
            // Check if BOP journals already exist with detailed components
            $bopJournals = JurnalUmum::where('referensi', $produksi->id)
                ->where('tipe_referensi', 'produksi_bop')
                ->where('keterangan', 'like', 'BOP - %')
                ->count();
            
            if ($bopJournals > 1) {
                $this->warn("  ⏭️  Already has {$bopJournals} BOP component journals, skipping");
                $skipped++;
                continue;
            }
            
            // Delete old BOP journals (both debit WIP and credit generic)
            $deleted = JurnalUmum::where('referensi', $produksi->id)
                ->where('tipe_referensi', 'produksi_bop')
                ->delete();
            
            $this->info("  🗑️  Deleted {$deleted} old BOP journals");
            
            // Get BOP breakdown with components
            $user_id = $produksi->user_id;
            $qtyProd = $produksi->qty_produksi;
            
            $hppBop = HargaPokokProduksiBop::where('user_id', $user_id)
                ->with('bopProses')
                ->get();
            
            $totalBOP = 0;
            $komponenList = [];
            
            foreach ($hppBop as $bop) {
                if ($bop->bopProses) {
                    $namaProses = $bop->bopProses->nama_bop_proses ?? 'BOP';
                    
                    // Process Bahan Pendukung
                    $komponenBahanPendukung = $bop->bopProses->komponen_bahan_pendukung;
                    if (is_string($komponenBahanPendukung)) {
                        $komponenBahanPendukung = json_decode($komponenBahanPendukung, true) ?? [];
                    } elseif (!is_array($komponenBahanPendukung)) {
                        $komponenBahanPendukung = [];
                    }
                    
                    foreach ($komponenBahanPendukung as $komponen) {
                        $subtotal = ($komponen['total'] ?? 0) * $qtyProd;
                        $totalBOP += $subtotal;
                        $komponenList[] = [
                            'nama_komponen' => $komponen['nama'] ?? 'Bahan Pendukung',
                            'subtotal' => $subtotal,
                            'coa_kode' => $komponen['coa_kredit'] ?? '530',
                        ];
                    }
                    
                    // Process Komponen Lainnya
                    $komponenLainnya = $bop->bopProses->komponen_lainnya;
                    if (is_string($komponenLainnya)) {
                        $komponenLainnya = json_decode($komponenLainnya, true) ?? [];
                    } elseif (!is_array($komponenLainnya)) {
                        $komponenLainnya = [];
                    }
                    
                    foreach ($komponenLainnya as $komponen) {
                        $subtotal = ($komponen['nilai_per_produk'] ?? 0) * $qtyProd;
                        $totalBOP += $subtotal;
                        $komponenList[] = [
                            'nama_komponen' => $komponen['nama_komponen'] ?? 'Overhead',
                            'subtotal' => $subtotal,
                            'coa_kode' => $komponen['coa_kredit'] ?? '550',
                        ];
                    }
                }
            }
            
            if ($totalBOP > 0 && count($komponenList) > 0) {
                // DEBIT: WIP BOP (1173)
                $coaWipBop = Coa::where('kode_akun', '1173')
                    ->where('user_id', $user_id)
                    ->first();
                
                if (!$coaWipBop) {
                    $this->error("  ❌ COA 1173 (WIP BOP) not found for user {$user_id}");
                    continue;
                }
                
                JurnalUmum::create([
                    'user_id' => $user_id,
                    'coa_id' => $coaWipBop->id,
                    'tanggal' => $produksi->tanggal,
                    'keterangan' => 'Alokasi BOP untuk Produksi ' . $produksi->produk->nama_produk,
                    'debit' => $totalBOP,
                    'kredit' => 0,
                    'referensi' => $produksi->id,
                    'tipe_referensi' => 'produksi_bop',
                    'created_by' => $user_id,
                ]);
                
                // KREDIT: Per komponen
                $created = 0;
                foreach ($komponenList as $komponen) {
                    if ($komponen['subtotal'] <= 0) continue;
                    
                    $coaKomponen = Coa::where('kode_akun', $komponen['coa_kode'])
                        ->where('user_id', $user_id)
                        ->first();
                    
                    if (!$coaKomponen) {
                        $this->warn("  ⚠️  COA {$komponen['coa_kode']} not found, skipping {$komponen['nama_komponen']}");
                        continue;
                    }
                    
                    JurnalUmum::create([
                        'user_id' => $user_id,
                        'coa_id' => $coaKomponen->id,
                        'tanggal' => $produksi->tanggal,
                        'keterangan' => 'BOP - ' . $komponen['nama_komponen'],
                        'debit' => 0,
                        'kredit' => $komponen['subtotal'],
                        'referensi' => $produksi->id,
                        'tipe_referensi' => 'produksi_bop',
                        'created_by' => $user_id,
                    ]);
                    $created++;
                }
                
                $this->info("  ✅ Created 1 debit + {$created} credit journal entries");
                $regenerated++;
            } else {
                $this->warn("  ⚠️  No BOP components found");
                $skipped++;
            }
        }
        
        $this->info("\n========================================");
        $this->info("✅ Regeneration complete!");
        $this->info("Regenerated: {$regenerated}");
        $this->info("Skipped: {$skipped}");
        $this->info("========================================");
        
        return 0;
    }
}
