<?php

namespace App\Helpers;

use App\Models\Coa;

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
}
