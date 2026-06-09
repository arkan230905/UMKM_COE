<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanBaku;
use App\Models\Satuan;
use App\Models\Coa;

class DefaultBahanBakuSeeder extends Seeder
{
    /**
     * Seed default bahan baku with COA mapping for new user
     */
    public function run(int $userId): void
    {
        // Get satuan IDs for this user
        $ekor = Satuan::where('nama', 'Ekor')->where('user_id', $userId)->first();
        
        if (!$ekor) {
            \Log::warning("Satuan 'Ekor' not found for user {$userId}, skipping bahan baku seeding");
            return;
        }

        // Get COA codes for this user
        $coaAyamPotong = Coa::where('kode_akun', '1141')->where('user_id', $userId)->first();
        $coaAyamKampung = Coa::where('kode_akun', '1142')->where('user_id', $userId)->first();
        $coaBebek = Coa::where('kode_akun', '1143')->where('user_id', $userId)->first();

        // 1. Ayam Potong
        BahanBaku::create([
            'user_id' => $userId,
            'nama_bahan' => 'Ayam Potong',
            'kode_bahan' => 'BB-AYAM-POTONG',
            'satuan_id' => $ekor->id,
            'harga_satuan' => 40000.00,
            'stok' => 0,
            'stok_minimum' => 10.00,
            'deskripsi' => 'Ayam potong berkualitas untuk produksi',
            'coa_persediaan_id' => $coaAyamPotong ? $coaAyamPotong->kode_akun : '1141',
        ]);

        // 2. Ayam Kampung
        BahanBaku::create([
            'user_id' => $userId,
            'nama_bahan' => 'Ayam Kampung',
            'kode_bahan' => 'BB-AYAM-KAMPUNG',
            'satuan_id' => $ekor->id,
            'harga_satuan' => 60000.00,
            'stok' => 0,
            'stok_minimum' => 5.00,
            'deskripsi' => 'Ayam kampung berkualitas untuk produksi',
            'coa_persediaan_id' => $coaAyamKampung ? $coaAyamKampung->kode_akun : '1142',
        ]);

        // 3. Bebek
        BahanBaku::create([
            'user_id' => $userId,
            'nama_bahan' => 'Bebek',
            'kode_bahan' => 'BB-BEBEK',
            'satuan_id' => $ekor->id,
            'harga_satuan' => 70000.00,
            'stok' => 0,
            'stok_minimum' => 5.00,
            'deskripsi' => 'Bebek berkualitas untuk produksi',
            'coa_persediaan_id' => $coaBebek ? $coaBebek->kode_akun : '1143',
        ]);

        \Log::info("Default bahan baku seeded for user {$userId}", [
            'bahan_baku_count' => 3,
            'with_coa' => true
        ]);
    }
}
