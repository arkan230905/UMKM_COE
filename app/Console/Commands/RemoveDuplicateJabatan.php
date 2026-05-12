<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateJabatan extends Command
{
    protected $signature = 'fix:duplicate-jabatan';
    protected $description = 'Remove duplicate jabatan names (keep oldest)';

    public function handle()
    {
        $this->info('Finding and removing duplicate jabatan names...');
        
        $duplicates = DB::select('
            SELECT nama, COUNT(*) as count 
            FROM jabatans 
            GROUP BY nama 
            HAVING COUNT(*) > 1
        ');
        
        if (empty($duplicates)) {
            $this->info('✓ No duplicates found');
            return 0;
        }
        
        foreach ($duplicates as $dup) {
            $this->line("Processing: {$dup->nama} ({$dup->count} records)");
            
            // Get all records with this name, ordered by created_at (oldest first)
            $records = DB::table('jabatans')
                ->where('nama', $dup->nama)
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Keep the first (oldest) record, delete the rest
            $keepId = $records->first()->id;
            $this->info("  ✓ Keeping ID: {$keepId} (created: {$records->first()->created_at})");
            
            foreach ($records->skip(1) as $record) {
                // Check if this jabatan is used in pegawai (by name, not ID)
                $pegawaiCount = DB::table('pegawais')
                    ->where('jabatan', $record->nama)
                    ->count();
                
                if ($pegawaiCount > 0) {
                    $this->warn("  ⚠ ID: {$record->id} is used by {$pegawaiCount} pegawai - they already reference by name, so safe to delete");
                    // Pegawai uses jabatan name, not ID, so we can safely delete the duplicate
                    // The pegawai will still reference the correct jabatan by name
                }
                
                // Check if this jabatan is used in btkls (by ID)
                $btklCount = DB::table('btkls')
                    ->where('jabatan_id', $record->id)
                    ->count();
                
                if ($btklCount > 0) {
                    $this->warn("  ⚠ ID: {$record->id} is used by {$btklCount} BTKL - updating to use ID: {$keepId}");
                    
                    // Update btkls to use the kept jabatan
                    DB::table('btkls')
                        ->where('jabatan_id', $record->id)
                        ->update(['jabatan_id' => $keepId]);
                }
                
                // Delete the duplicate
                DB::table('jabatans')->where('id', $record->id)->delete();
                $this->info("  ✗ Deleted ID: {$record->id} (created: {$record->created_at})");
            }
        }
        
        $this->info('✓ Duplicate removal completed');
        return 0;
    }
}
