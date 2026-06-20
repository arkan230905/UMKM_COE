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
     * @param int|null $userId Optional user_id for multi-tenant filtering
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasBankAccounts($userId = null)
    {
        $query = Coa::whereIn('kode_akun', ['111', '112', '113'])
            ->whereIn('tipe_akun', ['Asset', 'Aset', 'ASET']);
        
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        
        $coaAccounts = $query->orderBy('kode_akun')->get();
        
        // Jika tidak ada, fallback ke pencarian berdasarkan nama
        if ($coaAccounts->count() === 0) {
            $fallbackQuery = Coa::where('tipe_akun', 'Asset')
                ->where(function($query) {
                    $query->where('nama_akun', 'like', '%kas%')
                          ->orWhere('nama_akun', 'like', '%bank%');
                });
            
            // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
            if ($userId !== null) {
                $fallbackQuery->where('user_id', $userId);
            }
            
            $coaAccounts = $fallbackQuery->orderBy('kode_akun')->get();
        }
        
        return $coaAccounts;
    }
    
    /**
     * Get akun Bank saja yang memiliki nomor rekening (untuk transfer)
     * Updated to match the query used in Tentang Perusahaan page
     * 
     * @param int|null $userId Optional user_id for multi-tenant filtering
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBankAccountsForTransfer($userId = null)
    {
        $bankKeywords = ['bank', 'bca', 'mandiri', 'bri', 'bni', 'bsi', 'cimb', 'danamon', 'permata', 'btn', 'bpd', 'maybank', 'mega', 'ocbc', 'panin', 'sinarmas', 'bukopin', 'jenius', 'jago', 'allo', 'uob', 'hana', 'muamalat', 'dki', 'bjb', 'jabar', 'jatim', 'jateng'];
        
        $query = Coa::whereIn('tipe_akun', ['Asset', 'asset', 'Aset', 'ASET', 'Aktiva'])
            ->where(function($query) use ($bankKeywords) {
                $query->where('kode_akun', 'like', '111%');
                foreach ($bankKeywords as $keyword) {
                    $query->orWhere('nama_akun', 'like', '%' . $keyword . '%');
                }
            })
            ->whereNotNull('nomor_rekening')
            ->where('nomor_rekening', '!=', '');
        
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        
        return $query->orderBy('kode_akun')->get();
    }
    
    /**
     * Get akun Kas saja (112x, 113x - Kas dan Kas Kecil)
     * 
     * @param int|null $userId Optional user_id for multi-tenant filtering
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasAccounts($userId = null)
    {
        $query = Coa::where(function($q) {
                $q->where('kode_akun', 'like', '112%')
                  ->orWhere('kode_akun', 'like', '113%');
            })
            ->whereIn('tipe_akun', ['Asset', 'Aset', 'ASET']);
        
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        
        return $query->orderBy('kode_akun')->get();
    }
    
    /**
     * Get akun Bank saja (111x - semua akun bank)
     * 
     * @param int|null $userId Optional user_id for multi-tenant filtering
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBankAccounts($userId = null)
    {
        $query = Coa::where('kode_akun', 'like', '111%')
            ->whereIn('tipe_akun', ['Asset', 'Aset', 'ASET']);
        
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        
        return $query->orderBy('kode_akun')->get();
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
     * @param int|null $userId Optional user_id for multi-tenant filtering
     * @return float
     */
    public static function getCurrentBalance($kodeAkun, $userId = null)
    {
        // Cari COA berdasarkan kode_akun
        $query = Coa::where('kode_akun', $kodeAkun);
        
        // 🔒 SECURITY: Filter by user_id for multi-tenant isolation
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        
        $coa = $query->first();
        if (!$coa) {
            return 0;
        }
        
        // Saldo awal dari COA
        $saldoAwal = is_numeric($coa->saldo_awal ?? 0) ? (float) ($coa->saldo_awal ?? 0) : 0;
        
        // Hitung saldo dari journal lines
        // Note: journal_entries tidak punya user_id, tapi COA sudah difilter by user_id
        // Jadi saldo yang dihitung otomatis adalah saldo untuk COA milik user tersebut
        $journalQuery = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.coa_id', $coa->id);
        
        $saldo = $journalQuery->selectRaw('SUM(debit) - SUM(credit) as saldo')
            ->value('saldo') ?? 0;
            
        return $saldoAwal + $saldo;
    }
    
    /**
     * Get semua akun Kas & Bank dengan saldo
     * 
     * @param int|null $userId Optional user_id for multi-tenant filtering
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasBankAccountsWithBalance($userId = null)
    {
        $accounts = self::getKasBankAccounts($userId);
        
        foreach ($accounts as $account) {
            $account->saldo = self::getCurrentBalance($account->kode_akun, $userId);
        }
        
        return $accounts;
    }
}
