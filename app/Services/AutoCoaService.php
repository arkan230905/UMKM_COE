<?php

namespace App\Services;

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class AutoCoaService
{
    /**
     * Create COA for Bahan Baku automatically
     * 
     * @param string $namaBahan
     * @param int $userId
     * @return Coa
     */
    public function createCoaForBahanBaku(string $namaBahan, int $userId): Coa
    {
        // Get the highest COA code for "Pers. Bahan Baku" category
        // Pattern: starts with "114" (Persediaan Bahan Baku category)
        // Must stay within 114xx range (1140-1149, 11400-11499, etc.)
        $lastCoa = Coa::where('user_id', $userId)
            ->where('kode_akun', 'LIKE', '114%')
            ->where('nama_akun', 'LIKE', 'Pers. Bahan Baku%')
            ->orderBy('kode_akun', 'desc')
            ->lockForUpdate() // Prevent race condition
            ->first();

        // Generate next code within the 114 group
        if ($lastCoa) {
            $lastCode = $lastCoa->kode_akun;
            
            // Check if last code is 1149 or 11499, etc. (about to reach next group)
            if (strlen($lastCode) == 4 && $lastCode == '1149') {
                // Move to 5-digit: 11400
                $nextCode = '11400';
            } else {
                // Simply increment
                $nextCode = strval(intval($lastCode) + 1);
                
                // Validate that we're still in 114 group
                if (!str_starts_with($nextCode, '114')) {
                    throw new \Exception('Kode akun Pers. Bahan Baku sudah penuh. Hubungi administrator.');
                }
            }
        } else {
            // Default starting code for Pers. Bahan Baku
            $nextCode = '1141';
        }

        // Create COA
        $coa = Coa::create([
            'kode_akun' => $nextCode,
            'nama_akun' => 'Pers. Bahan Baku ' . $namaBahan,
            'kategori_akun' => 'Aset Lancar',
            'tipe_akun' => 'Aset',
            'saldo_normal' => 'Debit',
            'keterangan' => 'Auto-created for Bahan Baku: ' . $namaBahan,
            'saldo_awal' => 0,
            'tanggal_saldo_awal' => now(),
            'posted_saldo_awal' => false,
            'user_id' => $userId,
        ]);

        return $coa;
    }

    /**
     * Create COA for Bahan Pendukung automatically
     * 
     * @param string $namaBahan
     * @param int $userId
     * @return Coa
     */
    public function createCoaForBahanPendukung(string $namaBahan, int $userId): Coa
    {
        // Get the highest COA code for "Pers. Bahan Pendukung" category
        // Pattern: starts with "115" (Persediaan Bahan Pendukung category)
        // Must stay within 115xx range (1150-1159, 11500-11599, etc.)
        $lastCoa = Coa::where('user_id', $userId)
            ->where('kode_akun', 'LIKE', '115%')
            ->where('nama_akun', 'LIKE', 'Pers. Bahan Pendukung%')
            ->orderBy('kode_akun', 'desc')
            ->lockForUpdate() // Prevent race condition
            ->first();

        // Generate next code within the 115 group
        if ($lastCoa) {
            $lastCode = $lastCoa->kode_akun;
            
            // Check if last code is 1159 or 11599, etc. (about to reach next group)
            if (strlen($lastCode) == 4 && $lastCode == '1159') {
                // Move to 5-digit: 11500
                $nextCode = '11500';
            } else {
                // Simply increment
                $nextCode = strval(intval($lastCode) + 1);
                
                // Validate that we're still in 115 group
                if (!str_starts_with($nextCode, '115')) {
                    throw new \Exception('Kode akun Pers. Bahan Pendukung sudah penuh. Hubungi administrator.');
                }
            }
        } else {
            // Default starting code for Pers. Bahan Pendukung
            $nextCode = '1151';
        }

        // Create COA
        $coa = Coa::create([
            'kode_akun' => $nextCode,
            'nama_akun' => 'Pers. Bahan Pendukung ' . $namaBahan,
            'kategori_akun' => 'Aset Lancar',
            'tipe_akun' => 'Aset',
            'saldo_normal' => 'Debit',
            'keterangan' => 'Auto-created for Bahan Pendukung: ' . $namaBahan,
            'saldo_awal' => 0,
            'tanggal_saldo_awal' => now(),
            'posted_saldo_awal' => false,
            'user_id' => $userId,
        ]);

        return $coa;
    }
}
