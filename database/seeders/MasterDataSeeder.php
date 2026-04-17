<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MasterDataSeeder extends Seeder
{
    /**
     * Seed master data untuk perusahaan baru
     * Data ini adalah copy dari data master yang sudah di-export
     */
    public function run(?int $perusahaanId = null): void
    {
        // Load master data JSON
        $masterDataPath = database_path('seeders/master_data/master_data_latest.json');
        
        if (!File::exists($masterDataPath)) {
            Log::warning('Master data file not found. Please run: php artisan master:export');
            if ($this->command) {
                $this->command->warn('⚠ Master data file not found!');
                $this->command->warn('Please run: php artisan master:export');
            }
            return;
        }
        
        $masterData = json_decode(File::get($masterDataPath), true);
        
        if (!$masterData) {
            Log::error('Failed to parse master data JSON');
            if ($this->command) {
                $this->command->error('Failed to parse master data JSON');
            }
            return;
        }
        
        if ($this->command) {
            $this->command->info('Seeding master data...');
        }
        
        Log::info('Starting master data seeding', [
            'perusahaan_id' => $perusahaanId,
            'tables' => count($masterData),
        ]);
        
        // ID mapping untuk maintain relationships
        $idMapping = [];
        
        foreach ($masterData as $table => $tableData) {
            if ($this->command) {
                $this->command->info("Seeding table: {$table}");
            }
            
            Log::info("Seeding table: {$table}");
            
            $hasPerusahaanId = $tableData['has_perusahaan_id'];
            $data = $tableData['data'];
            
            if (empty($data)) {
                if ($this->command) {
                    $this->command->warn("  No data for {$table}");
                }
                Log::warning("No data for table: {$table}");
                continue;
            }
            
            // Initialize ID mapping for this table
            $idMapping[$table] = [];
            
            $insertedCount = 0;
            
            foreach ($data as $row) {
                $oldId = $row['id'] ?? null;
                
                // Add perusahaan_id if table has it
                if ($hasPerusahaanId && $perusahaanId) {
                    $row['perusahaan_id'] = $perusahaanId;
                }
                
                // Update foreign keys based on ID mapping
                $row = $this->updateForeignKeys($row, $idMapping, $table);
                
                // Remove id to let database auto-increment
                unset($row['id']);
                
                try {
                    // Insert and get new ID
                    $newId = DB::table($table)->insertGetId($row);
                    
                    // Store ID mapping
                    if ($oldId) {
                        $idMapping[$table][$oldId] = $newId;
                    }
                    
                    $insertedCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to insert into {$table}", [
                        'error' => $e->getMessage(),
                        'row' => $row,
                    ]);
                    if ($this->command) {
                        $this->command->error("  Error inserting into {$table}: " . $e->getMessage());
                    }
                }
            }
            
            if ($this->command) {
                $this->command->info("  ✓ {$insertedCount} records inserted");
            }
            
            Log::info("Table seeded: {$table}", [
                'inserted' => $insertedCount,
            ]);
        }
        
        if ($this->command) {
            $this->command->info('✓ Master data seeding completed!');
        }
        
        Log::info('Master data seeding completed', [
            'perusahaan_id' => $perusahaanId,
        ]);
    }
    
    /**
     * Update foreign keys in row based on ID mapping
     */
    protected function updateForeignKeys(array $row, array $idMapping, string $currentTable): array
    {
        // Common foreign key patterns
        $foreignKeyPatterns = [
            'coa_id' => 'coa',
            'parent_id' => $currentTable, // Self-referencing
            'jenis_aset_id' => 'jenis_aset',
            'kategori_aset_id' => 'kategori_aset',
            'supplier_id' => 'supplier',
            'pelanggan_id' => 'pelanggan',
            'bahan_baku_id' => 'bahan_baku',
            'bahan_pendukung_id' => 'bahan_pendukung',
            'produk_id' => 'produk',
            'pegawai_id' => 'pegawai',
            'jabatan_id' => 'jabatan',
            'satuan_id' => 'satuan',
            'bop_id' => 'bop',
        ];
        
        foreach ($foreignKeyPatterns as $fkColumn => $fkTable) {
            if (isset($row[$fkColumn]) && $row[$fkColumn]) {
                $oldFkId = $row[$fkColumn];
                
                // Check if we have mapping for this foreign key
                if (isset($idMapping[$fkTable][$oldFkId])) {
                    $row[$fkColumn] = $idMapping[$fkTable][$oldFkId];
                }
            }
        }
        
        return $row;
    }
    
    /**
     * Static method untuk dipanggil dari listener dengan perusahaan_id
     */
    public static function seedForCompany(int $perusahaanId): void
    {
        $seeder = new self();
        $seeder->run($perusahaanId);
    }
}
