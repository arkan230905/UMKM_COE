<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCoaPersediaanService
{
    /**
     * Sinkronisasi semua COA persediaan dengan data stok master
     */
    public static function syncAllCoaPersediaan()
    {
        try {
            DB::beginTransaction();
            
            Log::info('Starting COA persediaan synchronization');
            
            // Reset semua saldo awal COA persediaan ke 0
            $persediaanCoas = Coa::where('nama_akun', 'like', '%persediaan%')->get();
            
            foreach($persediaanCoas as $coa) {
                $coa->saldo_awal = 0;
                $coa->save();
            }
            
            // Sinkronisasi dari Bahan Baku
            $bahanBakus = BahanBaku::with(['coaPersediaan', 'satuan'])->get();
            
            foreach($bahanBakus as $bb) {
                if ($bb->coaPersediaan && $bb->stok > 0 && $bb->harga_satuan > 0) {
                    $totalValue = $bb->stok * $bb->harga_satuan;
                    $coa = $bb->coaPersediaan;
                    
                    // Tambahkan ke saldo awal COA
                    $currentSaldoAwal = $coa->saldo_awal ?? 0;
                    $coa->saldo_awal = $currentSaldoAwal + $totalValue;
                    $coa->save();
                    
                    Log::info("Updated COA {$coa->kode_akun} with bahan baku {$bb->nama_bahan}: Rp " . number_format($totalValue, 0, ',', '.'));
                }
            }
            
            // Sinkronisasi dari Bahan Pendukung
            $bahanPendukungs = BahanPendukung::with(['coaPersediaan', 'satuan'])->get();
            
            foreach($bahanPendukungs as $bp) {
                if ($bp->coaPersediaan && $bp->stok > 0 && $bp->harga_satuan > 0) {
                    $totalValue = $bp->stok * $bp->harga_satuan;
                    $coa = $bp->coaPersediaan;
                    
                    // Tambahkan ke saldo awal COA
                    $currentSaldoAwal = $coa->saldo_awal ?? 0;
                    $coa->saldo_awal = $currentSaldoAwal + $totalValue;
                    $coa->save();
                    
                    Log::info("Updated COA {$coa->kode_akun} with bahan pendukung {$bp->nama_bahan}: Rp " . number_format($totalValue, 0, ',', '.'));
                }
            }
            
            DB::commit();
            
            Log::info('COA persediaan synchronization completed successfully');
            
            return [
                'success' => true,
                'message' => 'Semua COA persediaan berhasil disinkronisasi dengan data stok!'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('COA persediaan synchronization failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Sinkronisasi COA persediaan untuk item tertentu
     */
    public static function syncItemCoa($item, $type = 'bahan_baku')
    {
        try {
            if (!$item->coaPersediaan) {
                return false;
            }
            
            $totalValue = $item->stok * $item->harga_satuan;
            $coa = $item->coaPersediaan;
            
            // Reset saldo awal COA ini ke 0 dulu
            $coa->saldo_awal = 0;
            $coa->save();
            
            // Set ke nilai yang benar
            $coa->saldo_awal = $totalValue;
            $coa->save();
            
            Log::info("Synced COA {$coa->kode_akun} for {$type} {$item->nama_bahan}: Rp " . number_format($totalValue, 0, ',', '.'));
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to sync COA for {$type} {$item->nama_bahan}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update stock movement agar konsisten dengan master data
     */
    public static function syncStockMovements()
    {
        try {
            DB::beginTransaction();
            
            Log::info('Starting stock movements synchronization');
            
            // Update Bahan Baku stock movements
            $bahanBakus = BahanBaku::with('satuan')->get();
            
            foreach($bahanBakus as $bb) {
                if ($bb->stok > 0) {
                    // Hapus initial stock lama
                    StockMovement::where('item_type', 'material')
                        ->where('item_id', $bb->id)
                        ->where('ref_type', 'initial_stock')
                        ->delete();
                    
                    // Buat initial stock baru
                    StockMovement::create([
                        'item_type' => 'material',
                        'item_id' => $bb->id,
                        'tanggal' => now()->startOfMonth()->format('Y-m-d'),
                        'direction' => 'in',
                        'qty' => $bb->stok,
                        'satuan' => $bb->satuan->nama ?? 'Unit',
                        'unit_cost' => $bb->harga_satuan,
                        'total_cost' => $bb->stok * $bb->harga_satuan,
                        'ref_type' => 'initial_stock',
                        'ref_id' => 0
                    ]);
                    
                    Log::info("Synced stock movement for bahan baku {$bb->nama_bahan}: {$bb->stok} {$bb->satuan->nama}");
                }
            }
            
            // Update Bahan Pendukung stock movements
            $bahanPendukungs = BahanPendukung::with('satuan')->get();
            
            foreach($bahanPendukungs as $bp) {
                if ($bp->stok > 0) {
                    // Hapus initial stock lama
                    StockMovement::where('item_type', 'support')
                        ->where('item_id', $bp->id)
                        ->where('ref_type', 'initial_stock')
                        ->delete();
                    
                    // Buat initial stock baru
                    StockMovement::create([
                        'item_type' => 'support',
                        'item_id' => $bp->id,
                        'tanggal' => now()->startOfMonth()->format('Y-m-d'),
                        'direction' => 'in',
                        'qty' => $bp->stok,
                        'satuan' => $bp->satuan->nama ?? 'Unit',
                        'unit_cost' => $bp->harga_satuan,
                        'total_cost' => $bp->stok * $bp->harga_satuan,
                        'ref_type' => 'initial_stock',
                        'ref_id' => 0
                    ]);
                    
                    Log::info("Synced stock movement for bahan pendukung {$bp->nama_bahan}: {$bp->stok} {$bp->satuan->nama}");
                }
            }
            
            DB::commit();
            
            Log::info('Stock movements synchronization completed successfully');
            
            return [
                'success' => true,
                'message' => 'Stock movements berhasil disinkronisasi!'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Stock movements synchronization failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Full synchronization: COA + Stock Movements
     */
    public static function fullSync()
    {
        $coaResult = self::syncAllCoaPersediaan();
        $stockResult = self::syncStockMovements();
        
        return [
            'coa_sync' => $coaResult,
            'stock_sync' => $stockResult,
            'success' => $coaResult['success'] && $stockResult['success'],
            'message' => 'Full synchronization completed'
        ];
    }
}
