<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\Produk;
use Illuminate\Support\Facades\Log;

class AutoCoaService
{
    /**
     * Get or create COA for persediaan barang jadi based on product name
     */
    public static function getOrCreatePersediaanBarangJadiCoa($productName, $productId = null)
    {
        // First, try to get existing COA from product
        if ($productId) {
            $produk = Produk::find($productId);
            if ($produk && $produk->coa_persediaan_id) {
                $coa = Coa::find($produk->coa_persediaan_id);
                if ($coa) {
                    Log::info("Using existing COA for product: {$productName} -> {$coa->kode_akun}");
                    return $coa;
                }
            }
        }
        
        // Try to find existing COA by name
        $coaName = "Pers. Barang Jadi {$productName}";
        $existingCoa = Coa::where('nama_akun', $coaName)->first();
        
        if ($existingCoa) {
            Log::info("Found existing COA by name: {$coaName} -> {$existingCoa->kode_akun}");
            
            // Update product with this COA if we have product ID
            if ($productId) {
                $produk = Produk::find($productId);
                if ($produk) {
                    $produk->coa_persediaan_id = $existingCoa->id;
                    $produk->save();
                    Log::info("Updated product {$productId} with COA {$existingCoa->id}");
                }
            }
            
            return $existingCoa;
        }
        
        // Create new COA
        $newCoa = self::createPersediaanBarangJadiCoa($productName, $productId);
        
        Log::info("Created new COA for product: {$productName} -> {$newCoa->kode_akun}");
        
        return $newCoa;
    }
    
    /**
     * Create new COA for persediaan barang jadi
     */
    private static function createPersediaanBarangJadiCoa($productName, $productId = null)
    {
        // Get the next available COA code
        $nextCode = self::getNextCoaCode('116');
        
        $coaName = "Pers. Barang Jadi {$productName}";
        
        $newCoa = Coa::create([
            'kode_akun' => $nextCode,
            'nama_akun' => $coaName,
            'tipe_akun' => 'Asset',
            'saldo_normal' => 'Debit',
            'kategori_akun' => 'Persediaan Barang Jadi',
            'status' => 'aktif',
            'created_by' => 1,
        ]);
        
        // Update product with new COA
        if ($productId) {
            $produk = Produk::find($productId);
            if ($produk) {
                $produk->coa_persediaan_id = $newCoa->id;
                $produk->save();
                Log::info("Updated product {$productId} with new COA {$newCoa->id}");
            }
        }
        
        return $newCoa;
    }
    
    /**
     * Get next available COA code for persediaan barang jadi (116x series)
     */
    private static function getNextCoaCode($baseCode)
    {
        // Find existing COA codes that start with 116
        $existingCodes = Coa::where('kode_akun', 'like', '116%')
            ->pluck('kode_akun')
            ->toArray();
        
        // Extract numeric parts and find the highest
        $maxNumber = 0;
        foreach ($existingCodes as $code) {
            $numericPart = (int)substr($code, 3);
            if ($numericPart > $maxNumber) {
                $maxNumber = $numericPart;
            }
        }
        
        // Generate next code
        $nextNumber = $maxNumber + 1;
        $nextCode = '116' . str_pad($nextNumber, 1, '0', STR_PAD_LEFT);
        
        // If it's just 1160, make it 1161
        if ($nextNumber == 0) {
            $nextCode = '1161';
        }
        
        return $nextCode;
    }
    
    /**
     * Get or create COA for any account type based on name and category
     */
    public static function getOrCreateCoa($namaAkun, $kategoriAkun = 'Lainnya', $tipeAkun = 'Asset', $saldoNormal = 'Debit')
    {
        // Try to find existing COA
        $existingCoa = Coa::where('nama_akun', $namaAkun)->first();
        
        if ($existingCoa) {
            Log::info("Found existing COA: {$namaAkun} -> {$existingCoa->kode_akun}");
            return $existingCoa;
        }
        
        // Generate new COA code
        $nextCode = self::generateCoaCode($tipeAkun, $kategoriAkun);
        
        // Create new COA
        $newCoa = Coa::create([
            'kode_akun' => $nextCode,
            'nama_akun' => $namaAkun,
            'tipe_akun' => $tipeAkun,
            'saldo_normal' => $saldoNormal,
            'kategori_akun' => $kategoriAkun,
            'status' => 'aktif',
            'created_by' => 1,
        ]);
        
        Log::info("Created new COA: {$namaAkun} -> {$nextCode}");
        
        return $newCoa;
    }
    
    /**
     * Generate COA code based on account type and category
     */
    private static function generateCoaCode($tipeAkun, $kategoriAkun)
    {
        // Define base codes for different account types
        $baseCodes = [
            'Asset' => [
                'Persediaan Barang Jadi' => '116',
                'Persediaan Barang dalam Proses' => '117',
                'Persediaan Bahan Baku' => '114',
                'Persediaan Bahan Pendukung' => '115',
                'Kas' => '11',
                'Bank' => '11',
                'Piutang' => '118',
                'Peralatan' => '119',
                'default' => '199'
            ],
            'Liability' => [
                'Hutang Usaha' => '21',
                'Hutang Bank' => '22',
                'default' => '299'
            ],
            'Equity' => [
                'Modal' => '31',
                'default' => '399'
            ],
            'Revenue' => [
                'Penjualan' => '41',
                'default' => '499'
            ],
            'Expense' => [
                'Beban Operasional' => '55',
                'HPP' => '51',
                'default' => '599'
            ]
        ];
        
        $baseCode = $baseCodes[$tipeAkun][$kategoriAkun] ?? $baseCodes[$tipeAkun]['default'] ?? '999';
        
        // Find existing codes with this base
        $existingCodes = Coa::where('kode_akun', 'like', $baseCode . '%')
            ->pluck('kode_akun')
            ->toArray();
        
        // Generate next number
        $maxNumber = 0;
        foreach ($existingCodes as $code) {
            $numericPart = (int)substr($code, strlen($baseCode));
            if ($numericPart > $maxNumber) {
                $maxNumber = $numericPart;
            }
        }
        
        $nextNumber = $maxNumber + 1;
        
        // For single digit base codes, add padding
        if (strlen($baseCode) == 2) {
            return $baseCode . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } else {
            return $baseCode . $nextNumber;
        }
    }
}
