<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class FixBopCoaMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:bop-coa-mapping {user_id : User ID yang akan diperbaiki}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix BOP COA mapping issue where Keju components use BOP - Susu instead of BOP - Keju';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user_id = $this->argument('user_id');
        
        $this->info("Memperbaiki BOP COA mapping untuk user_id: $user_id");
        $this->newLine();
        
        try {
            DB::beginTransaction();
            
            // Step 1: Pastikan COA BOP - Keju (533) ada
            $kejuCoa = Coa::where('user_id', $user_id)
                ->where('kode_akun', '533')
                ->first();
            
            if (!$kejuCoa) {
                $this->info("Membuat COA 533 - BOP - Keju...");
                $kejuCoa = Coa::create([
                    'user_id' => $user_id,
                    'kode_akun' => '533',
                    'nama_akun' => 'BOP - Keju',
                    'tipe_akun' => 'Biaya',
                    'saldo_normal' => 'debit',
                ]);
                $this->info("✅ COA 533 - BOP - Keju berhasil dibuat");
            } else {
                $this->info("✅ COA 533 - BOP - Keju sudah ada");
            }
            
            // Step 2: Pastikan COA BOP - Susu (531) ada dan benar
            $susuCoa = Coa::where('user_id', $user_id)
                ->where('kode_akun', '531')
                ->first();
            
            if (!$susuCoa) {
                $this->info("Membuat COA 531 - BOP - Susu...");
                $susuCoa = Coa::create([
                    'user_id' => $user_id,
                    'kode_akun' => '531',
                    'nama_akun' => 'BOP - Susu',
                    'tipe_akun' => 'Biaya',
                    'saldo_normal' => 'debit',
                ]);
                $this->info("✅ COA 531 - BOP - Susu berhasil dibuat");
            } else {
                // Update nama jika salah
                if ($susuCoa->nama_akun !== 'BOP - Susu') {
                    $susuCoa->update(['nama_akun' => 'BOP - Susu']);
                    $this->info("✅ COA 531 nama diupdate menjadi 'BOP - Susu'");
                } else {
                    $this->info("✅ COA 531 - BOP - Susu sudah benar");
                }
            }
            
            // Step 3: Cari jurnal yang salah (Keju menggunakan COA 531)
            $this->newLine();
            $this->info("Mencari jurnal yang salah...");
            
            $incorrectEntries = JurnalUmum::with('coa')
                ->where('user_id', $user_id)
                ->whereHas('coa', function($q) {
                    $q->where('kode_akun', '531'); // BOP - Susu
                })
                ->where('keterangan', 'LIKE', '%Keju%')
                ->where('tipe_referensi', 'produksi_bop')
                ->get();
            
            $this->info("Ditemukan " . $incorrectEntries->count() . " jurnal yang salah");
            
            if ($incorrectEntries->count() > 0) {
                $this->newLine();
                $this->info("Memperbaiki jurnal yang salah:");
                
                $bar = $this->output->createProgressBar($incorrectEntries->count());
                $bar->start();
                
                foreach ($incorrectEntries as $entry) {
                    // Update COA dan keterangan
                    $newKeterangan = str_replace('BOP - Susu', 'BOP - Keju', $entry->keterangan);
                    
                    $entry->update([
                        'coa_id' => $kejuCoa->id,
                        'keterangan' => $newKeterangan,
                    ]);
                    
                    $bar->advance();
                }
                
                $bar->finish();
                $this->newLine(2);
                $this->info("✅ Semua jurnal berhasil diperbaiki");
            }
            
            // Step 4: Verifikasi hasil
            $this->newLine();
            $this->info("=== VERIFIKASI HASIL ===");
            
            // Cek jurnal Keju dengan COA 533
            $kejuCorrect = JurnalUmum::with('coa')
                ->where('user_id', $user_id)
                ->whereHas('coa', function($q) {
                    $q->where('kode_akun', '533'); // BOP - Keju
                })
                ->where('keterangan', 'LIKE', '%Keju%')
                ->where('tipe_referensi', 'produksi_bop')
                ->count();
            
            // Cek jurnal Keju yang masih salah (COA 531)
            $kejuIncorrect = JurnalUmum::with('coa')
                ->where('user_id', $user_id)
                ->whereHas('coa', function($q) {
                    $q->where('kode_akun', '531'); // BOP - Susu
                })
                ->where('keterangan', 'LIKE', '%Keju%')
                ->where('tipe_referensi', 'produksi_bop')
                ->count();
            
            // Cek jurnal Susu dengan COA 531
            $susuCorrect = JurnalUmum::with('coa')
                ->where('user_id', $user_id)
                ->whereHas('coa', function($q) {
                    $q->where('kode_akun', '531'); // BOP - Susu
                })
                ->where('keterangan', 'LIKE', '%Susu%')
                ->where('tipe_referensi', 'produksi_bop')
                ->count();
            
            $this->table(
                ['Komponen', 'COA yang Benar', 'Jumlah Jurnal', 'Status'],
                [
                    ['Keju', '533 (BOP - Keju)', $kejuCorrect, '✅'],
                    ['Keju (salah)', '531 (BOP - Susu)', $kejuIncorrect, $kejuIncorrect == 0 ? '✅' : '❌'],
                    ['Susu', '531 (BOP - Susu)', $susuCorrect, '✅'],
                ]
            );
            
            if ($kejuIncorrect == 0) {
                $this->info("🎉 PERBAIKAN BERHASIL! Semua jurnal BOP sudah menggunakan COA yang benar.");
            } else {
                $this->warn("⚠️  Masih ada jurnal yang salah. Silakan periksa manual.");
            }
            
            DB::commit();
            $this->info("✅ Transaksi database berhasil di-commit");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ ERROR: " . $e->getMessage());
            $this->error("Transaksi database di-rollback");
            
            return Command::FAILURE;
        }
    }
}