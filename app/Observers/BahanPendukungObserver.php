<?php

namespace App\Observers;

use App\Models\BahanPendukung;
use App\Models\BomJobBahanPendukung;
use App\Models\BomJobCosting;
use App\Models\BomDetail;
use App\Models\Produk;
use App\Support\UnitConverter;
use Illuminate\Support\Facades\Log;

/**
 * Observer untuk auto-update biaya bahan dan BOM saat harga bahan pendukung berubah
 */
class BahanPendukungObserver
{
    /**
     * Handle the BahanPendukung "updated" event.
     */
    public function updated(BahanPendukung $bahanPendukung)
    {
        // Cek apakah harga berubah
        if ($bahanPendukung->isDirty('harga_satuan')) {
            $hargaLama = $bahanPendukung->getOriginal('harga_satuan');
            $hargaBaru = $bahanPendukung->harga_satuan;
            
            Log::info('🔄 Harga Bahan Pendukung Berubah - Auto Update Triggered', [
                'bahan_pendukung_id' => $bahanPendukung->id,
                'nama_bahan' => $bahanPendukung->nama_bahan,
                'harga_lama' => $hargaLama,
                'harga_baru' => $hargaBaru
            ]);
            
            // Update semua BOM yang menggunakan bahan pendukung ini
            $this->updateBomJobBahanPendukung($bahanPendukung);
        }
    }
    
    /**
     * Update semua BomJobBahanPendukung yang menggunakan bahan pendukung ini
     */
    private function updateBomJobBahanPendukung(BahanPendukung $bahanPendukung)
    {
        $converter = new UnitConverter();
        $updatedProducts = [];
        
        // Cari semua BomJobBahanPendukung yang menggunakan bahan pendukung ini
        $bomJobBahanPendukungs = BomJobBahanPendukung::where('bahan_pendukung_id', $bahanPendukung->id)
            ->with(['bomJobCosting.produk'])
            ->get();
        
        foreach ($bomJobBahanPendukungs as $jobPendukung) {
            // Get base unit from bahan pendukung
            $satuanBase = is_object($bahanPendukung->satuan) 
                ? $bahanPendukung->satuan->nama 
                : ($bahanPendukung->satuan ?? 'unit');
            
            // Get recipe unit from BOM
            $satuanResep = $jobPendukung->satuan ?: $satuanBase;
            
            // Convert harga_satuan (per base unit) to harga per recipe unit
            try {
                if (strtolower($satuanBase) === 'liter' && strtolower($satuanResep) === 'mililiter') {
                    // Special case: convert from Rp/liter to Rp/ml
                    $hargaPerSatuanResep = $bahanPendukung->harga_satuan / 1000;
                } elseif (strtolower($satuanBase) === 'kilogram' && strtolower($satuanResep) === 'gram') {
                    // Special case: convert from Rp/kg to Rp/gram
                    $hargaPerSatuanResep = $bahanPendukung->harga_satuan / 1000;
                } else {
                    // Use converter for other cases
                    $conversionFactor = $converter->convert(1, $satuanBase, $satuanResep);
                    $hargaPerSatuanResep = $bahanPendukung->harga_satuan / $conversionFactor;
                }
                
                // Update harga satuan with converted price
                $jobPendukung->harga_satuan = $hargaPerSatuanResep;
                
                // Recalculate subtotal
                $jobPendukung->subtotal = $jobPendukung->jumlah * $hargaPerSatuanResep;
                $jobPendukung->save();
                
                Log::info('✅ BomJobBahanPendukung Updated', [
                    'job_pendukung_id' => $jobPendukung->id,
                    'produk' => $jobPendukung->bomJobCosting->produk->nama_produk ?? 'N/A',
                    'jumlah' => $jobPendukung->jumlah,
                    'satuan_resep' => $satuanResep,
                    'satuan_base' => $satuanBase,
                    'harga_base' => $bahanPendukung->harga_satuan,
                    'harga_resep' => $hargaPerSatuanResep,
                    'subtotal' => $jobPendukung->subtotal
                ]);
                
                // Tandai produk untuk recalculate
                if ($jobPendukung->bomJobCosting && $jobPendukung->bomJobCosting->produk) {
                    $updatedProducts[$jobPendukung->bomJobCosting->produk->id] = $jobPendukung->bomJobCosting->produk;
                }
                
            } catch (\Exception $e) {
                Log::error('❌ Error updating BomJobBahanPendukung', [
                    'job_pendukung_id' => $jobPendukung->id,
                    'error' => $e->getMessage(),
                    'satuan_base' => $satuanBase,
                    'satuan_resep' => $satuanResep
                ]);
            }
        }
        
        // Recalculate biaya bahan untuk setiap produk yang terpengaruh
        foreach ($updatedProducts as $produk) {
            $this->recalculateProductBiayaBahan($produk);
        }
        
        Log::info('🎯 Auto Update Complete', [
            'bahan_pendukung' => $bahanPendukung->nama_bahan,
            'affected_products' => count($updatedProducts),
            'product_names' => array_map(fn($p) => $p->nama_produk, $updatedProducts)
        ]);
    }
    
