<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixForeignKeyConstraintSeeder extends Seeder
{
    /**
     * Fix foreign key constraint saat menghapus COA
     * Hapus referensi ke COA lama sebelum sync
     */
    public function run(): void
    {
        Log::info('Starting FixForeignKeyConstraintSeeder');
        
        $user_id = 7;
        
        // Cek semua tabel yang mungkin memiliki foreign key ke coas
        $tablesToCheck = [
            'produksis' => ['coa_persediaan_barang_jadi_id'],
            'bahan_bakus' => ['coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id'],
            'bahan_pendukungs' => ['coa_persediaan_id', 'coa_hpp_id', 'coa_pembelian_id'],
            'pembayaran_beban' => ['akun_kas_id', 'akun_beban_id'],
            'jurnal_umum' => ['coa_id'],
        ];
        
        $coaIdsToDelete = [];
        
        // Get semua COA untuk user ini
        $userCOAs = DB::table('accounts')
            ->where('user_id', $user_id)
            ->get(['id', 'kode_akun', 'nama_akun']);
        
        Log::info("Current COAs for user {$user_id}:");
        foreach ($userCOAs as $coa) {
            Log::info("  - ID: {$coa->id}, Kode: {$coa->kode_akun}, Nama: {$coa->nama_akun}");
        }
        
        // Cek referensi untuk setiap tabel
        foreach ($tablesToCheck as $tableName => $columns) {
            Log::info("Checking table: {$tableName}");
            
            foreach ($columns as $column) {
                $references = DB::table($tableName)
                    ->where('user_id', $user_id)
                    ->whereNotNull($column)
                    ->distinct()
                    ->pluck($column)
                    ->toArray();
                
                if (!empty($references)) {
                    Log::info("  - Found references in {$tableName}.{$column}: " . implode(', ', $references));
                    
                    // Update referensi ke NULL jika COA tidak ada di DefaultCoaSeeder
                    $defaultCOACodes = [
                        '11', '111', '112', '113', '114', '1141', '115', '1151', '1152', '1153',
                        '116', '1161', '117', '1171', '1172', '1173', '118', '119', '120', '125', '126', '127',
                        '21', '210', '211', '212',
                        '31', '310', '311',
                        '41', '410', '42',
                        '51', '510', '513', '514', '515', '516',
                        '52', '520',
                        '53', '531', '533', '532',
                        '54', '55', '550', '551', '552', '553',
                        '56', '590', '591', '592', '593', '594', '536'
                    ];
                    
                    // Update referensi yang tidak ada di DefaultCoaSeeder ke NULL
                    $updatedCount = DB::table($tableName)
                        ->where('user_id', $user_id)
                        ->whereIn($column, function($query) use ($references, $defaultCOACodes) {
                            $query->select('id')
                                ->from('coas')
                                ->where('user_id', $user_id)
                                ->whereNotIn('kode_akun', $defaultCOACodes)
                                ->whereIn('id', $references);
                        })
                        ->update([$column => null]);
                    
                    if ($updatedCount > 0) {
                        Log::info("    Updated {$updatedCount} records in {$tableName}.{$column} to NULL");
                    }
                }
            }
        }
        
        // Sekarang coba hapus semua COA user ini
        try {
            $deletedCount = DB::table('accounts')
                ->where('user_id', $user_id)
                ->delete();
            
            Log::info("Successfully deleted {$deletedCount} COA records for user {$user_id}");
        } catch (\Exception $e) {
            Log::info("Error deleting COAs: " . $e->getMessage());
            
            // Jika masih error, coba hapus satu per satu
            $coaIds = DB::table('accounts')
                ->where('user_id', $user_id)
                ->pluck('id')
                ->toArray();
            
            foreach ($coaIds as $coaId) {
                try {
                    DB::table('accounts')
                        ->where('id', $coaId)
                        ->delete();
                    Log::info("Deleted COA ID: {$coaId}");
                } catch (\Exception $e) {
                    Log::info("Failed to delete COA ID {$coaId}: " . $e->getMessage());
                }
            }
        }
        
        $this->command->info('Foreign key constraint fix completed!');
    }
}
