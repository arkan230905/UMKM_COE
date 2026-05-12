<?php

namespace App\Services;

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

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

    /**
     * Create default COA data for a new company
     */
    public function createDefaultCoaForCompany($companyId)
    {
        // Get current COA data from company_id 998 as fixed template
        $currentCoaData = Coa::withoutGlobalScopes()
            ->where('company_id', 998)
            ->orderBy('kode_akun')
            ->get();
            
        $coaData = [];
        
        foreach ($currentCoaData as $coa) {
            $coaData[] = [
                'kode_akun' => $coa->kode_akun,
                'nama_akun' => $coa->nama_akun,
                'tipe_akun' => $coa->tipe_akun,
                'saldo_awal' => 0, // Set all to 0 for new registrations (empty balance)
            ];
        }

        // Create COA accounts with company-specific prefixed codes
        $createdCount = 0;
        
        foreach ($coaData as $coa) {
            // Create company-specific prefixed kode_akun to avoid unique constraint
            $prefixedKode = $companyId . '_' . $coa['kode_akun'];
            
            // Check if account already exists for this company
            $existingCoa = Coa::withoutGlobalScopes()
                ->where('kode_akun', $prefixedKode)
                ->where('company_id', $companyId)
                ->first();
            
            if (!$existingCoa) {
                // Create new account with prefixed code
                try {
                    $newCoa = new Coa();
                    $newCoa->kode_akun = $prefixedKode; // Use prefixed code
                    $newCoa->nama_akun = $coa['nama_akun'];
                    $newCoa->tipe_akun = $coa['tipe_akun'];
                    $newCoa->kategori_akun = $coa['tipe_akun'];
                    $newCoa->saldo_awal = 0;
                    $newCoa->tanggal_saldo_awal = now();
                    $newCoa->posted_saldo_awal = false;
                    $newCoa->company_id = $companyId;
                    $newCoa->save();
                    $createdCount++;
                } catch (\Illuminate\Database\QueryException $e) {
                    // Skip if unique constraint violation
                    continue;
                }
            } else {
                // Update existing account
                $existingCoa->update([
                    'nama_akun' => $coa['nama_akun'],
                    'tipe_akun' => $coa['tipe_akun'],
                    'kategori_akun' => $coa['tipe_akun'],
                    'saldo_awal' => 0,
                    'tanggal_saldo_awal' => now(),
                    'posted_saldo_awal' => false,
                ]);
                $createdCount++;
            }
        }
        
        return $createdCount;
    }
}
