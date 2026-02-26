<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDuplicateJabatan extends Command
{
    protected $signature = 'check:duplicate-jabatan';
    protected $description = 'Check for duplicate jabatan names';

    public function handle()
    {
        $this->info('Checking for duplicate jabatan names...');
        
        $duplicates = DB::select('
            SELECT nama, COUNT(*) as count 
            FROM jabatans 
            GROUP BY nama 
            HAVING COUNT(*) > 1
        ');
        
        if (empty($duplicates)) {
            $this->info('✓ No duplicates found');
        } else {
            $this->error('✗ Found duplicates:');
            foreach ($duplicates as $dup) {
                $this->line("  - {$dup->nama} appears {$dup->count} times");
                
                // Show IDs of duplicates
                $records = DB::table('jabatans')
                    ->where('nama', $dup->nama)
                    ->select('id', 'nama', 'kategori', 'created_at')
                    ->get();
                    
                foreach ($records as $record) {
                    $this->line("    ID: {$record->id}, Kategori: {$record->kategori}, Created: {$record->created_at}");
                }
            }
        }
        
        return 0;
    }
}
