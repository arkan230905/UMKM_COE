<?php

namespace App\Observers;

use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Models\JurnalUmum;
use App\Services\AutoCoaService;
use Illuminate\Support\Facades\Log;

class ProduksiObserver
{
    /**
     * Handle the Produksi "completed" event.
     * This will create journal entries when production is completed
     */
    public function updated(Produksi $produksi)
    {
        // Check if status changed to 'selesai' or 'completed'
        if ($produksi->wasChanged('status') && 
            in_array($produksi->status, ['selesai', 'completed'])) {
            
            $this->createProduksiJournals($produksi);
        }
    }
    
    /**
     * Create journal entries for completed production
     */
    private function createProduksiJournals(Produksi $produksi)
    {
        try {
            Log::info("Creating journals for completed production: {$produksi->id}");
            
            // Get total production cost
            $totalCost = $produksi->total_biaya ?? 0;
            
            if ($totalCost <= 0) {
                Log::warning("Production cost is 0 or negative, skipping journal creation");
                return;
            }
            
            // Get product information
            $produk = null;
            $productName = 'Produk Tidak Dikenal';
            
            // Try to get product from production details
            $details = ProduksiDetail::where('produksi_id', $produksi->id)
                ->where('tipe', 'finished_goods')
                ->first();
            
            if ($details && $details->produk_id) {
                $produk = \App\Models\Produk::find($details->produk_id);
                if ($produk) {
                    $productName = $produk->nama_produk;
                }
            }
            
            // Get or create COA for persediaan barang jadi
            $coaPersediaan = AutoCoaService::getOrCreatePersediaanBarangJadiCoa(
                $productName, 
                $produk ? $produk->id : null
            );
            
            // Get COA for persediaan dalam proses
            $coaDalamProses = \App\Models\Coa::where('kode_akun', '117')->first();
            if (!$coaDalamProses) {
                // Create if not exists
                $coaDalamProses = AutoCoaService::getOrCreateCoa(
                    'Pers. Barang dalam Proses',
                    'Persediaan Barang dalam Proses',
                    'Asset',
                    'Debit'
                );
            }
            
            // Delete existing journals for this production (if any)
            $this->deleteExistingJournals($produksi);
            
            // Create journal entries
            $journalData = [
                // Debit: Persediaan Barang Jadi
                [
                    'coa_id' => $coaPersediaan->id,
                    'tanggal' => $produksi->tanggal,
                    'keterangan' => "Transfer WIP ke Barang Jadi - {$productName}",
                    'debit' => $totalCost,
                    'kredit' => 0,
                    'referensi' => "PROD-" . $produksi->tanggal->format('Ymd') . "-" . str_pad($produksi->id, 3, '0', STR_PAD_LEFT),
                    'tipe_referensi' => 'produksi',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                // Credit: Persediaan Barang dalam Proses
                [
                    'coa_id' => $coaDalamProses->id,
                    'tanggal' => $produksi->tanggal,
                    'keterangan' => "Transfer WIP ke Barang Jadi - {$productName}",
                    'debit' => 0,
                    'kredit' => $totalCost,
                    'referensi' => "PROD-" . $produksi->tanggal->format('Ymd') . "-" . str_pad($produksi->id, 3, '0', STR_PAD_LEFT),
                    'tipe_referensi' => 'produksi',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ];
            
            // Insert journals
            JurnalUmum::insert($journalData);
            
            Log::info("Successfully created journals for production {$produksi->id}", [
                'product' => $productName,
                'total_cost' => $totalCost,
                'coa_persediaan' => $coaPersediaan->kode_akun,
                'coa_dalam_proses' => $coaDalamProses->kode_akun
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to create journals for production {$produksi->id}: " . $e->getMessage());
        }
    }
    
    /**
     * Delete existing journals for this production
     */
    private function deleteExistingJournals(Produksi $produksi)
    {
        $referensi = "PROD-" . $produksi->tanggal->format('Ymd') . "-" . str_pad($produksi->id, 3, '0', STR_PAD_LEFT);
        
        JurnalUmum::where('tipe_referensi', 'produksi')
            ->where('referensi', $referensi)
            ->delete();
        
        Log::info("Deleted existing journals for production: {$referensi}");
    }
    
    /**
     * Handle the Produksi "deleted" event.
     */
    public function deleted(Produksi $produksi)
    {
        // Delete associated journal entries when production is deleted
        $referensi = "PROD-" . $produksi->tanggal->format('Ymd') . "-" . str_pad($produksi->id, 3, '0', STR_PAD_LEFT);
        
        JurnalUmum::where('tipe_referensi', 'produksi')
            ->where('referensi', $referensi)
            ->delete();
        
        Log::info("Deleted journals for deleted production: {$referensi}");
    }
}
