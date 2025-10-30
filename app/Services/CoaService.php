<?php

namespace App\Services;

use App\Models\Coa;

class CoaService
{
    public function generateKodeAkun($tipeAkun, $kodeInduk = null)
    {
        if ($kodeInduk) {
            // Untuk akun anak (contoh: 110101, 110102 di bawah 1101)
            $lastSubAccount = Coa::where('kode_akun', 'like', $kodeInduk . '%')
                ->whereRaw('LENGTH(kode_akun) = ?', [strlen($kodeInduk) + 2])
                ->orderBy('kode_akun', 'desc')
                ->first();

            if ($lastSubAccount) {
                $lastNumber = (int) substr($lastSubAccount->kode_akun, -2);
                return $kodeInduk . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            } else {
                return $kodeInduk . '01';
            }
        } else {
            // Untuk akun utama (contoh: 1100, 1200, dst)
            $lastAccount = Coa::where('tipe_akun', $tipeAkun)
                ->whereRaw('LENGTH(kode_akun) = 4')
                ->orderBy('kode_akun', 'desc')
                ->first();

            if ($lastAccount) {
                $lastNumber = (int) substr($lastAccount->kode_akun, 1, 3);
                return $tipeAkun . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                return $tipeAkun . '001';
            }
        }
    }
}
