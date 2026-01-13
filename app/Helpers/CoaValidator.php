<?php

namespace App\Helpers;

use App\Models\Coa;
use App\Models\Account;
use Exception;

class CoaValidator
{
    /**
     * Cache untuk COA yang sudah dibaca
     */
    private static $coaCache = null;
    
    /**
     * Baca COA secara berkala dan cache hasilnya
     */
    public static function loadCoa()
    {
        if (self::$coaCache === null) {
            self::$coaCache = Coa::orderBy('kode_akun')->get()->keyBy('kode_akun');
        }
        return self::$coaCache;
    }
    
    /**
     * Validasi akun yang diperlukan untuk jurnal pembelian
     */
    public static function validatePurchaseAccounts()
    {
        $coa = self::loadCoa();
        $requiredAccounts = [
            '101' => 'Kas',
            '102' => 'Persediaan Bahan Baku',
            '1105' => 'Persediaan Bahan Pendukung'
        ];
        
        $missing = [];
        foreach ($requiredAccounts as $code => $name) {
            if (!$coa->has($code)) {
                $missing[] = "Akun {$code} - {$name} belum ada di COA";
            }
        }
        
        if (!empty($missing)) {
            throw new Exception("COA tidak lengkap untuk jurnal pembelian:\n" . implode("\n", $missing));
        }
        
        // Pastikan akun juga ada di tabel accounts dengan nama yang benar
        self::syncAccountsToTable($requiredAccounts);
        
        // Perbaiki akun lama yang salah (1101, 1104) jika ada
        self::fixOldAccounts();
        
        return true;
    }
    
    /**
     * Perbaiki akun lama yang salah (1101, 1104) jika ada
     */
    private static function fixOldAccounts()
    {
        $coa = self::loadCoa();
        
        // Hapus akun 1101 jika ada dan ganti dengan 101
        $oldKas = Account::where('code', '1101')->first();
        if ($oldKas && $coa->has('101')) {
            // Cek apakah 101 sudah ada
            $newKas = Account::where('code', '101')->first();
            if ($newKas) {
                // Jika 101 sudah ada, hapus 1101
                $oldKas->delete();
            } else {
                // Jika 101 belum ada, ubah 1101 menjadi 101
                $oldKas->code = '101';
                $oldKas->name = $coa->get('101')->nama_akun;
                $oldKas->save();
            }
        }
        
        // Hapus akun 1104 jika ada dan ganti dengan 102
        $oldPersediaan = Account::where('code', '1104')->first();
        if ($oldPersediaan && $coa->has('102')) {
            // Cek apakah 102 sudah ada
            $newPersediaan = Account::where('code', '102')->first();
            if ($newPersediaan) {
                // Jika 102 sudah ada, hapus 1104
                $oldPersediaan->delete();
            } else {
                // Jika 102 belum ada, ubah 1104 menjadi 102
                $oldPersediaan->code = '102';
                $oldPersediaan->name = $coa->get('102')->nama_akun;
                $oldPersediaan->save();
            }
        }
    }
    
    /**
     * Validasi akun yang diperlukan untuk jurnal retur pembelian
     */
    public static function validatePurchaseReturnAccounts()
    {
        return self::validatePurchaseAccounts(); // Sama dengan pembelian
    }
    
    /**
     * Sinkronkan COA ke tabel accounts untuk memastikan nama akun benar
     */
    private static function syncAccountsToTable($requiredAccounts)
    {
        $coa = self::loadCoa();
        
        foreach ($requiredAccounts as $code => $expectedName) {
            $coaAccount = $coa->get($code);
            if (!$coaAccount) continue;
            
            // Cari atau buat account di tabel accounts
            $account = Account::where('code', $code)->first();
            
            if (!$account) {
                // Buat baru dengan nama dari COA
                $type = self::mapCoaTypeToAccountType($coaAccount->tipe_akun ?? '');
                Account::create([
                    'code' => $code,
                    'name' => $coaAccount->nama_akun,
                    'type' => $type,
                ]);
            } else {
                // Update nama jika tidak sesuai dengan COA
                if ($account->name !== $coaAccount->nama_akun) {
                    $account->name = $coaAccount->nama_akun;
                    $account->save();
                }
            }
        }
    }
    
    /**
     * Mendapatkan kode akun untuk tipe tertentu
     */
    public static function getAccountCode($type)
    {
        $coa = self::loadCoa();
        
        $accountMap = [
            'kas' => '101',
            'persediaan_bahan_baku' => '102',
            'persediaan_bahan_pendukung' => '1105',
        ];
        
        $code = $accountMap[$type] ?? null;
        
        if (!$code || !$coa->has($code)) {
            throw new Exception("Akun untuk tipe '{$type}' tidak ditemukan di COA");
        }
        
        return $code;
    }
    
    /**
     * Mendapatkan nama akun dari kode
     */
    public static function getAccountName($code)
    {
        $coa = self::loadCoa();
        $account = $coa->get($code);
        
        if (!$account) {
            throw new Exception("Akun dengan kode '{$code}' tidak ditemukan di COA");
        }
        
        return $account->nama_akun;
    }
    
    /**
     * Mapping tipe akun COA ke tipe account
     */
    private static function mapCoaTypeToAccountType($tipeAkun)
    {
        $t = strtolower(trim($tipeAkun));
        return match ($t) {
            'asset', 'assets', 'aktiva' => 'asset',
            'liability', 'liabilities', 'utang', 'kewajiban' => 'liability',
            'equity', 'modal' => 'equity',
            'revenue', 'pendapatan' => 'revenue',
            'expense', 'expenses', 'beban' => 'expense',
            default => 'asset', // fallback
        };
    }
    
    /**
     * Clear cache (untuk testing atau refresh)
     */
    public static function clearCache()
    {
        self::$coaCache = null;
    }
}
