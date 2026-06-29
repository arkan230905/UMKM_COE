<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\TargetProduksi;
use App\Models\TargetProduksiDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class TargetProduksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user pertama
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        // Ambil beberapa produk
        $produks = Produk::where('user_id', $user->id)->take(3)->get();
        
        if ($produks->isEmpty()) {
            $this->command->warn('No products found. Please create products first.');
            return;
        }

        $tahun = now()->year;

        foreach ($produks as $index => $produk) {
            // Target untuk tahun berjalan
            $totalTarget = ($index + 1) * 120000; // 120K, 240K, 360K
            
            $target = TargetProduksi::create([
                'user_id' => $user->id,
                'tahun' => $tahun,
                'produk_id' => $produk->id,
                'total_target_tahunan' => $totalTarget,
                'created_by' => $user->id,
            ]);

            // Generate distribusi bulanan
            $monthlyTargets = $this->generateMonthlyTargets($totalTarget);
            
            foreach ($monthlyTargets as $month => $targetBulanan) {
                TargetProduksiDetail::create([
                    'target_produksi_id' => $target->id,
                    'bulan' => $month,
                    'target_bulanan' => $targetBulanan,
                ]);
            }

            $this->command->info("Created target for {$produk->nama_produk} - {$tahun}: " . number_format($totalTarget, 0, ',', '.') . " units");

            // Target untuk tahun depan
            $nextYear = $tahun + 1;
            $totalTargetNext = ($index + 1) * 150000; // 150K, 300K, 450K (increased)
            
            $targetNext = TargetProduksi::create([
                'user_id' => $user->id,
                'tahun' => $nextYear,
                'produk_id' => $produk->id,
                'total_target_tahunan' => $totalTargetNext,
                'created_by' => $user->id,
            ]);

            $monthlyTargetsNext = $this->generateMonthlyTargets($totalTargetNext);
            
            foreach ($monthlyTargetsNext as $month => $targetBulanan) {
                TargetProduksiDetail::create([
                    'target_produksi_id' => $targetNext->id,
                    'bulan' => $month,
                    'target_bulanan' => $targetBulanan,
                ]);
            }

            $this->command->info("Created target for {$produk->nama_produk} - {$nextYear}: " . number_format($totalTargetNext, 0, ',', '.') . " units");
        }

        $this->command->info('Target Produksi seeded successfully!');
    }

    /**
     * Generate monthly targets dengan distribusi yang realistis
     */
    private function generateMonthlyTargets(int $totalTarget): array
    {
        // Distribusi persentase untuk setiap bulan (total 100%)
        $percentages = [
            1 => 7,   // Januari - awal tahun rendah
            2 => 7,   // Februari
            3 => 8,   // Maret - mulai naik
            4 => 8,   // April
            5 => 9,   // Mei
            6 => 10,  // Juni - puncak
            7 => 10,  // Juli - puncak
            8 => 9,   // Agustus
            9 => 9,   // September
            10 => 8,  // Oktober
            11 => 8,  // November
            12 => 7,  // Desember - akhir tahun turun
        ];

        $targets = [];
        $totalAllocated = 0;

        for ($i = 1; $i <= 11; $i++) {
            $target = floor($totalTarget * $percentages[$i] / 100);
            $targets[$i] = $target;
            $totalAllocated += $target;
        }

        // Bulan terakhir dapat sisa
        $targets[12] = $totalTarget - $totalAllocated;

        return $targets;
    }
}
