<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;

class SyncBahanBakuStok extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bahan-baku:sync-stok {--id= : ID bahan baku tertentu} {--force : Force sync tanpa konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi stok bahan baku dengan stock movements untuk konsistensi dengan laporan stok';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== SINKRONISASI STOK BAHAN BAKU ===');
        $this->info('Memperbaiki inkonsistensi antara detail bahan baku dan laporan stok');
        $this->newLine();

        $bahanBakuId = $this->option('id');
        $force = $this->option('force');

        if ($bahanBakuId) {
            // Sync bahan baku tertentu
            $bahanBaku = BahanBaku::find($bahanBakuId);
            
            if (!$bahanBaku) {
                $this->error("Bahan baku dengan ID {$bahanBakuId} tidak ditemukan!");
                return 1;
            }

            $this->syncSingleBahanBaku($bahanBaku, $force);
        } else {
            // Sync semua bahan baku
            $this->syncAllBahanBaku($force);
        }

        return 0;
    }

    private function syncSingleBahanBaku(BahanBaku $bahanBaku, $force = false)
    {
        $this->info("📦 Bahan Baku: {$bahanBaku->nama_bahan} (ID: {$bahanBaku->id})");
        
        $stokMaster = $bahanBaku->stok;
        $stokRealTime = $bahanBaku->stok_real_time;
        $selisih = $stokMaster - $stokRealTime;

        $this->table(
            ['Sumber', 'Stok', 'Satuan'],
            [
                ['Master Data (Detail Bahan Baku)', number_format($stokMaster, 4), 'kg'],
                ['Stock Movements (Laporan Stok)', number_format($stokRealTime, 4), 'kg'],
                ['Selisih', number_format($selisih, 4), 'kg']
            ]
        );

        if (abs($selisih) <= 0.01) {
            $this->info('✅ Stok sudah konsisten!');
            return;
        }

        $this->warn("⚠️  Ditemukan inkonsistensi sebesar {$selisih} kg");

        if (!$force && !$this->confirm('Apakah Anda ingin memperbaiki stok master?')) {
            $this->info('Sinkronisasi dibatalkan.');
            return;
        }

        $result = $bahanBaku->syncStokWithMovements();

        if ($result['updated']) {
            $this->info("✅ Berhasil! Stok diupdate dari {$result['old_stock']} kg menjadi {$result['new_stock']} kg");
        } else {
            $this->error('❌ Gagal melakukan sinkronisasi');
        }
    }

    private function syncAllBahanBaku($force = false)
    {
        $this->info('🔍 Menganalisis semua bahan baku...');
        
        $bahanBakus = BahanBaku::all();
        $inconsistentItems = [];

        // Cek inkonsistensi
        foreach ($bahanBakus as $bahan) {
            if (!$bahan->isStokConsistent()) {
                $inconsistentItems[] = [
                    'id' => $bahan->id,
                    'nama_bahan' => $bahan->nama_bahan,
                    'stok_master' => $bahan->stok,
                    'stok_real_time' => $bahan->stok_real_time,
                    'selisih' => $bahan->stok - $bahan->stok_real_time
                ];
            }
        }

        if (empty($inconsistentItems)) {
            $this->info('✅ Semua stok bahan baku sudah konsisten!');
            return;
        }

        $this->warn("⚠️  Ditemukan {count($inconsistentItems)} bahan baku dengan inkonsistensi:");
        
        $tableData = [];
        foreach ($inconsistentItems as $item) {
            $tableData[] = [
                $item['id'],
                $item['nama_bahan'],
                number_format($item['stok_master'], 2),
                number_format($item['stok_real_time'], 2),
                number_format($item['selisih'], 2)
            ];
        }

        $this->table(
            ['ID', 'Nama Bahan', 'Stok Master', 'Stok Real-Time', 'Selisih'],
            $tableData
        );

        if (!$force && !$this->confirm('Apakah Anda ingin memperbaiki semua inkonsistensi?')) {
            $this->info('Sinkronisasi dibatalkan.');
            return;
        }

        $this->info('🔄 Melakukan sinkronisasi...');
        
        $results = BahanBaku::syncAllStokWithMovements();
        
        if (empty($results)) {
            $this->info('✅ Tidak ada perubahan yang diperlukan.');
        } else {
            $this->info("✅ Berhasil memperbaiki {count($results)} bahan baku:");
            
            foreach ($results as $result) {
                $this->line("  • {$result['nama_bahan']}: {$result['old_stock']} → {$result['new_stock']} kg");
            }
        }

        $this->newLine();
        $this->info('🎯 Sinkronisasi selesai! Sekarang detail bahan baku dan laporan stok akan menunjukkan nilai yang sama.');
    }
}