<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnsurePenjualanCoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Memastikan semua COA yang diperlukan untuk penjualan ada
     */
    public function run(): void
    {
        $requiredCoas = [
            // Kas & Bank (Asset)
            [
                'kode_akun' => '1101',
                'nama_akun' => 'Kas',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Kas & Bank',
            ],
            [
                'kode_akun' => '1102',
                'nama_akun' => 'Kas di Bank',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Kas & Bank',
            ],
            [
                'kode_akun' => '1107',
                'nama_akun' => 'Persediaan Barang Jadi',
                'tipe_akun' => 'Asset',
                'kategori_akun' => 'Persediaan',
            ],
            
            // Penjualan (Revenue)
            [
                'kode_akun' => '4101',
                'nama_akun' => 'Penjualan',
                'tipe_akun' => 'Revenue',
                'kategori_akun' => 'Pendapatan',
            ],
            
            // HPP (Expense)
            [
                'kode_akun' => '5101',
                'nama_akun' => 'Harga Pokok Penjualan',
                'tipe_akun' => 'Expense',
                'kategori_akun' => 'Harga Pokok Penjualan',
            ],
        ];

        foreach ($requiredCoas as $coa) {
            // Check if COA already exists
            $existing = DB::table('coas')->where('kode_akun', $coa['kode_akun'])->first();
            
            if (!$existing) {
                DB::table('coas')->insert([
                    'kode_akun' => $coa['kode_akun'],
                    'nama_akun' => $coa['nama_akun'],
                    'tipe_akun' => $coa['tipe_akun'],
                    'kategori_akun' => $coa['kategori_akun'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info("✓ Created COA: {$coa['kode_akun']} - {$coa['nama_akun']}");
            } else {
                $this->command->info("- COA already exists: {$coa['kode_akun']} - {$existing->nama_akun}");
            }
        }
        
        $this->command->info("✅ All required COA for penjualan are now available");
    }
}