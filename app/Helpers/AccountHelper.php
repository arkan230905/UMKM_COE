<?php

namespace App\Helpers;

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class AccountHelper
{
    /**
     * Kode akun Kas & Bank yang digunakan di seluruh sistem
     * STANDAR: Gunakan ini di SEMUA controller untuk konsistensi
     * Updated to match actual COA and accounts data
     */
    const KAS_BANK_CODES = ['1101', '1102', '1110', '1120', '101', '102'];
    
    /**
     * Get semua akun Kas & Bank
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasBankAccounts()
    {
        // Prioritas: Cari COA yang mengandung kata 'kas' atau 'bank'
        $coaAccounts = Coa::where('tipe_akun', 'Asset')
            ->where(function($query) {
                $query->where('nama_akun', 'like', '%kas%')
                      ->orWhere('nama_akun', 'like', '%bank%');
            })
            ->orderBy('kode_akun')
            ->get();
        
        // Jika ada COA dengan nama kas/bank, gunakan itu
        if ($coaAccounts->count() > 0) {
            return $coaAccounts;
        }
        
        // Fallback: cari COA dengan kode yang umum untuk kas/bank
        $fallbackCoas = Coa::whereIn('kode_akun', ['1101', '1102', '1110', '1120', '101', '102'])
            ->where('tipe_akun', 'Asset')
            ->orderBy('kode_akun')
            ->get();
            
        return $fallbackCoas;
    }
    
    /**
     * Get akun Kas saja (1110, 101)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasAccounts()
    {
        return Coa::whereIn('kode_akun', ['1110', '101'])
            ->where('tipe_akun', 'Asset')
            ->orderBy('kode_akun')
            ->get();
    }
    
    /**
     * Get akun Bank saja (1120, 102)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBankAccounts()
    {
        return Coa::whereIn('kode_akun', ['1120', '102'])
            ->where('tipe_akun', 'Asset')
            ->orderBy('kode_akun')
            ->get();
    }
    
    /**
     * Check apakah kode akun adalah akun Kas/Bank
     * 
     * @param string $kodeAkun
     * @return bool
     */
    public static function isKasBankAccount($kodeAkun)
    {
        return in_array($kodeAkun, ['1101', '1102', '1110', '1120', '101', '102']);
    }
    
    /**
     * Get nama kategori akun (Kas/Bank/Lainnya)
     * 
     * @param string $kodeAkun
     * @return string
     */
    public static function getAccountCategory($kodeAkun)
    {
        if (in_array($kodeAkun, ['1101', '1110', '101'])) {
            return 'Kas';
        } elseif (in_array($kodeAkun, ['1102', '1120', '102'])) {
            return 'Bank';
        }
        return 'Lainnya';
    }
    
    /**
     * Get saldo saat ini untuk akun Kas/Bank
     * 
     * @param string $kodeAkun
     * @return float
     */
    public static function getCurrentBalance($kodeAkun)
    {
        // Cari COA berdasarkan kode_akun
        $coa = Coa::where('kode_akun', $kodeAkun)->first();
        if (!$coa) {
            return 0;
        }
        
        // Saldo awal dari COA
        $saldoAwal = is_numeric($coa->saldo_awal ?? 0) ? (float) ($coa->saldo_awal ?? 0) : 0;
        
        // Hitung saldo dari journal lines
        $saldo = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.coa_id', $coa->id)
            ->selectRaw('SUM(debit) - SUM(credit) as saldo')
            ->value('saldo') ?? 0;
            
        return $saldoAwal + $saldo;
    }
    
    /**
     * Get semua akun Kas & Bank dengan saldo
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasBankAccountsWithBalance()
    {
        $accounts = self::getKasBankAccounts();
        
        foreach ($accounts as $account) {
            $account->saldo = self::getCurrentBalance($account->kode_akun);
        }
        
        return $accounts;
    }
}
