<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateMasterData extends Command
{
    protected $signature = 'master:clean-duplicates {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Bersihkan data duplikat dari tabel master';

    /**
     * Tabel dan kolom unique identifier untuk deteksi duplikat
     */
    protected $tables = [
        'satuans' => ['kode', 'nama'],  // Duplikat jika kode DAN nama sama
        'bahan_bakus' => ['nama_bahan'],  // Duplikat jika nama_bahan sama
        'bahan_pendukungs' => ['nama_bahan'],  // Duplikat jika nama_bahan sama
        'jabatans' => ['nama'],
        'produks' => ['nama_produk'],
        'pelanggans' => ['nama_pelanggan'],  // Duplikat jika nama_pelanggan sama
    ];

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE - Tidak ada data yang akan dihapus ===');
        } else {
            $this->info('=== Membersihkan Data Duplikat ===');
        }
        
        $this->newLine();
        
        $totalDeleted = 0;
        
        foreach ($this->tables as $table => $uniqueColumns) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                $this->warn("Table {$table} tidak ditemukan, skip...");
                continue;
            }
            
            $this->info("Checking table: {$table}");
            
            // Get total records before
            $totalBefore = DB::table($table)->count();
            
            // Find duplicates
            $duplicates = $this->findDuplicates($table, $uniqueColumns);
            
            if ($duplicates->isEmpty()) {
                $this->info("  ✓ No duplicates found");
                $this->newLine();
                continue;
            }
            
            $duplicateCount = $duplicates->count();
            $this->warn("  Found {$duplicateCount} duplicate groups");
            
            $deletedCount = 0;
            
            foreach ($duplicates as $duplicate) {
                // Get all records with same unique values
                $query = DB::table($table);
                
                foreach ($uniqueColumns as $column) {
                    $query->where($column, $duplicate->$column);
                }
                
                $records = $query->orderBy('id')->get();
                
                if ($records->count() <= 1) {
                    continue;
                }
                
                // Keep the first record (oldest), delete the rest
                $keepId = $records->first()->id;
                $deleteIds = $records->skip(1)->pluck('id')->toArray();
                
                $this->line("  - Keeping ID {$keepId}, deleting " . count($deleteIds) . " duplicates");
                
                if (!$isDryRun) {
                    DB::table($table)->whereIn('id', $deleteIds)->delete();
                }
                
                $deletedCount += count($deleteIds);
            }
            
            $totalAfter = $isDryRun ? $totalBefore : DB::table($table)->count();
            
            if ($deletedCount > 0) {
                $this->info("  ✓ Deleted {$deletedCount} duplicate records");
                $this->info("  Records: {$totalBefore} → {$totalAfter}");
            }
            
            $totalDeleted += $deletedCount;
            $this->newLine();
        }
        
        $this->info('=== Summary ===');
        
        if ($isDryRun) {
            $this->warn("Would delete {$totalDeleted} duplicate records");
            $this->info("Run without --dry-run to actually delete");
        } else {
            $this->info("✓ Deleted {$totalDeleted} duplicate records");
            $this->info("✓ Database cleaned successfully!");
        }
        
        return 0;
    }
    
    /**
     * Find duplicate records based on unique columns
     */
    protected function findDuplicates(string $table, array $uniqueColumns): \Illuminate\Support\Collection
    {
        $selectColumns = array_merge($uniqueColumns, [DB::raw('COUNT(*) as count')]);
        
        $query = DB::table($table)
            ->select($selectColumns)
            ->groupBy($uniqueColumns)
            ->having('count', '>', 1);
        
        return $query->get();
    }
}
