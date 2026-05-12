<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportMasterData extends Command
{
    protected $signature = 'master:export {--perusahaan_id=1 : ID Perusahaan yang akan dijadikan master data}';
    protected $description = 'Export data master dari perusahaan tertentu untuk dijadikan template bagi owner baru';

    /**
     * Daftar tabel yang akan di-export sebagai master data
     * PENTING: Urutan tabel harus sesuai dengan foreign key dependencies
     */
    protected $tables = [
        // Master data global (tidak terikat perusahaan)
        'satuans',
        'jabatans',
        
        // Master data perusahaan (akan di-copy per perusahaan)
        'coas',
        'jenis_asets',
        'kategori_asets',
        'suppliers',
        'pelanggans',
        'bahan_bakus',
        'bahan_pendukungs',
        'produks',
        'bops',
        'pegawais',
        
        // Data transaksi yang mungkin ingin dijadikan contoh (opsional)
        // Uncomment jika ingin include data transaksi sebagai contoh
        // 'asets',
        // 'pembelians',
        // 'penjualans',
        // 'produksis',
    ];

    public function handle()
    {
        $perusahaanId = $this->option('perusahaan_id');
        
        $this->info("Exporting master data dari perusahaan ID: {$perusahaanId}");
        
        // Validasi perusahaan exists
        $perusahaan = DB::table('perusahaan')->find($perusahaanId);
        if (!$perusahaan) {
            $this->error("Perusahaan dengan ID {$perusahaanId} tidak ditemukan!");
            return 1;
        }
        
        $this->info("Perusahaan: {$perusahaan->nama}");
        
        $masterData = [];
        $totalRecords = 0;
        
        foreach ($this->tables as $table) {
            $this->info("Exporting table: {$table}");
            
            // Check if table exists
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                $this->warn("  Table {$table} tidak ditemukan, skip...");
                continue;
            }
            
            // Get table structure
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            
            // Check if table has perusahaan_id column
            $hasPerusahaanId = in_array('perusahaan_id', $columns);
            
            // Get data
            $query = DB::table($table);
            
            if ($hasPerusahaanId) {
                // Filter by perusahaan_id
                $query->where('perusahaan_id', $perusahaanId);
            }
            
            $data = $query->get()->toArray();
            $count = count($data);
            
            if ($count > 0) {
                // Convert to array and remove auto-increment IDs and timestamps if needed
                $cleanedData = array_map(function($row) use ($hasPerusahaanId) {
                    $row = (array) $row;
                    
                    // Remove perusahaan_id karena akan di-set ulang saat insert
                    if ($hasPerusahaanId) {
                        unset($row['perusahaan_id']);
                    }
                    
                    // Keep ID untuk maintain relationships, tapi akan di-map ulang saat insert
                    // unset($row['id']); // JANGAN hapus ID, kita butuh untuk mapping
                    
                    return $row;
                }, $data);
                
                $masterData[$table] = [
                    'has_perusahaan_id' => $hasPerusahaanId,
                    'columns' => $columns,
                    'data' => $cleanedData,
                ];
                
                $totalRecords += $count;
                $this->info("  ✓ {$count} records exported");
            } else {
                $this->warn("  ⚠ No data found");
            }
        }
        
        // Save to JSON file
        $filename = 'master_data_' . date('Y_m_d_His') . '.json';
        $path = database_path('seeders/master_data/' . $filename);
        
        // Create directory if not exists
        if (!File::exists(database_path('seeders/master_data'))) {
            File::makeDirectory(database_path('seeders/master_data'), 0755, true);
        }
        
        // Save JSON
        File::put($path, json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // Also save as "latest" for easy reference
        $latestPath = database_path('seeders/master_data/master_data_latest.json');
        File::put($latestPath, json_encode($masterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->info("\n" . str_repeat('=', 60));
        $this->info("✓ Export completed!");
        $this->info("Total tables: " . count($masterData));
        $this->info("Total records: {$totalRecords}");
        $this->info("Saved to: {$path}");
        $this->info("Latest: {$latestPath}");
        $this->info(str_repeat('=', 60));
        
        return 0;
    }
}
