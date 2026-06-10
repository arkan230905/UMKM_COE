<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanPendukung;
use App\Models\Satuan;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class DefaultBahanPendukungJasukeSeeder extends Seeder
{
    /**
     * Seed default Bahan Pendukung untuk Jasuke (Jagung Susu Keju)
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

        // Cek apakah sudah ada bahan pendukung untuk user ini
        $existingCount = BahanPendukung::where('user_id', $userId)->count();
        if ($existingCount > 0) {
            if ($this->command) {
                $this->command->info("Bahan Pendukung already exists for user ID: {$userId} ({$existingCount} items)");
            }
            return;
        }

        // Ambil satuan yang diperlukan
        $satuanLiter = Satuan::where('nama', 'Liter')->first();
        $satuanGram = Satuan::where('nama', 'Gram')->first();
        $satuanPcs = Satuan::where('nama', 'Pcs')->first();
        
        if (!$satuanLiter || !$satuanGram || !$satuanPcs) {
            if ($this->command) {
                $this->command->error('Satuan not found. Please run SatuanDefaultSeeder first.');
            }
            return;
        }

        // Ambil COA Persediaan Bahan Pendukung
        $coaSusu = Coa::where('kode_akun', '1151')->where('user_id', $userId)->first();
        $coaKeju = Coa::where('kode_akun', '1152')->where('user_id', $userId)->first();
        $coaKemasan = Coa::where('kode_akun', '1153')->where('user_id', $userId)->first();

        if (!$coaSusu || !$coaKeju || !$coaKemasan) {
            if ($this->command) {
                $this->command->error('COA Persediaan Bahan Pendukung not found. Please run DefaultCoaSeeder first.');
            }
            return;
        }

        // Default Bahan Pendukung untuk Jasuke
        $bahanPendukungData = [
            [
                'nama_bahan' => 'Susu Kental Manis',
                'satuan_id' => $satuanLiter->id,
                'harga_satuan' => 25000, // Rp 25.000 per liter
                'stok_minimum' => 5,
                'saldo_awal' => 0,
                'coa_persediaan_id' => $coaSusu->kode_akun,
            ],
            [
                'nama_bahan' => 'Keju Parut',
                'satuan_id' => $satuanGram->id,
                'harga_satuan' => 150, // Rp 150 per gram
                'stok_minimum' => 500,
                'saldo_awal' => 0,
                'coa_persediaan_id' => $coaKeju->kode_akun,
            ],
            [
                'nama_bahan' => 'Cup Plastik',
                'satuan_id' => $satuanPcs->id,
                'harga_satuan' => 500, // Rp 500 per pcs
                'stok_minimum' => 100,
                'saldo_awal' => 0,
                'coa_persediaan_id' => $coaKemasan->kode_akun,
            ],
        ];

        foreach ($bahanPendukungData as $data) {
            BahanPendukung::create(array_merge($data, [
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        if ($this->command) {
            $this->command->info("✅ Default Bahan Pendukung Jasuke created for user ID: {$userId}");
        }
    }
}
