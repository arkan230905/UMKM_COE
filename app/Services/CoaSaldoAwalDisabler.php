<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk menonaktifkan logika update saldo awal COA dari bahan baku dan bahan pendukung
 */
class CoaSaldoAwalDisabler
{
    /**
     * Cek apakah item harus dikecualikan dari update saldo awal COA
     */
    public static function shouldSkipSaldoAwalUpdate($item): bool
    {
        // Cek flag exclusion jika ada
        if (isset($item->exclude_from_coa) && $item->exclude_from_coa) {
            return true;
        }
        
        if (isset($item->coa_recording_disabled) && $item->coa_recording_disabled) {
            return true;
        }
        
        // Default: skip untuk semua bahan baku dan bahan pendukung
        return true;
    }
    
    /**
     * Wrapper untuk update saldo awal COA yang bisa di-disable
     */
    public static function updateCoaSaldoAwal($coa, $amount, $operation = 'add'): bool
    {
        // Selalu skip update saldo awal COA untuk bahan
        Log::info("Skipping COA saldo awal update", [
            'coa_code' => $coa->kode_akun ?? 'unknown',
            'coa_name' => $coa->nama_akun ?? 'unknown',
            'amount' => $amount,
            'operation' => $operation,
            'reason' => 'COA saldo awal update disabled for bahan'
        ]);
        
        return false; // Tidak melakukan update
    }
    
    /**
     * Wrapper untuk PersediaanSaldoAwalService yang bisa di-disable
     */
    public static function updateSaldoAwalItem($item, $type = 'bahan_baku'): bool
    {
        if (self::shouldSkipSaldoAwalUpdate($item)) {
            Log::info("Skipping saldo awal update for item", [
                'item_type' => $type,
                'item_id' => $item->id ?? 'unknown',
                'item_name' => $item->nama_bahan ?? 'unknown',
                'reason' => 'Item excluded from COA saldo awal updates'
            ]);
            return false;
        }
        
        // Jika tidak di-skip, panggil service asli
        return \App\Services\PersediaanSaldoAwalService::updateSaldoAwalItem($item, $type);
    }
    
    /**
     * Reset semua saldo awal COA bahan baku dan bahan pendukung ke nol
     */
    public static function resetBahanCoaSaldoAwal(): int
    {
        $resetCount = 0;
        
        // Reset COA bahan baku
        $bahanBakuCoas = Coa::where('kode_akun', 'LIKE', '1104%')->get();
        foreach ($bahanBakuCoas as $coa) {
            $oldSaldo = $coa->saldo_awal;
            $coa->saldo_awal = 0;
            $coa->save();
            
            if ($oldSaldo != 0) {
                $resetCount++;
                Log::info("Reset bahan baku COA saldo awal", [
                    'coa_code' => $coa->kode_akun,
                    'coa_name' => $coa->nama_akun,
                    'old_saldo' => $oldSaldo,
                    'new_saldo' => 0
                ]);
            }
        }
        
        // Reset COA bahan pendukung
        $bahanPendukungCoas = Coa::where('kode_akun', 'LIKE', '113%')->get();
        foreach ($bahanPendukungCoas as $coa) {
            $oldSaldo = $coa->saldo_awal;
            $coa->saldo_awal = 0;
            $coa->save();
            
            if ($oldSaldo != 0) {
                $resetCount++;
                Log::info("Reset bahan pendukung COA saldo awal", [
                    'coa_code' => $coa->kode_akun,
                    'coa_name' => $coa->nama_akun,
                    'old_saldo' => $oldSaldo,
                    'new_saldo' => 0
                ]);
            }
        }
        
        return $resetCount;
    }
    
    /**
     * Nonaktifkan semua logika update saldo awal untuk bahan yang sudah ada
     */
    public static function disableExistingBahanSaldoAwal(): array
    {
        $stats = [
            'bahan_baku_updated' => 0,
            'bahan_pendukung_updated' => 0,
            'coa_reset' => 0
        ];
        
        // Update flag untuk bahan baku
        $stats['bahan_baku_updated'] = BahanBaku::whereNull('exclude_from_coa')
            ->orWhere('exclude_from_coa', false)
            ->update([
                'exclude_from_coa' => true,
                'coa_recording_disabled' => true,
                'coa_exclusion_date' => now()
            ]);
        
        // Update flag untuk bahan pendukung
        $stats['bahan_pendukung_updated'] = BahanPendukung::whereNull('exclude_from_coa')
            ->orWhere('exclude_from_coa', false)
            ->update([
                'exclude_from_coa' => true,
                'coa_recording_disabled' => true,
                'coa_exclusion_date' => now()
            ]);
        
        // Reset saldo awal COA
        $stats['coa_reset'] = self::resetBahanCoaSaldoAwal();
        
        Log::info("Disabled saldo awal updates for existing bahan", $stats);
        
        return $stats;
    }
}