    /**
     * Recalculate total biaya bahan untuk produk
     */
    private function recalculateProductBiayaBahan(Produk $produk)
    {
        $converter = new UnitConverter();
        $totalBiayaBahan = 0;
        
        // 1. Hitung biaya dari Bahan Baku (BomDetail)
        $bomDetails = BomDetail::with('bahanBaku.satuan')
            ->where('bom_id', function($query) use ($produk) {
                $query->select('id')->from('boms')->where('produk_id', $produk->id);
            })
            ->get();
        
        foreach ($bomDetails as $detail) {
            if (!$detail->bahanBaku) continue;
            
            $satuanBase = is_object($detail->bahanBaku->satuan) 
                ? $detail->bahanBaku->satuan->nama 
                : ($detail->bahanBaku->satuan ?? 'unit');
            
            try {
                $qtyBase = $converter->convert(
                    (float) $detail->jumlah,
                    $detail->satuan ?: $satuanBase,
                    $satuanBase
                );
                
                $totalBiayaBahan += $detail->bahanBaku->harga_satuan * $qtyBase;
            } catch (\Exception $e) {
                Log::error('Error calculating bahan baku cost', [
                    'produk_id' => $produk->id,
                    'detail_id' => $detail->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // 2. Hitung biaya dari Bahan Pendukung (BomJobBahanPendukung)
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            $bomJobBahanPendukung = BomJobBahanPendukung::with('bahanPendukung.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->get();
            
            foreach ($bomJobBahanPendukung as $jobPendukung) {
                if (!$jobPendukung->bahanPendukung) continue;
                
                $satuanBase = is_object($jobPendukung->bahanPendukung->satuan) 
                    ? $jobPendukung->bahanPendukung->satuan->nama 
                    : ($jobPendukung->bahanPendukung->satuan ?? 'unit');
                
                try {
                    $qtyBase = $converter->convert(
                        (float) $jobPendukung->jumlah,
                        $jobPendukung->satuan ?: $satuanBase,
                        $satuanBase
                    );
                    
                    $totalBiayaBahan += $jobPendukung->bahanPendukung->harga_satuan * $qtyBase;
                } catch (\Exception $e) {
                    Log::error('Error calculating bahan pendukung cost', [
                        'produk_id' => $produk->id,
                        'job_pendukung_id' => $jobPendukung->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Recalculate BomJobCosting
            $bomJobCosting->recalculate();
            
            Log::info('🔄 BomJobCosting Recalculated', [
                'bom_job_costing_id' => $bomJobCosting->id,
                'produk' => $produk->nama_produk,
                'total_bahan_pendukung' => $bomJobCosting->total_bahan_pendukung,
                'total_hpp' => $bomJobCosting->total_hpp
            ]);
        }
        
        // 3. Update biaya bahan dan harga_bom di produk
        $produk->update([
            'biaya_bahan' => $totalBiayaBahan
        ]);
        
        // Update harga_bom dengan HPP lengkap (BBB + Bahan Pendukung + BTKL + BOP)
        if ($bomJobCosting) {
            $produk->update([
                'harga_bom' => $bomJobCosting->total_hpp  // HPP lengkap
            ]);
            
            Log::info('💰 Harga BOM Updated with HPP', [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'biaya_bahan' => $totalBiayaBahan,
                'harga_bom' => $bomJobCosting->total_hpp
            ]);
        } else {
            Log::info('💰 Biaya Bahan Updated', [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'biaya_bahan' => $totalBiayaBahan
            ]);
        }
    }
    
    /**
     * Handle BahanPendukung "deleting" event.
     * Memberikan keterangan dan menangani BOM yang terpengaruh saat bahan pendukung akan dihapus
     */
    public function deleting(BahanPendukung $bahanPendukung)
    {
        Log::info('🗑️ Bahan Pendukung Akan Dihapus - Processing Affected BOMs', [
            'bahan_pendukung_id' => $bahanPendukung->id,
            'nama_bahan' => $bahanPendukung->nama_bahan,
            'harga_terakhir' => $bahanPendukung->harga_satuan,
            'satuan' => $bahanPendukung->satuan->nama ?? 'Unknown'
        ]);
        
        // Proses semua BOM yang menggunakan bahan pendukung ini
        $this->handleDeletedBahanPendukungInBOMs($bahanPendukung);
    }
    
    /**
     * Handle BahanPendukung "restored" event (untuk soft delete recovery)
     */
    public function restored(BahanPendukung $bahanPendukung)
    {
        Log::info('♻️ Bahan Pendukung Dikembalikan - Restoring BOM Data', [
            'bahan_pendukung_id' => $bahanPendukung->id,
            'nama_bahan' => $bahanPendukung->nama_bahan,
            'harga' => $bahanPendukung->harga_satuan
        ]);
        
        // Kembalikan data BOM yang terpengaruh
        $this->restoreBahanPendukungInBOMs($bahanPendukung);
    }
    
    /**
     * Proses BOM yang terpengaruh saat bahan pendukung dihapus
     */
    private function handleDeletedBahanPendukungInBOMs(BahanPendukung $bahanPendukung)
    {
        $affectedProducts = [];
        
        // Cari semua BomJobBahanPendukung yang menggunakan bahan pendukung ini
        $bomJobBahanPendukungs = \App\Models\BomJobBahanPendukung::where('bahan_pendukung_id', $bahanPendukung->id)
            ->with(['bomJobCosting.produk'])
            ->get();
        
        foreach ($bomJobBahanPendukungs as $jobPendukung) {
            if ($jobPendukung->bomJobCosting && $jobPendukung->bomJobCosting->produk) {
                $this->updateBomJobBahanPendukungWithDeletedNote($jobPendukung, $bahanPendukung);
                $affectedProducts[$jobPendukung->bomJobCosting->produk->id] = $jobPendukung->bomJobCosting->produk;
            }
        }
        
        // Recalculate biaya bahan untuk setiap produk yang terpengaruh
        foreach ($affectedProducts as $produk) {
            $this->recalculateProductBiayaAfterDeletion($produk, $bahanPendukung);
        }
        
        Log::info('📋 BOM Processing Complete', [
            'bahan_pendukung' => $bahanPendukung->nama_bahan,
            'affected_products' => count($affectedProducts),
            'product_names' => array_map(fn($p) => $p->nama_produk, $affectedProducts)
        ]);
    }
    
    /**
     * Update BomJobBahanPendukung dengan catatan penghapusan
     */
    private function updateBomJobBahanPendukungWithDeletedNote($jobPendukung, BahanPendukung $bahanPendukung)
    {
        // Update dengan catatan bahwa bahan pendukung telah dihapus
        $jobPendukung->update([
            'nama_bahan_terhapus' => $bahanPendukung->nama_bahan,
            'harga_terakhir' => $bahanPendukung->harga_satuan,
            'satuan_terakhir' => $bahanPendukung->satuan->nama ?? 'Unknown',
            'catatan_hapus' => 'Bahan pendukung dihapus pada ' . now()->format('Y-m-d H:i:s'),
            'harga_satuan' => 0, // Set ke 0 agar tidak mempengaruhi perhitungan
            'subtotal' => 0 // Set ke 0 agar tidak mempengaruhi perhitungan
        ]);
        
        Log::info('📝 BomJobBahanPendukung Updated with Deletion Note', [
            'job_pendukung_id' => $jobPendukung->id,
            'produk' => $jobPendukung->bomJobCosting->produk->nama_produk ?? 'N/A',
            'bahan_terhapus' => $bahanPendukung->nama_bahan
        ]);
    }
    
    /**
     * Recalculate biaya produk setelah penghapusan bahan pendukung
     */
    private function recalculateProductBiayaAfterDeletion(Produk $produk, BahanPendukung $deletedBahanPendukung)
    {
        $converter = new UnitConverter();
        $totalBiayaBahan = 0;
        
        // 1. Hitung biaya dari Bahan Baku (BomDetail)
        $bomDetails = BomDetail::with('bahanBaku.satuan')
            ->where('bom_id', function($query) use ($produk) {
                $query->select('id')->from('boms')->where('produk_id', $produk->id);
            })
            ->get();
        
        foreach ($bomDetails as $detail) {
            if (!$detail->bahanBaku) continue;
            
            $satuanBase = is_object($detail->bahanBaku->satuan) 
                ? $detail->bahanBaku->satuan->nama 
                : ($detail->bahanBaku->satuan ?? 'unit');
            
            try {
                $qtyBase = $converter->convert(
                    (float) $detail->jumlah,
                    $detail->satuan ?: $satuanBase,
                    $satuanBase
                );
                
                $totalBiayaBahan += $detail->bahanBaku->harga_satuan * $qtyBase;
            } catch (\Exception $e) {
                Log::error('Error calculating remaining bahan baku cost', [
                    'produk_id' => $produk->id,
                    'detail_id' => $detail->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // 2. Hitung biaya dari BomJobBBB yang masih ada
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            $bomJobBBBs = \App\Models\BomJobBBB::with('bahanBaku.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->where('harga_satuan', '>', 0) // Hanya yang masih memiliki harga
                ->get();
            
            foreach ($bomJobBBBs as $bbb) {
                if (!$bbb->bahanBaku) continue;
                
                $satuanBase = is_object($bbb->bahanBaku->satuan) 
                    ? $bbb->bahanBaku->satuan->nama 
                    : ($bbb->bahanBaku->satuan ?? 'unit');
                
                try {
                    $qtyBase = $converter->convert(
                        (float) $bbb->jumlah,
                        $bbb->satuan ?: $satuanBase,
                        $satuanBase
                    );
                    
                    $totalBiayaBahan += $bbb->bahanBaku->harga_satuan * $qtyBase;
                } catch (\Exception $e) {
                    Log::error('Error calculating remaining BBB cost', [
                        'produk_id' => $produk->id,
                        'bbb_id' => $bbb->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 3. Hitung biaya dari BomJobBahanPendukung yang masih ada
            $bomJobBahanPendukung = \App\Models\BomJobBahanPendukung::with('bahanPendukung.satuan')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->where('harga_satuan', '>', 0) // Hanya yang masih memiliki harga
                ->get();
            
            foreach ($bomJobBahanPendukung as $jobPendukung) {
                if (!$jobPendukung->bahanPendukung) continue;
                
                $satuanBase = is_object($jobPendukung->bahanPendukung->satuan) 
                    ? $jobPendukung->bahanPendukung->satuan->nama 
                    : ($jobPendukung->bahanPendukung->satuan ?? 'unit');
                
                try {
                    $qtyBase = $converter->convert(
                        (float) $jobPendukung->jumlah,
                        $jobPendukung->satuan ?: $satuanBase,
                        $satuanBase
                    );
                    
                    $totalBiayaBahan += $jobPendukung->bahanPendukung->harga_satuan * $qtyBase;
                } catch (\Exception $e) {
                    Log::error('Error calculating remaining bahan pendukung cost', [
                        'produk_id' => $produk->id,
                        'job_pendukung_id' => $jobPendukung->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 4. Update BomJobCosting
            $bomJobCosting->recalculate();
        }
        
        // 5. Update biaya bahan di produk
        $produk->update([
            'biaya_bahan' => $totalBiayaBahan
        ]);
        
        // 6. Update harga_bom dengan HPP lengkap
        if ($bomJobCosting) {
            $produk->update([
                'harga_bom' => $bomJobCosting->total_hpp
            ]);
            
            Log::info('💰 Harga BOM Updated After Deletion', [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'bahan_pendukung_dihapus' => $deletedBahanPendukung->nama_bahan,
                'biaya_bahan_baru' => $totalBiayaBahan,
                'harga_bom' => $bomJobCosting->total_hpp
            ]);
        } else {
            Log::info('💰 Biaya Bahan Updated After Deletion', [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'bahan_pendukung_dihapus' => $deletedBahanPendukung->nama_bahan,
                'biaya_bahan_baru' => $totalBiayaBahan
            ]);
        }
        
        Log::info('✅ Product Biaya Recalculated After Deletion', [
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama_produk,
            'biaya_bahan' => $totalBiayaBahan
        ]);
    }
    
    /**
     * Kembalikan data BOM saat bahan pendukung di-restore
     */
    private function restoreBahanPendukungInBOMs(BahanPendukung $bahanPendukung)
    {
        // Implementasi restore logic (jika diperlukan)
        Log::info('🔄 Restore BOM data for restored bahan pendukung', [
            'bahan_pendukung_id' => $bahanPendukung->id,
            'nama_bahan' => $bahanPendukung->nama_bahan
        ]);
    }
}
