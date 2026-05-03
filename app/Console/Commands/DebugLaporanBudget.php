<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PembayaranBeban;
use App\Models\BebanOperasional;

class DebugLaporanBudget extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:laporan-budget {--user=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug budget calculation in laporan pembayaran beban';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUG LAPORAN BUDGET FOR USER ID: {$userId} ===");
        
        // Simulate laporan pembayaran beban logic
        $selectedMonth = now();
        
        $this->info("\nSelected Month: " . $selectedMonth->format('Y-m'));
        
        // Get pembayaran beban for user
        $pembayaranQuery = PembayaranBeban::with(['coaBeban', 'coaKas', 'bebanOperasional'])
            ->where('user_id', $userId)
            ->whereYear('tanggal', $selectedMonth->year)
            ->whereMonth('tanggal', $selectedMonth->month);
        
        $pembayaranBeban = $pembayaranQuery->get();
        
        $this->info("Found {$pembayaranBeban->count()} pembayaran beban records");
        
        // Build the Budget vs Actual data (same as laporan controller)
        $laporanData = collect([]);
        $totalBudget = 0;
        $totalAktual = 0;
        $totalSelisih = 0;
        
        // Group by beban operasional or COA for reporting
        $groupedPembayaran = $pembayaranBeban->groupBy(function($item) {
            return $item->beban_operasional_id ? 'beban_' . $item->beban_operasional_id : 'coa_' . $item->akun_beban_id;
        });
        
        $this->info("\nGrouped pembayaran: {$groupedPembayaran->count()} groups");
        
        foreach ($groupedPembayaran as $groupKey => $pembayaranGroup) {
            $totalAmount = $pembayaranGroup->sum('jumlah');
            $firstItem = $pembayaranGroup->first();
            
            $namaBeban = 'Unknown';
            $kategori = 'Uncategorized';
            $budget = 0;
            
            if ($firstItem->beban_operasional) {
                $this->info("\nProcessing Beban Operasional:");
                $this->info("  - ID: {$firstItem->beban_operasional->id}");
                $this->info("  - Name: {$firstItem->beban_operasional->nama_beban}");
                $this->info("  - Owner: {$firstItem->beban_operasional->created_by}");
                $this->info("  - Current User: {$userId}");
                $this->info("  - Budget: {$firstItem->beban_operasional->budget_bulanan}");
                
                // If linked to beban operasional - use created_by field for ownership check
                if ($firstItem->beban_operasional->created_by == $userId) {
                    $namaBeban = $firstItem->beban_operasional->nama_beban;
                    $kategori = $firstItem->beban_operasional->kategori;
                    $budget = $firstItem->beban_operasional->budget_bulanan ?? 0;
                    $this->info("  - ✓ Ownership matched, using budget: {$budget}");
                } else {
                    // If beban operasional belongs to different user, treat as direct expense
                    $namaBeban = $firstItem->coaBeban ? $firstItem->coaBeban->nama_akun : 'Unknown';
                    $kategori = 'Direct Expense (Cross-User)';
                    $budget = 0;
                    $this->info("  - ✗ Ownership mismatch, budget set to 0");
                }
            } elseif ($firstItem->coaBeban) {
                // If only linked to COA
                $namaBeban = $firstItem->coaBeban->nama_akun;
                $kategori = 'Direct Expense';
                $budget = 0; // No budget if not linked to beban operasional
                $this->info("  - Only COA linked, budget set to 0");
            }
            
            $selisih = $budget - $totalAmount;
            $status = $totalAmount > $budget ? 'Over Budget' : 'Aman';
            
            $this->info("  - Final: {$namaBeban} | Budget: {$budget} | Aktual: {$totalAmount} | Selisih: {$selisih}");
            
            $laporanData->push((object) [
                'id' => $firstItem->id,
                'kategori' => $kategori,
                'nama_beban' => $namaBeban,
                'budget_bulanan' => $budget,
                'aktual_bulan_ini' => $totalAmount,
                'selisih' => $selisih,
                'status' => $status,
                'status_color' => $totalAmount > $budget ? 'danger' : 'success',
                'keterangan' => $firstItem->keterangan,
            ]);
            
            $totalBudget += $budget;
            $totalAktual += $totalAmount;
            $totalSelisih += $selisih;
        }
        
        $this->info("\n=== LAPORAN SUMMARY ===");
        $this->info("Total Budget: Rp " . number_format($totalBudget, 0, ',', '.'));
        $this->info("Total Aktual: Rp " . number_format($totalAktual, 0, ',', '.'));
        $this->info("Total Selisih: Rp " . number_format($totalSelisih, 0, ',', '.'));
        $this->info("Overall Status: " . ($totalAktual > $totalBudget ? 'Over Budget' : 'Aman'));
        
        $this->info("\n=== DETAILED LAPORAN DATA ===");
        foreach ($laporanData as $index => $data) {
            $this->info(($index + 1) . ". {$data->kategori} | {$data->nama_beban} | Budget: Rp " . number_format($data->budget_bulanan, 0, ',', '.') . " | Aktual: Rp " . number_format($data->aktual_bulan_ini, 0, ',', '.') . " | Status: {$data->status}");
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
