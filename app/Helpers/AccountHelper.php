<?php

namespace App\Helpers;

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class AccountHelper
{
    /**
     * Kode akun Kas & Bank yang digunakan di seluruh sistem
     * STANDAR: Gunakan ini di SEMUA controller untuk konsistensi
     * Updated to match kas bank report accounts (111, 112, 113, 118)
     */
    const KAS_BANK_CODES = ['111', '112', '113', '118'];
    
    /**
     * Get semua akun Kas & Bank dengan format metode-akun COA
     * Format: Nama Akun = lowercase(nama) (kode_akun)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasBankAccounts()
    {
        // Hanya ambil akun bank yang memiliki nomor rekening (untuk transfer)
        // dan akun kas untuk pembayaran tunai
        $coaAccounts = Coa::whereIn('kode_akun', ['111', '112', '113'])
            ->whereIn('tipe_akun', ['Asset', 'Aset', 'ASET'])
            ->orderBy('kode_akun')
            ->get();
        
        // Jika tidak ada, fallback ke pencarian berdasarkan nama
        if ($coaAccounts->count() === 0) {
            $coaAccounts = Coa::where('tipe_akun', 'Asset')
                ->where(function($query) {
                    $query->where('nama_akun', 'like', '%kas%')
                          ->orWhere('nama_akun', 'like', '%bank%');
                })
                ->orderBy('kode_akun')
                ->get();
        }
        
        return $coaAccounts;
    }
    
    /**
     * Get akun Bank saja yang memiliki nomor rekening (untuk transfer)
     * Updated to match the query used in Tentang Perusahaan page
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBankAccountsForTransfer()
    {
        return Coa::where('tipe_akun', 'asset')
            ->where(function($query) {
                $query->where('nama_akun', 'like', '%bank%')
                      ->orWhere('kode_akun', '111');
            })
            ->whereNotNull('nomor_rekening')
            ->where('nomor_rekening', '!=', '')
            ->orderBy('kode_akun')
            ->get();
    }
    
    /**
     * Get akun Kas saja (112, 113)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasAccounts()
    {
        return Coa::whereIn('kode_akun', ['112', '113'])
            ->where('tipe_akun', 'Asset')
            ->orderBy('kode_akun')
            ->get();
    }
    
    /**
     * Get akun Bank saja (111)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBankAccounts()
    {
        return Coa::whereIn('kode_akun', ['111'])
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
        return in_array($kodeAkun, ['111', '112', '113', '118']);
    }
    
    /**
     * Get nama kategori akun (Kas/Bank/Piutang/Lainnya)
     * 
     * @param string $kodeAkun
     * @return string
     */
    public static function getAccountCategory($kodeAkun)
    {
        if (in_array($kodeAkun, ['112', '113'])) {
            return 'Kas';
        } elseif ($kodeAkun === '111') {
            return 'Bank';
        } elseif ($kodeAkun === '118') {
            return 'Piutang';
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
