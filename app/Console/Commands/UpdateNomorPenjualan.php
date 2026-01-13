<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateNomorPenjualan extends Command
{
    protected $signature = 'update:nomor-penjualan';
    protected $description = 'Update nomor penjualan yang kosong';

    public function handle()
    {
        // Update nomor penjualan yang kosong
        $penjualan = DB::table('penjualans')
            ->whereNull('nomor_penjualan')
            ->get(['id', 'tanggal']);

        $this->info("Found " . $penjualan->count() . " penjualan records without nomor_penjualan");

        if ($penjualan->count() > 0) {
            foreach ($penjualan as $p) {
                $tanggal = $p->tanggal;
                $date = is_string($tanggal) ? $tanggal : $tanggal->format('Ymd');
                
                // Hitung jumlah penjualan hari ini
                $count = DB::table('penjualans')
                    ->whereDate('tanggal', $tanggal)
                    ->whereNotNull('nomor_penjualan')
                    ->count() + 1;
                
                // Format: PJ-YYYYMMDD-0001
                $nomor = 'PJ-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                
                // Update the record
                DB::table('penjualans')
                    ->where('id', $p->id)
                    ->update(['nomor_penjualan' => $nomor]);
                
                $this->info("Updated ID {$p->id} with nomor: $nomor");
            }
            
            $this->info("Update completed!");
        }

        // Show updated data
        $updated = DB::table('penjualans')
            ->select('id', 'nomor_penjualan', 'tanggal')
            ->limit(5)
            ->get();

        $this->info("Updated sample data:");
        foreach ($updated as $row) {
            $this->line("ID: {$row->id}, Nomor: " . ($row->nomor_penjualan ?? 'NULL') . ", Tanggal: {$row->tanggal}");
        }

        return 0;
    }
}
