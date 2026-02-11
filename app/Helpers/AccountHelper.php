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
    const KAS_BANK_CODES = ['1110', '1120', '101', '102'];
    
    /**
     * Get semua akun Kas & Bank
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasBankAccounts()
    {
        // Prioritas: Gunakan COA yang memiliki saldo awal
        $coaAccounts = Coa::whereIn('kode_akun', ['1110', '1120'])
            ->where('tipe_akun', 'Asset')
            ->where('is_akun_header', '!=', 1)
            ->orderBy('kode_akun')
            ->get();
        
        // Jika ada COA dengan saldo awal, gunakan itu
        if ($coaAccounts->count() > 0) {
            return $coaAccounts;
        }
        
        // Fallback: cari di accounts table yang memiliki transaksi
        $activeAccountCodes = DB::table('accounts')
            ->join('journal_lines', 'accounts.id', '=', 'journal_lines.account_id')
            ->whereIn('accounts.code', ['101', '102'])
            ->distinct()
            ->pluck('accounts.code')
            ->toArray();
        
        $virtualCoas = collect();
        foreach ($activeAccountCodes as $code) {
            $accountRecord = DB::table('accounts')->where('code', $code)->first();
            if ($accountRecord) {
                $virtualCoa = new Coa();
                $virtualCoa->kode_akun = $accountRecord->code;
                $virtualCoa->nama_akun = $accountRecord->name;
                $virtualCoa->tipe_akun = 'Asset';
                $virtualCoa->is_akun_header = 0;
                $virtualCoa->saldo_awal = 0;
                $virtualCoas->push($virtualCoa);
            }
        }
        
        return $virtualCoas->sortBy('kode_akun');
    }
    
    /**
     * Get akun Kas saja (1110, 101)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasAccounts()
    {
        // Try COA first
        $coaAccounts = Coa::whereIn('kode_akun', ['1110'])
            ->where('tipe_akun', 'Asset')
            ->where('is_akun_header', '!=', 1)
            ->orderBy('kode_akun')
            ->get();
            
        if ($coaAccounts->count() > 0) {
            return $coaAccounts;
        }
        
        // Fallback to accounts table
        $accountRecord = DB::table('accounts')->where('code', '101')->first();
        if ($accountRecord) {
            $virtualCoa = new Coa();
            $virtualCoa->kode_akun = $accountRecord->code;
            $virtualCoa->nama_akun = $accountRecord->name;
            $virtualCoa->tipe_akun = 'Asset';
            $virtualCoa->is_akun_header = 0;
            $virtualCoa->saldo_awal = 0;
            return collect([$virtualCoa]);
        }
        
        return collect();
    }
    
    /**
     * Get akun Bank saja (1120, 102)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBankAccounts()
    {
        // Try COA first
        $coaAccounts = Coa::whereIn('kode_akun', ['1120'])
            ->where('tipe_akun', 'Asset')
            ->where('is_akun_header', '!=', 1)
            ->orderBy('kode_akun')
            ->get();
            
        if ($coaAccounts->count() > 0) {
            return $coaAccounts;
        }
        
        // Fallback to accounts table
        $accountRecord = DB::table('accounts')->where('code', '102')->first();
        if ($accountRecord) {
            $virtualCoa = new Coa();
            $virtualCoa->kode_akun = $accountRecord->code;
            $virtualCoa->nama_akun = $accountRecord->name;
            $virtualCoa->tipe_akun = 'Asset';
            $virtualCoa->is_akun_header = 0;
            $virtualCoa->saldo_awal = 0;
            return collect([$virtualCoa]);
        }
        
        return collect();
    }
    
    /**
     * Check apakah kode akun adalah akun Kas/Bank
     * 
     * @param string $kodeAkun
     * @return bool
     */
    public static function isKasBankAccount($kodeAkun)
    {
        return in_array($kodeAkun, ['1110', '1120', '101', '102']);
    }
    
    /**
     * Get nama kategori akun (Kas/Bank/Lainnya)
     * 
     * @param string $kodeAkun
     * @return string
     */
    public static function getAccountCategory($kodeAkun)
    {
        if (in_array($kodeAkun, ['1110', '101'])) {
            return 'Kas';
        } elseif (in_array($kodeAkun, ['1120', '102'])) {
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
        // Cari account_id yang sesuai dengan kode_akun ini
        $account = DB::table('accounts')->where('code', $kodeAkun)->first();
        if (!$account) {
            // Jika tidak ada di accounts, gunakan saldo awal COA
            $coa = Coa::where('kode_akun', $kodeAkun)->first();
            return is_numeric($coa->saldo_awal ?? 0) ? (float) ($coa->saldo_awal ?? 0) : 0;
        }
        
        // Hitung saldo dari journal lines
        $saldo = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.account_id', $account->id)
            ->selectRaw('SUM(debit) - SUM(credit) as saldo')
            ->value('saldo') ?? 0;
            
        // Tambahkan saldo awal dari COA jika ada
        $coa = Coa::where('kode_akun', $kodeAkun)->first();
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
