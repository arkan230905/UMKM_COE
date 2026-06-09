<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanPendukung;
use App\Models\Coa;
use App\Models\User;

class SetCoaBahanPendukungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Auto-set COA Persediaan untuk semua Bahan Pendukung berdasarkan nama
     */
    public function run(): void
    {
        $this->command->info("========================================");
        $this->command->info("SET COA PERSEDIAAN BAHAN PENDUKUNG");
        $this->command->info("========================================\n");

        // Mapping nama bahan pendukung ke kode COA persediaan
        // Berdasarkan COA Seeder Ayam Goreng Bundo
        $coaMapping = [
            // Format: 'keyword dalam nama bahan' => 'kode_coa_persediaan'
            'air' => '1150',           // Pers. Bahan Pendukung Air
            'minyak' => '1151',        // Pers. Bahan Pendukung Minyak Goreng
            'tepung terigu' => '1152', // Pers. Bahan Pendukung Tepung Terigu
            'tepung maizena' => '1153',// Pers. Bahan Pendukung Tepung Maizena
            'lada' => '1154',          // Pers. Bahan Pendukung Lada
            'kaldu' => '1155',         // Pers. Bahan Pendukung Bubuk Kaldu
            'bawang putih' => '1156',  // Pers. Bahan Pendukung Bubuk Bawang Putih
            'kemasan' => '1157',       // Pers. Bahan Pendukung Kemasan
            
            // Fallback untuk bahan pendukung lain
            'bubuk' => '1155',         // Default ke Bubuk Kaldu
            'bumbu' => '1154',         // Default ke Lada
        ];

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $totalUpdated = 0;
        $totalSkipped = 0;

        foreach ($users as $user) {
            $this->command->info("Processing user: {$user->name} (ID: {$user->id})");
            
            // Get semua bahan pendukung untuk user ini
            $bahanPendukungs = BahanPendukung::where('user_id', $user->id)->get();
            
            if ($bahanPendukungs->isEmpty()) {
                $this->command->warn("  ⚠️  Tidak ada bahan pendukung untuk user ini\n");
                continue;
            }

            $updated = 0;
            $skipped = 0;

            foreach ($bahanPendukungs as $bahan) {
                // Skip jika sudah ada COA persediaan
                if (!empty($bahan->coa_persediaan_id)) {
                    $skipped++;
                    continue;
                }

                // Cari COA yang cocok berdasarkan nama bahan
                $namaBahanLower = strtolower($bahan->nama_bahan);
                $coaCode = null;

                // Cari keyword yang matching
                foreach ($coaMapping as $keyword => $kodeCoa) {
                    if (str_contains($namaBahanLower, $keyword)) {
                        $coaCode = $kodeCoa;
                        break;
                    }
                }

                // Jika tidak ada yang cocok, gunakan generic COA 115 (Pers. Bahan Pendukung)
                if (!$coaCode) {
                    $coaCode = '115'; // Generic Pers. Bahan Pendukung
                }

                // Cek apakah COA ada untuk user ini
                $coa = Coa::where('kode_akun', $coaCode)
                    ->where('user_id', $user->id)
                    ->first();

                if ($coa) {
                    $bahan->update([
                        'coa_persediaan_id' => $coaCode
                    ]);
                    $updated++;
                    
                    $this->command->info("  ✅ {$bahan->nama_bahan} → COA {$coaCode} ({$coa->nama_akun})");
                } else {
                    $this->command->warn("  ⚠️  {$bahan->nama_bahan} - COA {$coaCode} tidak ditemukan");
                }
            }

            $this->command->info("  Updated: {$updated}, Skipped: {$skipped}\n");
            
            $totalUpdated += $updated;
            $totalSkipped += $skipped;
        }

        $this->command->info("========================================");
        $this->command->info("✅ Seeder completed!");
        $this->command->info("Total updated: {$totalUpdated}");
        $this->command->info("Total skipped: {$totalSkipped}");
        $this->command->info("========================================");
    }
}
