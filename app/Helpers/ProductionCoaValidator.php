<?php

namespace App\Helpers;

use App\Models\Coa;
use Illuminate\Support\Facades\Log;

class ProductionCoaValidator
{
    /**
     * Validate that all required COAs exist for production
     * 
     * @param int $userId
     * @return array ['valid' => bool, 'missing' => array, 'message' => string]
     */
    public static function validateRequiredCoas($userId)
    {
        $requiredCoas = [
            '1171' => 'Pers. Barang Dalam Proses - BBB (WIP BBB)',
            '1172' => 'Pers. Barang Dalam Proses - BTKL (WIP BTKL)',
            '1173' => 'Pers. Barang Dalam Proses - BOP (WIP BOP)',
            '211' => 'Hutang Gaji',
        ];
        
        $missingCoas = [];
        
        foreach ($requiredCoas as $kode => $nama) {
            $exists = Coa::where('kode_akun', $kode)
                ->where('user_id', $userId)
                ->exists();
            
            if (!$exists) {
                $missingCoas[$kode] = $nama;
            }
        }
        
        if (empty($missingCoas)) {
            return [
                'valid' => true,
                'missing' => [],
                'message' => 'All required COAs exist'
            ];
        }
        
        $missingList = [];
        foreach ($missingCoas as $kode => $nama) {
            $missingList[] = "{$kode} - {$nama}";
        }
        
        $message = "COA yang diperlukan untuk produksi tidak ditemukan:\n" . 
                   implode("\n", $missingList) . "\n\n" .
                   "Silakan buat COA ini terlebih dahulu di Master Data > Chart of Accounts, " .
                   "atau jalankan seeder: php artisan db:seed --class=RequiredProductionCoasSeeder";
        
        Log::error('Missing required production COAs', [
            'user_id' => $userId,
            'missing_coas' => $missingCoas
        ]);
        
        return [
            'valid' => false,
            'missing' => $missingCoas,
            'message' => $message
        ];
    }
    
    /**
     * Validate BOP component COAs exist
     * 
     * @param array $bopKomponen Array of BOP components with 'coa_kode'
     * @param int $userId
     * @return array ['valid' => bool, 'missing' => array, 'message' => string]
     */
    public static function validateBopCoas($bopKomponen, $userId)
    {
        $missingCoas = [];
        
        foreach ($bopKomponen as $komponen) {
            if (empty($komponen['coa_kode'])) {
                continue;
            }
            
            $exists = Coa::where('kode_akun', $komponen['coa_kode'])
                ->where('user_id', $userId)
                ->exists();
            
            if (!$exists) {
                $missingCoas[$komponen['coa_kode']] = $komponen['nama_komponen'] ?? 'Unknown';
            }
        }
        
        if (empty($missingCoas)) {
            return [
                'valid' => true,
                'missing' => [],
                'message' => 'All BOP COAs exist'
            ];
        }
        
        $missingList = [];
        foreach ($missingCoas as $kode => $nama) {
            $missingList[] = "{$kode} - BOP {$nama}";
        }
        
        $message = "COA untuk komponen BOP tidak ditemukan:\n" . 
                   implode("\n", $missingList) . "\n\n" .
                   "Silakan buat COA ini terlebih dahulu di Master Data > Chart of Accounts.";
        
        Log::error('Missing BOP COAs', [
            'user_id' => $userId,
            'missing_coas' => $missingCoas
        ]);
        
        return [
            'valid' => false,
            'missing' => $missingCoas,
            'message' => $message
        ];
    }
    
    /**
     * Validate all COAs needed for a production
     * 
     * @param array $hppData HPP data with bop_komponen
     * @param int $userId
     * @throws \Exception if validation fails
     */
    public static function validateOrThrow($hppData, $userId)
    {
        // Validate required COAs
        $requiredValidation = self::validateRequiredCoas($userId);
        if (!$requiredValidation['valid']) {
            throw new \Exception($requiredValidation['message']);
        }
        
        // Validate BOP COAs if BOP components exist
        if (!empty($hppData['bop_komponen'])) {
            $bopValidation = self::validateBopCoas($hppData['bop_komponen'], $userId);
            if (!$bopValidation['valid']) {
                throw new \Exception($bopValidation['message']);
            }
        }
    }
}
