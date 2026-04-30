<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing COA accounts for HPP functionality
        
        // COA 56 - Harga Pokok Penjualan
        $this->createCoaIfNotExists([
            'kode_akun' => '56',
            'nama_akun' => 'Harga Pokok Penjualan',
            'tipe_akun' => 'Biaya',
            'kategori_akun' => 'Biaya',
            'saldo_awal' => 0,
            'saldo_normal' => 'Debit',
            // 'is_akun_header' => false, // Removed - column doesn't exist
            'user_id' => 1, // Will be updated for each company
            'company_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // COA 552 - HPP (general)
        $this->createCoaIfNotExists([
            'kode_akun' => '552',
            'nama_akun' => 'HPP',
            'tipe_akun' => 'Biaya',
            'kategori_akun' => 'Biaya',
            'saldo_awal' => 0,
            'saldo_normal' => 'Debit',
            // 'is_akun_header' => false, // Removed - column doesn't exist
            'user_id' => 1,
            'company_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create for other companies too
        $companies = DB::table('perusahaan')->get();
        foreach ($companies as $company) {
            if ($company->id != 1) { // Skip company 1 as it's already created
                // COA 56 - Harga Pokok Penjualan
                $this->createCoaIfNotExists([
                    'kode_akun' => '56',
                    'nama_akun' => 'Harga Pokok Penjualan',
                    'tipe_akun' => 'Biaya',
                    'kategori_akun' => 'Biaya',
                    'saldo_awal' => 0,
                    'saldo_normal' => 'Debit',
                    // 'is_akun_header' => false, // Removed - column doesn't exist
                    'user_id' => $company->id,
                    'company_id' => $company->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $company->id);
                
                // COA 552 - HPP (general)
                $this->createCoaIfNotExists([
                    'kode_akun' => '552',
                    'nama_akun' => 'HPP',
                    'tipe_akun' => 'Biaya',
                    'kategori_akun' => 'Biaya',
                    'saldo_awal' => 0,
                    'saldo_normal' => 'Debit',
                    // 'is_akun_header' => false, // Removed - column doesn't exist
                    'user_id' => $company->id,
                    'company_id' => $company->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $company->id);
            }
        }
        
        echo "Added missing HPP COA accounts for all companies\n";
    }
    
    private function createCoaIfNotExists($coaData, $companyId = null)
    {
        $existing = DB::table('coas')
            ->where('kode_akun', $coaData['kode_akun'])
            ->where('company_id', $coaData['company_id'])
            ->first();
            
        if (!$existing) {
            DB::table('coas')->insert($coaData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the created COA accounts
        DB::table('coas')
            ->whereIn('kode_akun', ['56', '552'])
            ->where('nama_akun', 'Harga Pokok Penjualan')
            ->orWhere('nama_akun', 'HPP')
            ->delete();
    }
};
