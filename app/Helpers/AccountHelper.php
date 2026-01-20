<?php

namespace App\Helpers;

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class AccountHelper
{
    /**
     * Kode akun Kas & Bank yang digunakan di seluruh sistem
     * STANDAR: Gunakan ini di SEMUA controller untuk konsistensi
     */
    const KAS_BANK_CODES = ['1101', '1102', '1103', '101', '102'];
    
    /**
     * Get semua akun Kas & Bank
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasBankAccounts()
    {
        return Coa::whereIn('kode_akun', self::KAS_BANK_CODES)
            ->where('tipe_akun', 'Asset')
            ->where('is_akun_header', '!=', 1)
            ->orderBy('kode_akun')
            ->get();
    }
    
    /**
     * Get akun Kas saja (1101, 101)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasAccounts()
    {
        return Coa::whereIn('kode_akun', ['1101', '101'])
            ->where('tipe_akun', 'Asset')
            ->where('is_akun_header', '!=', 1)
            ->orderBy('kode_akun')
            ->get();
    }
    
    /**
     * Get akun Bank saja (1102, 102)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBankAccounts()
    {
        return Coa::whereIn('kode_akun', ['1102', '102'])
            ->where('tipe_akun', 'Asset')
            ->where('is_akun_header', '!=', 1)
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
        return in_array($kodeAkun, self::KAS_BANK_CODES);
    }
    
    /**
     * Get nama kategori akun (Kas/Bank/Lainnya)
     * 
     * @param string $kodeAkun
     * @return string
     */
    public static function getAccountCategory($kodeAkun)
    {
        if (in_array($kodeAkun, ['1101', '101'])) {
            return 'Kas';
        } elseif (in_array($kodeAkun, ['1102', '102'])) {
            return 'Bank';
        } elseif ($kodeAkun === '1103') {
            return 'Kas Lainnya';
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
        // Cari account_id yang sesuai dengan kode_akun ini
        $account = DB::table('accounts')->where('code', $kodeAkun)->first();
        if (!$account) {
            // Jika tidak ada di accounts, gunakan saldo awal COA
            $coa = Coa::find($kodeAkun);
            return is_numeric($coa->saldo_awal ?? 0) ? (float) ($coa->saldo_awal ?? 0) : 0;
        }
        
        // Hitung saldo dari journal lines
        $saldo = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.account_id', $account->id)
            ->selectRaw('SUM(debit) - SUM(credit) as saldo')
            ->value('saldo') ?? 0;
            
        // Tambahkan saldo awal dari COA jika ada
        $coa = Coa::find($kodeAkun);
        $saldoAwal = is_numeric($coa->saldo_awal ?? 0) ? (float) ($coa->saldo_awal ?? 0) : 0;
        
        return $saldo + $saldoAwal;
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
