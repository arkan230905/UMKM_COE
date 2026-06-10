<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanBaku;
use App\Models\Satuan;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class DefaultBahanBakuJasukeSeeder extends Seeder
{
    /**
     * Seed default Bahan Baku untuk Jasuke (Jagung Susu Keju)
     * Dipanggil otomatis saat user registrasi
     */
    public function run(?int $userId = null): void
    {
        // Jika userId tidak diberikan, gunakan user pertama dari database
        if ($userId === null) {
            $user = DB::table('users')->first();
            if (!$user) {
                if ($this->command) {
                    $this->command->error('No users found in database. Please create a user first.');
                }
                return;
            }
            $userId = $user->id;
        }

        // Cek apakah sudah ada bahan baku untuk user ini
        $existingCount = BahanBaku::where('user_id', $userId)->count();
        if ($existingCount > 0) {
            if ($this->command) {
                $this->command->info("Bahan Baku already exists for user ID: {$userId} ({$existingCount} items)");
            }
            return;
        }

        // Ambil satuan yang diperlukan
        $satuanKg = Satuan::where('nama', 'Kilogram')->first();
        
        if (!$satuanKg) {
            if ($this->command) {
                $this->command->error('Satuan Kilogram not found. Please run SatuanDefaultSeeder first.');
            }
            return;
        }

        // Ambil COA Persediaan Bahan Baku Jagung
        $coaJagung = Coa::where('kode_akun', '1141')
            ->where('user_id', $userId)
            ->first();

        if (!$coaJagung) {
            if ($this->command) {
                $this->command->error('COA Persediaan Bahan Baku Jagung (1141) not found. Please run DefaultCoaSeeder first.');
            }
            return;
        }

        // Default Bahan Baku untuk Jasuke
        $bahanBakuData = [
            [
                'nama_bahan' => 'Jagung Manis',
                'satuan_id' => $satuanKg->id,
                'harga_satuan' => 15000, // Rp 15.000 per kg
                'stok_minimum' => 10,
                'saldo_awal' => 0,
                'coa_persediaan_id' => $coaJagung->kode_akun,
            ],
        ];

        foreach ($bahanBakuData as $data) {
            BahanBaku::create(array_merge($data, [
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        if ($this->command) {
            $this->command->info("✅ Default Bahan Baku Jasuke created for user ID: {$userId}");
        }
    }
}
