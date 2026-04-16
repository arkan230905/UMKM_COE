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

    /**
     * Create default COA data for a new company
     */
    public function createDefaultCoaForCompany($companyId)
    {
        // Get current COA data from database as fixed template
        $currentCoaData = Coa::withoutGlobalScopes()
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

        foreach ($coaData as $coa) {
            // Check if COA already exists for this company
            $existingCoa = Coa::withoutGlobalScopes()
                ->where('kode_akun', $coa['kode_akun'])
                ->where('company_id', $companyId)
                ->first();
                
            if ($existingCoa) {
                // Update existing record
                $existingCoa->update([
                    'nama_akun' => $coa['nama_akun'],
                    'tipe_akun' => $coa['tipe_akun'],
                    'kategori_akun' => $coa['tipe_akun'],
                    'saldo_awal' => 0,
                    'tanggal_saldo_awal' => now(),
                    'posted_saldo_awal' => false,
                ]);
            } else {
                // Check if kode_akun exists globally without company_id
                $globalCoa = Coa::withoutGlobalScopes()
                    ->where('kode_akun', $coa['kode_akun'])
                    ->whereNull('company_id')
                    ->first();
                    
                if ($globalCoa) {
                    // Create new COA for this company
                    Coa::withoutGlobalScopes()->create([
                        'kode_akun' => $coa['kode_akun'],
                        'nama_akun' => $coa['nama_akun'],
                        'tipe_akun' => $coa['tipe_akun'],
                        'kategori_akun' => $coa['tipe_akun'],
                        'saldo_awal' => 0,
                        'tanggal_saldo_awal' => now(),
                        'posted_saldo_awal' => false,
                        'company_id' => $companyId,
                    ]);
                }
            }
        }

        return count($coaData);
    }
}
