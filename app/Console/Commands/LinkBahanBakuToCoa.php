<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;

class LinkBahanBakuToCoa extends Command
{
    protected $signature = 'bahan:link-coa 
                            {--user-id= : ID user yang akan digunakan}
                            {--auto : Otomatis link berdasarkan nama bahan}';

    protected $description = 'Link bahan baku dan bahan pendukung ke COA Persediaan yang sesuai';

    public function handle()
    {
        $userId = $this->option('user-id');
        $auto = $this->option('auto');

        if (!$userId) {
            $userId = $this->ask('Masukkan ID user');
        }

        $this->info("Linking bahan baku dan bahan pendukung ke COA untuk user ID: {$userId}\n");

        // Mapping bahan baku ke COA
        $bahanBakuMapping = [
            'Ayam Potong' => '1141',
            'Ayam Kampung' => '1142',
            'Bebek' => '1143',
        ];

        // Mapping bahan pendukung ke COA
        $bahanPendukungMapping = [
            'Air' => '1150',
            'Minyak Goreng' => '1151',
            'Tepung Terigu' => '1152',
            'Tepung Maizena' => '1153',
            'Lada' => '1154',
            'Bubuk Kaldu' => '1155',
            'Bubuk Bawang Putih' => '1156',
            'Kemasan' => '1157',
        ];

        $this->info("=== BAHAN BAKU ===");
        $bahanBakus = BahanBaku::where('user_id', $userId)->get();
        
        foreach ($bahanBakus as $bahan) {
            $coaKode = null;
            
            // Cari COA yang sesuai berdasarkan nama
            foreach ($bahanBakuMapping as $namaBahan => $kode) {
                if (stripos($bahan->nama_bahan, $namaBahan) !== false) {
                    $coaKode = $kode;
                    break;
                }
            }

            if ($coaKode) {
                // Cek apakah COA ada
                $coa = Coa::withoutGlobalScopes()
                    ->where('kode_akun', $coaKode)
                    ->where('user_id', $userId)
                    ->first();

                if ($coa) {
                    $bahan->coa_persediaan_id = $coaKode;
                    $bahan->save();
                    $this->info("✓ {$bahan->nama_bahan} → COA {$coaKode} ({$coa->nama_akun})");
                } else {
                    $this->warn("✗ {$bahan->nama_bahan} → COA {$coaKode} tidak ditemukan");
                }
            } else {
                $this->line("  {$bahan->nama_bahan} → Tidak ada mapping");
            }
        }

        $this->newLine();
        $this->info("=== BAHAN PENDUKUNG ===");
        $bahanPendukungs = BahanPendukung::where('user_id', $userId)->get();
        
        foreach ($bahanPendukungs as $bahan) {
            $coaKode = null;
            
            // Cari COA yang sesuai berdasarkan nama
            foreach ($bahanPendukungMapping as $namaBahan => $kode) {
                if (stripos($bahan->nama_bahan, $namaBahan) !== false) {
                    $coaKode = $kode;
                    break;
                }
            }

            if ($coaKode) {
                // Cek apakah COA ada
                $coa = Coa::withoutGlobalScopes()
                    ->where('kode_akun', $coaKode)
                    ->where('user_id', $userId)
                    ->first();

                if ($coa) {
                    $bahan->coa_persediaan_id = $coaKode;
                    $bahan->save();
                    $this->info("✓ {$bahan->nama_bahan} → COA {$coaKode} ({$coa->nama_akun})");
                } else {
                    $this->warn("✗ {$bahan->nama_bahan} → COA {$coaKode} tidak ditemukan");
                }
            } else {
                $this->line("  {$bahan->nama_bahan} → Tidak ada mapping");
            }
        }

        $this->newLine();
        $this->info("✓ Selesai!");

        return 0;
    }
}
