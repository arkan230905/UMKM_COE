<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PembayaranBeban;

class DebugPembayaranDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:pembayaran-detail {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug detailed pembayaran beban data and relationships';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUG PEMBAYARAN DETAIL FOR USER ID: {$userId} ===");
        
        // Get pembayaran beban with all relationships
        $pembayaranBeban = PembayaranBeban::with(['coaBeban', 'coaKas', 'bebanOperasional'])
            ->where('user_id', $userId)
            ->get();
        
        $this->info("\nFound {$pembayaranBeban->count()} pembayaran beban records:");
        
        foreach ($pembayaranBeban as $index => $pembayaran) {
            $this->info("\n" . ($index + 1) . ". Pembayaran ID: {$pembayaran->id}");
            $this->info("   User ID: {$pembayaran->user_id}");
            $this->info("   Tanggal: {$pembayaran->tanggal}");
            $this->info("   Jumlah: {$pembayaran->jumlah}");
            $this->info("   Keterangan: {$pembayaran->keterangan}");
            
            $this->info("   Beban Operasional ID: " . ($pembayaran->beban_operasional_id ?? 'NULL'));
            if ($pembayaran->beban_operasional) {
                $this->info("   Beban Operasional Name: {$pembayaran->beban_operasional->nama_beban}");
                $this->info("   Beban Operasional Owner: {$pembayaran->beban_operasional->created_by}");
                $this->info("   Beban Operasional Budget: {$pembayaran->beban_operasional->budget_bulanan}");
                $this->info("   Beban Operasional Status: {$pembayaran->beban_operasional->status}");
            } else {
                $this->info("   Beban Operasional: NULL (not linked)");
            }
            
            $this->info("   COA Beban ID: {$pembayaran->akun_beban_id}");
            if ($pembayaran->coaBeban) {
                $this->info("   COA Beban Name: {$pembayaran->coaBeban->nama_akun}");
                $this->info("   COA Beban Kode: {$pembayaran->coaBeban->kode_akun}");
            } else {
                $this->info("   COA Beban: NULL");
            }
            
            $this->info("   COA Kas ID: {$pembayaran->akun_kas_id}");
            if ($pembayaran->coaKas) {
                $this->info("   COA Kas Name: {$pembayaran->coaKas->nama_akun}");
            } else {
                $this->info("   COA Kas: NULL");
            }
        }
        
        // Check if there's a relationship issue
        $this->info("\n=== RELATIONSHIP ANALYSIS ===");
        foreach ($pembayaranBeban as $pembayaran) {
            $this->info("\nPembayaran ID {$pembayaran->id}:");
            
            if ($pembayaran->beban_operasional_id) {
                if ($pembayaran->bebanOperasional) {
                    $this->info("  ✓ Beban Operasional relationship working");
                    if ($pembayaran->beban_operasional->created_by == $userId) {
                        $this->info("  ✓ Owner match - Budget should be: {$pembayaran->beban_operasional->budget_bulanan}");
                    } else {
                        $this->info("  ✗ Owner mismatch - Budget will be 0");
                    }
                } else {
                    $this->info("  ✗ Beban Operasional relationship broken - ID exists but no data loaded");
                }
            } else {
                $this->info("  - No Beban Operasional ID - Will use COA only");
            }
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
