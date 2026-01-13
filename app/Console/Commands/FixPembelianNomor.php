<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPembelianNomor extends Command
{
    protected $signature = 'fix:pembelian-nomor';
    protected $description = 'Fix nomor pembelian yang kosong';

    public function handle()
    {
        $this->info('Checking pembelian records...');
        
        // Check if column exists
        $columnExists = DB::select("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pembelians' AND column_name = 'nomor_pembelian'");
        
        if ($columnExists[0]->count == 0) {
            $this->error('Column nomor_pembelian does not exist in pembelians table');
            return 1;
        }
        
        $this->info('Column nomor_pembelian exists');
        
        // Get records without nomor
        $records = DB::table('pembelians')
            ->whereNull('nomor_pembelian')
            ->get(['id', 'tanggal']);
        
        $this->info("Found " . $records->count() . " pembelian records without nomor_pembelian");
        
        if ($records->count() > 0) {
            foreach ($records as $record) {
                $tanggal = $record->tanggal;
                $date = is_string($tanggal) ? $tanggal : $tanggal->format('Ymd');
                
                // Hitung jumlah pembelian hari ini
                $count = DB::table('pembelians')
                    ->whereDate('tanggal', $tanggal)
                    ->whereNotNull('nomor_pembelian')
                    ->count() + 1;
                
                // Format: PB-YYYYMMDD-0001
                $nomor = 'PB-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                
                // Update the record
                DB::table('pembelians')
                    ->where('id', $record->id)
                    ->update(['nomor_pembelian' => $nomor]);
                
                $this->info("Updated ID {$record->id} with nomor: $nomor");
            }
            
            $this->info('Update completed!');
        }
        
        // Show updated data
        $updated = DB::table('pembelians')
            ->select('id', 'nomor_pembelian', 'tanggal')
            ->limit(10)
            ->get();
        
        $this->info('Updated sample data:');
        foreach ($updated as $row) {
            $this->line("ID: {$row->id}, Nomor: " . ($row->nomor_pembelian ?? 'NULL') . ", Tanggal: {$row->tanggal}");
        }

        return 0;
    }
}
