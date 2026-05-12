<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DebugLaporanKasBank extends Command
{
    protected $signature = 'debug:laporan-kas-bank {start_date} {end_date}';
    protected $description = 'Debug laporan kas bank untuk melihat perhitungan detail';

    public function handle()
    {
        $startDate = $this->argument('start_date');
        $endDate = $this->argument('end_date');
        
        $this->info("Debug Laporan Kas Bank");
        $this->info("Periode: {$startDate} - {$endDate}");
        $this->info("================================");
        
        // 1. Pembelian
        $this->info("\n1. PEMBELIAN:");
        
        $pembelians = \App\Models\Pembelian::whereBetween('tanggal', [$startDate, $endDate])
            ->get(['id', 'nomor_pembelian', 'tanggal', 'total']);
            
        $totalPembelian = 0;
        foreach ($pembelians as $p) {
            // Hitung total dari details
            $total = $p->total ?? 0;
            if ($total == 0 && $p->details && $p->details->count() > 0) {
                $total = $p->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                });
            }
            $totalPembelian += $total;
            $this->line("  ID:{$p->id} | {$p->nomor_pembelian} | {$p->tanggal} | Total: {$total}");
        }
        $this->info("Total Pembelian: Rp " . number_format($totalPembelian, 0, ',', '.'));
        
        // 2. Pembayaran Utang
        $this->info("\n2. PEMBAYARAN UTANG:");
        
        $utangs = \DB::table('ap_settlements')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get(['id', 'kode_settlement', 'tanggal', 'dibayar_bersih']);
            
        $totalUtang = 0;
        foreach ($utangs as $u) {
            $totalUtang += $u->dibayar_bersih;
            $this->line("  ID:{$u->id} | {$u->kode_settlement} | {$u->tanggal} | Dibayar: {$u->dibayar_bersih}");
        }
        $this->info("Total Pembayaran Utang: Rp " . number_format($totalUtang, 0, ',', '.'));
        
        // 3. Pembayaran Retur Penjualan - sementara dihapus karena ada masalah dengan tabel returs
        $this->info("\n3. PEMBAYARAN RETUR PENJUALAN:");
        $this->info("Total Pembayaran Retur Penjualan: Rp 0 (sementara dihapus karena ada masalah dengan tabel returs)");
        
        // 4. Gaji
        $this->info("\n4. GAJI:");
        
        $penggajians = DB::table('penggajians')
            ->whereBetween('tanggal_penggajian', [$startDate, $endDate])
            ->get(['id', 'tanggal_penggajian', 'total_gaji']);
            
        $totalGaji = 0;
        foreach ($penggajians as $g) {
            $totalGaji += $g->total_gaji;
            $this->line("  ID:{$g->id} | {$g->tanggal_penggajian} | Gaji: {$g->total_gaji}");
        }
        $this->info("Total Gaji: Rp " . number_format($totalGaji, 0, ',', '.'));
        
        // 5. Beban
        $this->info("\n5. BEBAN:");
        
        $expenses = \App\Models\ExpensePayment::whereBetween('tanggal', [$startDate, $endDate])
            ->get(['id', 'tanggal', 'nominal']);
            
        $totalBeban = 0;
        foreach ($expenses as $e) {
            $totalBeban += $e->nominal;
            $this->line("  ID:{$e->id} | {$e->tanggal} | Nominal: {$e->nominal}");
        }
        $this->info("Total Beban: Rp " . number_format($totalBeban, 0, ',', '.'));
        
        // 6. Total Keseluruhan
        $this->info("\n================================");
        $this->info("TOTAL PENGELUARAN:");
        $this->info("  Pembelian: Rp " . number_format($totalPembelian, 0, ',', '.'));
        $this->info("  Pembayaran Utang: Rp " . number_format($totalUtang, 0, ',', '.'));
        $this->info("  Pembayaran Retur Penjualan: Rp 0 (sementara dihapus)");
        $this->info("  Gaji: Rp " . number_format($totalGaji, 0, ',', '.'));
        $this->info("  Beban: Rp " . number_format($totalBeban, 0, ',', '.'));
        $this->info("================================");
        $this->info("TOTAL KESSELURUHAN: Rp " . number_format($totalPembelian + $totalUtang + $totalGaji + $totalBeban, 0, ',', '.'));

        return 0;
    }
}
