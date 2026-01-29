<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanPendukung;

class CleanupDuplicates extends Command
{
    protected $signature = 'cleanup:duplicates';
    protected $description = 'Cleanup duplicate bahan pendukung records';

    public function handle()
    {
        $this->info('Starting cleanup of duplicate bahan pendukung records...');
        
        // Cek duplikat berdasarkan nama
        $duplicates = BahanPendukung::select('nama_bahan')
            ->groupBy('nama_bahan')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $this->info('Found ' . $duplicates->count() . ' duplicate items');

        $deletedCount = 0;
        
        foreach ($duplicates as $duplicate) {
            $this->line('- Duplicate: ' . $duplicate->nama_bahan);
            
            // Ambil semua record dengan nama yang sama
            $items = BahanPendukung::where('nama_bahan', $duplicate->nama_bahan)
                ->orderBy('id')
                ->get();
            
            $this->line('  Records:');
            foreach ($items as $item) {
                $this->line('    ID: ' . $item->id . ', Harga: ' . $item->harga_satuan . ', Created: ' . $item->created_at);
            }
            
            // Hapus duplikat (kecuali yang pertama)
            $toDelete = $items->slice(1);
            foreach ($toDelete as $item) {
                $this->line('    -> Deleting ID: ' . $item->id);
                $item->delete();
                $deletedCount++;
            }
        }

        $this->info('Cleanup completed! Deleted ' . $deletedCount . ' duplicate records.');
        
        // Cek hasil
        $allItems = BahanPendukung::orderBy('nama_bahan')->get();
        $this->line('');
        $this->line('Current items:');
        foreach ($allItems as $item) {
            $this->line('- ' . $item->nama_bahan . ' (ID: ' . $item->id . ')');
        }
        
        return 0;
    }
}
