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
     * Includes all sub-accounts: 111x (Bank), 112x (Kas), 113x (Kas Kecil)
     * 
     * @param int|null $userId Optional user_id for multi-tenant filtering
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getKasBankAccounts($userId = null)
    {
        $query = Coa::where(function($q) {
                $q->where('kode_akun', 'like', '111%')  // Bank accounts
                  ->orWhere('kode_akun', 'like', '112%') // Kas accounts
                  ->orWhere('kode_akun', 'like', '113%'); // Kas Kecil accounts
            })
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
     * Now supports pattern matching for sub-accounts
     * 
     * @param string $kodeAkun
     * @return bool
     */
    public static function isKasBankAccount($kodeAkun)
    {
        return str_starts_with($kodeAkun, '111') ||  // Bank accounts
               str_starts_with($kodeAkun, '112') ||  // Kas accounts
               str_starts_with($kodeAkun, '113') ||  // Kas Kecil accounts
               str_starts_with($kodeAkun, '118');    // Piutang accounts
    }
    
    /**
     * Get nama kategori akun (Kas/Bank/Piutang/Lainnya)
     * Now supports pattern matching for sub-accounts
     * 
     * @param string $kodeAkun
     * @return string
     */
    public static function getAccountCategory($kodeAkun)
    {
        // Kas: 112x (Kas) dan 113x (Kas Kecil)
        if (str_starts_with($kodeAkun, '112') || str_starts_with($kodeAkun, '113')) {
            return 'Kas';
        } 
        // Bank: 111x (all bank accounts)
        elseif (str_starts_with($kodeAkun, '111')) {
            return 'Bank';
        } 
        // Piutang: 118x
        elseif (str_starts_with($kodeAkun, '118')) {
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
        
        // Saldo awal dari COA (Pastikan casting ke float aman)
        $rawSaldoAwal = $coa->saldo_awal ?? 0;
        $saldoAwal = is_numeric($rawSaldoAwal) ? (float) $rawSaldoAwal : (float) str_replace(['Rp', '.', ',', ' '], ['', '', '.', ''], $rawSaldoAwal);
        
        // Hitung akumulasi dari journal lines (Sistem Baru)
        $journalQuery = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.coa_id', $coa->id);
            
        $jNew = $journalQuery->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_kredit')->first();

        // Hitung akumulasi dari jurnal_umum (Sistem Lama/Penggajian)
        $queryJurnalUmum = DB::table('jurnal_umum')
            ->where('coa_id', $coa->id);
            
        if ($userId !== null) {
            $queryJurnalUmum->where('user_id', $userId);
        }
        
        $jOld = $queryJurnalUmum->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(kredit), 0) as total_kredit')->first();
        
        // Total Mutasi Jurnal
        $totalDebitJurnal = ($jNew->total_debit ?? 0) + ($jOld->total_debit ?? 0);
        $totalKreditJurnal = ($jNew->total_kredit ?? 0) + ($jOld->total_kredit ?? 0);
        
        // Rumus saldo berdasarkan Saldo Normal (Sesuai kaidah akuntansi)
        if (strtolower($coa->saldo_normal) === 'debit') {
            // Aset / Beban: saldo_berjalan = saldo_awal + total_debit_jurnal - total_kredit_jurnal
            return $saldoAwal + $totalDebitJurnal - $totalKreditJurnal;
        } else {
            // Kewajiban / Ekuitas / Pendapatan: saldo_berjalan = saldo_awal + total_kredit_jurnal - total_debit_jurnal
            return $saldoAwal + $totalKreditJurnal - $totalDebitJurnal;
        }
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

    /**
     * Get hanya akun Bank (exclude Kas, Kas Kecil) dengan saldo
     * 
     * @param int|null $userId Optional user_id for multi-tenant filtering
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBankAccountsWithBalance($userId = null)
    {
        $bankKeywords = ['bank', 'bca', 'mandiri', 'bri', 'bni', 'bsi', 'cimb', 'danamon', 'permata', 'btn', 'bpd', 'maybank', 'mega', 'ocbc', 'panin', 'sinarmas', 'bukopin', 'jenius', 'jago', 'allo', 'uob', 'hana', 'muamalat', 'dki', 'bjb', 'jabar', 'jatim', 'jateng', 'seabank'];
        
        $query = Coa::whereIn('tipe_akun', ['Asset', 'Aset', 'ASET', 'Aktiva', 'asset'])
            ->where(function($q) use ($bankKeywords) {
                $q->where('kode_akun', 'like', '111%');
                foreach ($bankKeywords as $keyword) {
                    $q->orWhere('nama_akun', 'LIKE', '%' . $keyword . '%');
                }
            });

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        
        $accounts = $query->orderBy('kode_akun')->get();
        
        // Filter collection to exclude non-bank assets (Kas, Kas Bank, Kas Kecil)
        $filteredAccounts = $accounts->filter(function($account) {
            $nama = strtolower(trim($account->nama_akun));
            
            // Exclude Kas, Kas Bank, Kas Kecil, and strict exclusions
            if ($nama === 'kas' || $nama === 'kas bank' || $nama === 'kas kecil' || strpos($nama, 'kredit') !== false || strpos($nama, 'hutang') !== false) {
                return false;
            }
            
            return true;
        })->values(); // reset keys
        
        foreach ($filteredAccounts as $account) {
            $account->saldo = self::getCurrentBalance($account->kode_akun, $userId);
        }
        
        return $filteredAccounts;
    }
}
