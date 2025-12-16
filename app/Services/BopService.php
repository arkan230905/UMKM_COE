<?php

namespace App\Services;

use App\Models\Bop;
use Illuminate\Support\Facades\DB;

class BopService
{
    /**
     * Update actual BOP based on payment transactions
     *
     * @param string $kodeAkun
     * @param float $amount
     * @param string $type 'add' or 'subtract'
     * @return bool
     */
    public static function updateAktual($kodeAkun, $amount, $type = 'add')
    {
        try {
            DB::beginTransaction();

            $bop = Bop::where('kode_akun', $kodeAkun)->first();

            if (!$bop) {
                // If BOP doesn't exist for this account, create a new one with budget 0
                $bop = Bop::create([
                    'kode_akun' => $kodeAkun,
                    'nama_akun' => 'Auto-created from payment', // This should be updated with actual account name
                    'budget' => 0,
                    'aktual' => 0,
                    'is_active' => true
                ]);
            }

            // Update actual amount
            if ($type === 'add') {
                $bop->aktual += $amount;
            } else {
                $bop->aktual -= $amount;
                // Ensure actual doesn't go below 0
                $bop->aktual = max(0, $bop->aktual);
            }

            $bop->save();
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update BOP actual: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recalculate actual BOP for an account based on all payment transactions
     *
     * @param string $kodeAkun
     * @param float $totalAmount
     * @return bool
     */
    public static function recalculateAktual($kodeAkun, $totalAmount)
    {
        try {
            \Log::info("BopService::recalculateAktual called for COA: {$kodeAkun}, Amount: {$totalAmount}");
            
            $bop = Bop::where('kode_akun', $kodeAkun)->first();

            if (!$bop) {
                \Log::info("BOP not found for COA: {$kodeAkun}, creating new one");
                // If BOP doesn't exist, create a new one
                $coa = \App\Models\Coa::where('kode_akun', $kodeAkun)->first();
                $bop = Bop::create([
                    'kode_akun' => $kodeAkun,
                    'nama_akun' => $coa ? $coa->nama_akun : 'Auto-created from payment',
                    'budget' => 0,
                    'aktual' => $totalAmount,
                    'is_active' => true
                ]);
                \Log::info("Created new BOP with aktual: {$totalAmount}");
                return true;
            }

            // Update with the new total amount
            $oldAktual = $bop->aktual;
            $bop->aktual = $totalAmount;
            $bop->save();
            
            \Log::info("Updated BOP aktual from {$oldAktual} to {$totalAmount} for COA: {$kodeAkun}");

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to recalculate BOP actual: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return false;
        }
    }
}
