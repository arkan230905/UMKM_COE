<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Produk;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class PersediaanSaldoAwalService
{
    /**
     * Posting otomatis saldo awal persediaan dari bahan baku, bahan pendukung, dan produk ke COA
     */
    public static function postSaldoAwalPersediaan()
    {
        try {
            DB::beginTransaction();
            
            // Reset semua saldo awal COA persediaan ke 0 terlebih dahulu
            $persediaanCoas = Coa::where('nama_akun', 'like', '%persediaan%')->get();
            foreach($persediaanCoas as $coa) {
                $coa->saldo_awal = 0;
                $coa->save();
            }
            
            // Posting dari Bahan Baku
            $bahanBakus = BahanBaku::with(['coaPersediaan'])->get();
            foreach($bahanBakus as $bb) {
                if ($bb->coaPersediaan && $bb->stok > 0 && $bb->harga_satuan > 0) {
                    $totalValue = $bb->stok * $bb->harga_satuan;
                    $coa = $bb->coaPersediaan;
                    
                    // Tambahkan ke saldo awal COA
                    $currentSaldoAwal = $coa->saldo_awal ?? 0;
                    $coa->saldo_awal = $currentSaldoAwal + $totalValue;
                    $coa->save();
                }
            }
            
            // Posting dari Bahan Pendukung
            $bahanPendukungs = BahanPendukung::with(['coaPersediaan'])->get();
            foreach($bahanPendukungs as $bp) {
                if ($bp->coaPersediaan && $bp->stok > 0 && $bp->harga_satuan > 0) {
                    $totalValue = $bp->stok * $bp->harga_satuan;
                    $coa = $bp->coaPersediaan;
                    
                    // Tambahkan ke saldo awal COA
                    $currentSaldoAwal = $coa->saldo_awal ?? 0;
                    $coa->saldo_awal = $currentSaldoAwal + $totalValue;
                    $coa->save();
                }
            }
            
            // Posting dari Produk (Barang Jadi)
            $produks = Produk::with(['coaPersediaan'])->get();
            foreach($produks as $produk) {
                if ($produk->coaPersediaan && $produk->stok > 0) {
                    // Use HPP or harga_bom for more accurate cost
                    $hargaPokok = $produk->hpp ?? $produk->harga_bom ?? $produk->harga_jual ?? 0;
                    if($hargaPokok > 0) {
                        $totalValue = $produk->stok * $hargaPokok;
                        $coa = $produk->coaPersediaan;
                        
                        // Tambahkan ke saldo awal COA
                        $currentSaldoAwal = $coa->saldo_awal ?? 0;
                        $coa->saldo_awal = $currentSaldoAwal + $totalValue;
                        $coa->save();
                    }
                }
            }
            
            DB::commit();
            return [
                'success' => true,
                'message' => 'Posting saldo awal persediaan berhasil!'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update saldo awal COA persediaan untuk item tertentu
     */
    public static function updateSaldoAwalItem($item, $type = 'bahan_baku')
    {
        if (!$item->coaPersediaan || $item->stok <= 0) {
            return false;
        }
        
        try {
            $coa = $item->coaPersediaan;
            
            // Hitung ulang total untuk COA ini
            $newSaldoAwal = 0;
            
            if ($type === 'bahan_baku') {
                $allItems = BahanBaku::with(['coaPersediaan'])
                    ->where('coa_persediaan_id', $coa->id)
                    ->get();
                    
                foreach($allItems as $item) {
                    if ($item->stok > 0 && $item->harga_satuan > 0) {
                        $newSaldoAwal += $item->stok * $item->harga_satuan;
                    }
                }
            } elseif ($type === 'bahan_pendukung') {
                $allItems = BahanPendukung::with(['coaPersediaan'])
                    ->where('coa_persediaan_id', $coa->id)
                    ->get();
                    
                foreach($allItems as $item) {
                    if ($item->stok > 0 && $item->harga_satuan > 0) {
                        $newSaldoAwal += $item->stok * $item->harga_satuan;
                    }
                }
            } elseif ($type === 'produk') {
                $allItems = Produk::with(['coaPersediaan'])
                    ->where('coa_persediaan_id', $coa->id)
                    ->get();
                    
                foreach($allItems as $item) {
                    if ($item->stok > 0) {
                        $hargaPokok = $item->hpp ?? $item->harga_bom ?? $item->harga_jual ?? 0;
                        if($hargaPokok > 0) {
                            $newSaldoAwal += $item->stok * $hargaPokok;
                        }
                    }
                }
            }
            
            $coa->saldo_awal = $newSaldoAwal;
            $coa->save();
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }
}
