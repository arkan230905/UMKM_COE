<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateNomorPembelian extends Command
{
    protected $signature = 'update:nomor-pembelian';
    protected $description = 'Update nomor pembelian yang kosong';

    public function handle()
    {
        // Update nomor pembelian yang kosong
        $pembelian = DB::table('pembelians')
            ->whereNull('nomor_pembelian')
            ->get(['id', 'tanggal']);

        $this->info("Found " . $pembelian->count() . " pembelian records without nomor_pembelian");

        if ($pembelian->count() > 0) {
            foreach ($pembelian as $p) {
                $tanggal = $p->tanggal;
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
                    ->where('id', $p->id)
                    ->update(['nomor_pembelian' => $nomor]);
                
                $this->info("Updated ID {$p->id} with nomor: $nomor");
            }
            
            $this->info("Update completed!");
        }

        // Show updated data
        $updated = DB::table('pembelians')
            ->select('id', 'nomor_pembelian', 'tanggal')
            ->limit(10)
            ->get();

        $this->info("Updated sample data:");
        foreach ($updated as $row) {
            $this->line("ID: {$row->id}, Nomor: " . ($row->nomor_pembelian ?? 'NULL') . ", Tanggal: {$row->tanggal}");
        }

        return 0;
    }
}
