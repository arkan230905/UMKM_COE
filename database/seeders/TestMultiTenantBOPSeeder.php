<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestMultiTenantBOPSeeder extends Seeder
{
    /**
     * Test multi-tenant BOP journal creation using dynamic COA from bop_proses table
     * Ensures each user's BOP components use their specific COA accounts
     */
    public function run(): void
    {
        Log::info('Starting TestMultiTenantBOPSeeder');
        
        $user_id = 7;
        $produksiId = 1;
        
        // Delete existing BOP journals
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->delete();
        
        // Get BOP proses data for this user
        $bopProsesList = DB::table('bop_proses')
            ->where('user_id', $user_id)
            ->where('is_active', true)
            ->get();
        
        Log::info("BOP Proses Data for User {$user_id}:");
        foreach ($bopProsesList as $bopProses) {
            Log::info("- {$bopProses->nama_bop_proses}: {$bopProses->total_bop_per_produk}");
            
            $komponenBop = $bopProses->komponen_bop;
            if (is_string($komponenBop)) {
                $komponenBop = json_decode($komponenBop, true) ?? [];
            } elseif (!is_array($komponenBop)) {
                $komponenBop = [];
            }
            
            Log::info("  Komponen: " . json_encode($komponenBop));
            
            $totalBopPerProduk = $bopProses->total_bop_per_produk ?? 0;
            $totalBopAmount = $totalBopPerProduk * 120; // Using qty_produksi = 120
            
            if ($totalBopAmount > 0) {
                // Create journal entry for each component with its specific COA from database
                foreach ($komponenBop as $komp) {
                    $componentName = $komp['component'] ?? 'BOP';
                    $ratePerHour = $komp['rate_per_hour'] ?? 0;
                    $coaDebit = $komp['coa_debit'] ?? '1173';
                    $coaKredit = $komp['coa_kredit'] ?? '510';
                    $description = $komp['description'] ?? '';
                    
                    // Calculate proportional amount for this component
                    $totalRate = array_sum(array_column($komponenBop, 'rate_per_hour'));
                    $componentAmount = $totalRate > 0 ? ($ratePerHour / $totalRate) * $totalBopAmount : 0;
                    
                    if ($componentAmount > 0) {
                        // Create debit entry (WIP BOP account)
                        $coaDebitId = $this->getCoaIdByKode($coaDebit, $user_id);
                        DB::table('jurnal_umum')->insert([
                            'user_id' => $user_id,
                            'coa_id' => $coaDebitId,
                            'tanggal' => '2026-05-09',
                            'keterangan' => "Alokasi BOP - {$bopProses->nama_bop_proses} ({$componentName}) ke Barang WIP BOP",
                            'debit' => $componentAmount,
                            'kredit' => 0,
                            'referensi' => $produksiId,
                            'tipe_referensi' => 'produksi_bop',
                            'created_by' => $user_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        // Create credit entry (expense account)
                        $coaKreditId = $this->getCoaIdByKode($coaKredit, $user_id);
                        DB::table('jurnal_umum')->insert([
                            'user_id' => $user_id,
                            'coa_id' => $coaKreditId,
                            'tanggal' => '2026-05-09',
                            'keterangan' => "Alokasi BOP - {$bopProses->nama_bop_proses} ({$componentName}) ke Barang WIP BOP",
                            'debit' => 0,
                            'kredit' => $componentAmount,
                            'referensi' => $produksiId,
                            'tipe_referensi' => 'produksi_bop',
                            'created_by' => $user_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        Log::info("  Created journal for {$componentName}: Debit {$coaDebit} = {$componentAmount}, Kredit {$coaKredit} = {$componentAmount}");
                    }
                }
            }
        }
        
        // Update transfer WIP BOP to match correct total
        $totalBOP = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->sum('debit');
            
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'Transfer WIP BOP ke Barang Jadi')
            ->update(['kredit' => $totalBOP]);
        
        // Update transfer WIP total
        $totalWIPTransfer = 300000 + 54000 + $totalBOP; // BBB + BTKL + BOP
        DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_transfer')
            ->where('keterangan', 'Transfer WIP ke Barang Jadi - Jasuke')
            ->update(['debit' => $totalWIPTransfer]);
        
        // Verify balance
        $totalDebit = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->sum('debit');
            
        $totalKredit = DB::table('jurnal_umum')
            ->where('user_id', $user_id)
            ->where('referensi', $produksiId)
            ->where('tipe_referensi', 'produksi_bop')
            ->sum('kredit');
        
        Log::info("Multi-Tenant BOP Test Results:");
        Log::info("- Total Debit: Rp " . number_format($totalDebit, 2, ',', '.'));
        Log::info("- Total Kredit: Rp " . number_format($totalKredit, 2, ',', '.'));
        Log::info("- Balance: Rp " . number_format($totalDebit - $totalKredit, 2, ',', '.'));
        Log::info("- Total BOP Transfer: Rp " . number_format($totalBOP, 2, ',', '.'));
        
        if ($totalDebit == $totalKredit) {
            Log::info('SUCCESS: Multi-tenant BOP journals are balanced!');
        } else {
            Log::warning('WARNING: Multi-tenant BOP journals still not balanced');
        }
        
        $this->command->info('Multi-tenant BOP test completed!');
        $this->command->info("Final balance: " . ($totalDebit - $totalKredit));
    }
    
    private function getCoaIdByKode($kodeAkun, $user_id)
    {
        $coa = DB::table('accounts')
            ->where('kode_akun', $kodeAkun)
            ->where('user_id', $user_id)
            ->first();
        
        return $coa ? $coa->id : null;
    }
}
