<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckBahanPendukungSeeder extends Seeder
{
    /**
     * Check bahan_pendukungs table for COA 530 usage
     * Find which table is causing the deletion error
     */
    public function run(): void
    {
        Log::info('Starting CheckBahanPendukungSeeder');
        
        $user_id = 7;
        $coaKode = '530';
        
        // Get COA ID for kode 530
        $coa = DB::table('accounts')
            ->where('kode_akun', $coaKode)
            ->where('user_id', $user_id)
            ->first();
        
        if (!$coa) {
            Log::info("COA {$coaKode} not found for user {$user_id}");
            return;
        }
        
        $coaId = $coa->id;
        
        Log::info("Checking COA {$coaKode} (ID: {$coaId}) usage in all tables");
        
        // Check all possible tables that might use COA 530
        $tables = [
            'bahan_bakus' => ['coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id'],
            'bahan_pendukungs' => ['coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id'],
            'jurnal_umum' => ['coa_id'],
            'pembayaran_beban' => ['akun_kas_id', 'akun_beban_id'],
        ];
        
        foreach ($tables as $tableName => $columns) {
            Log::info("Checking table: {$tableName}");
            
            foreach ($columns as $column) {
                $count = DB::table($tableName)
                    ->where($column, $coaKode)
                    ->where('user_id', $user_id)
                    ->count();
                
                if ($count > 0) {
                    Log::info("  - Found {$count} records in {$tableName}.{$column} using COA {$coaKode}");
                    
                    // Show sample records
                    $samples = DB::table($tableName)
                        ->where($column, $coaKode)
                        ->where('user_id', $user_id)
                        ->limit(3)
                        ->get();
                    
                    foreach ($samples as $sample) {
                        Log::info("    Sample: ID " . ($sample->id ?? 'N/A') . " - " . ($sample->nama ?? $sample->keterangan ?? 'N/A'));
                    }
                } else {
                    Log::info("  - No records in {$tableName}.{$column} using COA {$coaKode}");
                }
            }
        }
        
        // Also check by COA ID (not kode)
        Log::info("Checking by COA ID ({$coaId}):");
        
        $tablesById = [
            'bahan_bakus' => ['coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id'],
            'bahan_pendukungs' => ['coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id'],
            'jurnal_umum' => ['coa_id'],
            'pembayaran_beban' => ['akun_kas_id', 'akun_beban_id'],
        ];
        
        foreach ($tablesById as $tableName => $columns) {
            Log::info("Checking table: {$tableName} by COA ID");
            
            foreach ($columns as $column) {
                $count = DB::table($tableName)
                    ->where($column, $coaId)
                    ->where('user_id', $user_id)
                    ->count();
                
                if ($count > 0) {
                    Log::info("  - Found {$count} records in {$tableName}.{$column} using COA ID {$coaId}");
                }
            }
        }
        
        $this->command->info('Bahan pendukung check completed!');
    }
}
