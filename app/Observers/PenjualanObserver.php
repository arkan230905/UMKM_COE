<?php

namespace App\Observers;

use App\Models\Penjualan;
use App\Models\JurnalUmum;
use App\Models\Coa;
use App\Services\AutoCoaService;

class PenjualanObserver
{
    /**
     * Handle the Penjualan "created" event.
     */
    public function created(Penjualan $penjualan)
    {
        $this->createPenjualanJournal($penjualan);
    }
    
    /**
     * Handle the Penjualan "updated" event.
     */
    public function updated(Penjualan $penjualan)
    {
        // Delete old journals and create new ones
        $this->deletePenjualanJournals($penjualan->id);
        $this->createPenjualanJournal($penjualan);
    }
    
    /**
     * Create journal entry for penjualan with HPP
     */
    private function createPenjualanJournal(Penjualan $penjualan)
    {
        if ($penjualan->total_harga <= 0) {
            \Log::warning('Skipping journal creation for penjualan with zero or negative total', [
                'penjualan_id' => $penjualan->id,
                'total_harga' => $penjualan->total_harga
            ]);
            return; // Skip if total is 0 or negative
        }
        
        \Log::info('Creating journal for penjualan', [
            'penjualan_id' => $penjualan->id,
            'nomor_penjualan' => $penjualan->nomor_penjualan,
            'total_harga' => $penjualan->total_harga,
            'tanggal' => $penjualan->tanggal
        ]);
        
        // Hitung HPP (Harga Pokok Produksi)
        $hpp = $this->calculateHPP($penjualan);
        
        \Log::info('Calculated HPP', [
            'hpp' => $hpp,
            'penjualan_id' => $penjualan->id
        ]);
        
        try {
            // Prepare journal entries
            $journalData = [];
            
            // 1. Debit Kas (untuk penjualan tunai) - find COA for payment method
            $kasCoa = $this->getKasCoa($penjualan->payment_method, $penjualan->sumber_dana);
            if ($kasCoa) {
                $journalData[] = [
                    'coa_id' => $kasCoa->id,
                    'tanggal' => $penjualan->tanggal,
                    'keterangan' => 'Penjualan Produk - ' . $penjualan->nomor_penjualan,
                    'debit' => $penjualan->total_harga,
                    'kredit' => 0,
                    'referensi' => $penjualan->nomor_penjualan,
                    'tipe_referensi' => 'penjualan',
                    'created_by' => 1,
                ];
            }
            
            // 2. Credit Penjualan - find COA for penjualan
            $penjualanCoa = AutoCoaService::getOrCreateCoa('Penjualan Produk', 'Penjualan', 'Revenue', 'Kredit');
            if ($penjualanCoa) {
                $journalData[] = [
                    'coa_id' => $penjualanCoa->id,
                    'tanggal' => $penjualan->tanggal,
                    'keterangan' => 'Penjualan Produk - ' . $penjualan->nomor_penjualan,
                    'debit' => 0,
                    'kredit' => $penjualan->total_harga,
                    'referensi' => $penjualan->nomor_penjualan,
                    'tipe_referensi' => 'penjualan',
                    'created_by' => 1,
                ];
            }
            
            // 3. Debit HPP (Harga Pokok Produksi)
            if ($hpp > 0) {
                $hppCoa = AutoCoaService::getOrCreateCoa('HPP', 'HPP', 'Expense', 'Debit');
                
                // Get the correct persediaan COA based on product
                $persediaanCoa = null;
                if ($penjualan->details->count() > 0) {
                    $firstDetail = $penjualan->details->first();
                    if ($firstDetail->produk_id) {
                        $produk = \App\Models\Produk::find($firstDetail->produk_id);
                        if ($produk) {
                            $persediaanCoa = AutoCoaService::getOrCreatePersediaanBarangJadiCoa(
                                $produk->nama_produk,
                                $produk->id
                            );
                        }
                    }
                }
                
                // Fallback to default persediaan if no product found
                if (!$persediaanCoa) {
                    $persediaanCoa = AutoCoaService::getOrCreateCoa('Persediaan Barang Jadi', 'Persediaan Barang Jadi', 'Asset', 'Debit');
                }
                
                if ($hppCoa) {
                    $journalData[] = [
                        'coa_id' => $hppCoa->id,
                        'tanggal' => $penjualan->tanggal,
                        'keterangan' => 'HPP Penjualan - ' . $penjualan->nomor_penjualan,
                        'debit' => $hpp,
                        'kredit' => 0,
                        'referensi' => $penjualan->nomor_penjualan,
                        'tipe_referensi' => 'penjualan',
                        'created_by' => 1,
                    ];
                }
                
                if ($persediaanCoa) {
                    $journalData[] = [
                        'coa_id' => $persediaanCoa->id,
                        'tanggal' => $penjualan->tanggal,
                        'keterangan' => 'Persediaan Barang Jadi - ' . $penjualan->nomor_penjualan,
                        'debit' => 0,
                        'kredit' => $hpp,
                        'referensi' => $penjualan->nomor_penjualan,
                        'tipe_referensi' => 'penjualan',
                        'created_by' => 1,
                    ];
                }
            }
            
            // Insert all journal entries
            if (!empty($journalData)) {
                JurnalUmum::insert($journalData);
                \Log::info('Journal entries created successfully', [
                    'penjualan_id' => $penjualan->id,
                    'entries_count' => count($journalData)
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to create journal entries for penjualan: ' . $e->getMessage(), [
                'penjualan_id' => $penjualan->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Calculate HPP (Harga Pokok Produksi) for penjualan
     */
    private function calculateHPP(Penjualan $penjualan)
    {
        // Use the new method that gets actual HPP from production
        return $this->getSaleHPP($penjualan);
    }
    
    /**
     * Calculate actual HPP per unit based on production costs
     */
    private function calculateActualHPP($produkId, $quantity)
    {
        // Ambil biaya produksi dari tabel produksi untuk produk ini
        $productionCosts = \App\Models\Produksi::where('produk_id', $produkId)
            ->where('status', 'completed')
            ->orderBy('id', 'desc')
            ->take(5) // Ambil 5 produksi terakhir
            ->get();
        
        if ($productionCosts->isEmpty()) {
            // Jika tidak ada data produksi, gunakan harga_bom sebagai fallback
            $produk = \App\Models\Produk::find($produkId);
            return $produk->harga_bom ?? 0;
        }
        
        $totalCost = 0;
        $totalQuantity = 0;
        
        foreach ($productionCosts as $production) {
            // Ambil detail produksi
            $productionDetails = \App\Models\ProduksiDetail::where('produksi_id', $production->id)->get();
            
            foreach($productionDetails as $detail) {
                if ($detail->tipe === 'material') {
                    // Bahan baku
                    $totalCost += $detail->jumlah * $detail->harga_satuan;
                } elseif ($detail->tipe === 'labor') {
                    // Tenaga kerja langsung
                    $totalCost += $detail->jumlah * $detail->harga_satuan;
                } elseif ($detail->tipe === 'overhead') {
                    // Biaya overhead
                    $totalCost += $detail->jumlah * $detail->harga_satuan;
                }
                $totalQuantity += $detail->jumlah;
            }
        }
        
        // Hitung HPP per unit
        $hppPerUnit = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
        
        return $hppPerUnit;
    }
    
    /**
     * Get HPP for specific sale based on FIFO method
     * This method tracks which production batch is used for which sale
     */
    private function getSaleHPP($penjualan)
    {
        $hpp = 0;
        
        foreach ($penjualan->details as $detail) {
            // Use the HPP method that considers sale date
            $hppPerUnit = $detail->produk->getHPPForSaleDate($penjualan->tanggal);
            $hpp += $hppPerUnit * $detail->jumlah;
        }
        
        return $hpp;
    }
    
    /**
     * Handle the Penjualan "deleted" event.
     *
     * @param  \App\Models\Penjualan  $penjualan
     * @return void
     */
    public function deleted(Penjualan $penjualan)
    {
        // Delete associated journal entries when penjualan is deleted
        $this->deletePenjualanJournals($penjualan->id);
    }
    
    /**
     * Get COA for Kas based on payment method
     */
    private function getKasCoa($paymentMethod, $sumberDana)
    {
        if ($paymentMethod === 'cash' || $paymentMethod === 'transfer') {
            // Use sumber_dana to find the specific kas account
            return Coa::where('kode_akun', $sumberDana)->first();
        }
        
        // Default to Kas (112) for other methods
        return Coa::where('kode_akun', '112')->first();
    }

    /**
     * Delete journal entries for a specific penjualan
     */
    private function deletePenjualanJournals($penjualanId)
    {
        // Get penjualan to get the nomor_penjualan
        $penjualan = Penjualan::find($penjualanId);
        if (!$penjualan) {
            return;
        }
        
        // Delete journals by referensi
        JurnalUmum::where('tipe_referensi', 'penjualan')
                  ->where('referensi', $penjualan->nomor_penjualan)
                  ->delete();
    }
}
