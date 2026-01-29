<?php

namespace App\Observers;

use App\Models\Penjualan;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\JournalService;

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
        
        $journalService = new \App\Services\JournalService();
        $entries = [];
        
        // Hitung HPP (Harga Pokok Produksi)
        $hpp = $this->calculateHPP($penjualan);
        
        \Log::info('Calculated HPP', [
            'hpp' => $hpp,
            'penjualan_id' => $penjualan->id
        ]);
        
        // 1. Debit Kas (untuk penjualan tunai)
        $entries[] = ['code' => '1101', 'debit' => $penjualan->total_harga, 'credit' => 0];
        
        // 2. Credit Penjualan
        $entries[] = ['code' => '4101', 'debit' => 0, 'credit' => $penjualan->total_harga];
        
        // 3. Debit HPP (Harga Pokok Produksi)
        if ($hpp > 0) {
            $entries[] = ['code' => '5101', 'debit' => $hpp, 'credit' => 0]; // HPP
            $entries[] = ['code' => '1107', 'debit' => 0, 'credit' => $hpp]; // Persediaan Barang Jadi
            
            \Log::info('Adding HPP entries', [
                'hpp' => $hpp,
                'entries_added' => 2
            ]);
        }
        
        \Log::info('Creating journal entry with entries', [
            'entries_count' => count($entries),
            'entries' => $entries
        ]);
        
        // Create journal entry
        $journal = $journalService->post(
            $penjualan->tanggal->format('Y-m-d'),
            'sale',
            $penjualan->id,
            'Penjualan Produk - ' . $penjualan->nomor_penjualan,
            $entries
        );
        
        \Log::info('Journal entry created', [
            'journal_id' => $journal->id
        ]);
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
     * Delete journal entries for a specific penjualan
     */
    private function deletePenjualanJournals($penjualanId)
    {
        $journals = JournalEntry::where('ref_type', 'sale')
                               ->where('ref_id', $penjualanId)
                               ->get();
        
        foreach ($journals as $journal) {
            // Delete journal lines first
            JournalLine::where('journal_entry_id', $journal->id)->delete();
            
            // Delete journal entry
            $journal->delete();
        }
    }
}
