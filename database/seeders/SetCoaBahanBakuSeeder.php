<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanBaku;
use App\Models\Coa;
use App\Models\User;

class SetCoaBahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Auto-set COA Persediaan untuk semua Bahan Baku berdasarkan nama
     */
    public function run(): void
    {
        $this->command->info("========================================");
        $this->command->info("SET COA PERSEDIAAN BAHAN BAKU");
        $this->command->info("========================================\n");

        // Mapping nama bahan baku ke kode COA persediaan
        // Berdasarkan COA Seeder Ayam Goreng Bundo
        $coaMapping = [
            // Format: 'keyword dalam nama bahan' => 'kode_coa_persediaan'
            
            // Bahan Baku Ayam
            'ayam potong' => '1141',   // Pers. Bahan Baku ayam potong
            'ayam kampung' => '1142',  // Pers. Bahan Baku ayam kampung
            'bebek' => '1143',         // Pers. Bahan Baku bebek
            'ayam lainnya' => '1144',  // Pers. Bahan Baku ayam lainnya
            'ayam' => '1141',          // Default ke ayam potong
            
            // Fallback generic
            'daging' => '1141',        // Default ke ayam potong
            'unggas' => '1141',        // Default ke ayam potong
        ];

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        foreach ($users as $user) {
            $this->command->info("Processing user: {$user->name} (ID: {$user->id})");
            
            // Get semua bahan baku untuk user ini
            $bahanBakus = BahanBaku::where('user_id', $user->id)->get();
            
            if ($bahanBakus->isEmpty()) {
                $this->command->warn("  ⚠️  Tidak ada bahan baku untuk user ini\n");
                continue;
            }

            $updated = 0;
            $skipped = 0;
            $errors = 0;

            foreach ($bahanBakus as $bahan) {
                // Skip jika sudah ada COA persediaan
                if (!empty($bahan->coa_persediaan_id)) {
                    $skipped++;
                    $this->command->line("  ⏭️  {$bahan->nama_bahan} - Already has COA: {$bahan->coa_persediaan_id}");
                    continue;
                }

                // Cari COA yang cocok berdasarkan nama bahan
                $namaBahanLower = strtolower($bahan->nama_bahan);
                $coaCode = null;

                // Cari keyword yang matching (prioritas keyword lebih spesifik dulu)
                $matchedKeyword = null;
                foreach ($coaMapping as $keyword => $kodeCoa) {
                    if (str_contains($namaBahanLower, $keyword)) {
                        // Prioritaskan keyword yang lebih panjang (lebih spesifik)
                        if ($matchedKeyword === null || strlen($keyword) > strlen($matchedKeyword)) {
                            $matchedKeyword = $keyword;
                            $coaCode = $kodeCoa;
                        }
                    }
                }

                // Jika tidak ada yang cocok, gunakan generic COA 114 (Pers. Bahan Baku)
                if (!$coaCode) {
                    $coaCode = '114'; // Generic Pers. Bahan Baku
                    $matchedKeyword = 'generic';
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
                    
                    $this->command->info("  ✅ {$bahan->nama_bahan} → COA {$coaCode} ({$coa->nama_akun}) [matched: {$matchedKeyword}]");
                } else {
                    $errors++;
                    $this->command->error("  ❌ {$bahan->nama_bahan} - COA {$coaCode} tidak ditemukan untuk user {$user->id}");
                }
            }

            $this->command->info("  Updated: {$updated}, Skipped: {$skipped}, Errors: {$errors}\n");
            
            $totalUpdated += $updated;
            $totalSkipped += $skipped;
            $totalErrors += $errors;
        }

        $this->command->info("========================================");
        $this->command->info("✅ Seeder completed!");
        $this->command->info("Total updated: {$totalUpdated}");
        $this->command->info("Total skipped: {$totalSkipped}");
        $this->command->info("Total errors: {$totalErrors}");
        $this->command->info("========================================");
    }
}
