<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aset;
use App\Models\KategoriAset;
use Illuminate\Support\Facades\DB;

class AssetTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding asset templates for new tenants...');
        
        // Template aset yang akan dikunci untuk semua tenant baru
        $assetTemplates = [
            [
                'kode_aset' => 'AST-TEMPLATE-0001',
                'nama_aset' => 'Peralatan Produksi',
                'kategori_aset_id' => 5, // Peralatan Produksi
                'harga_perolehan' => 85000000.00,
                'biaya_perolehan' => 0.00,
                'nilai_residu' => 5000000.00,
                'umur_manfaat' => 5,
                'penyusutan_per_tahun' => 34000000.00,
                'penyusutan_per_bulan' => 2833333.33,
                'nilai_buku' => 19784218.00, // Nilai buku per Maret 2026
                'akumulasi_penyusutan' => 65215782.29, // Akumulasi per Maret 2026
                'tanggal_beli' => '2022-09-12',
                'tanggal_akuisisi' => '2022-09-12',
                'tanggal_perolehan' => '2022-09-12',
                'status' => 'aktif',
                'metode_penyusutan' => 'saldo_menurun',
                'tarif_penyusutan' => 40.00,
                'bulan_mulai' => 1,
                'keterangan' => 'Template aset peralatan produksi untuk tenant baru',
                'asset_coa_id' => 31, // 119 - Peralatan
                'accum_depr_coa_id' => 32, // 120 - Akumulasi Penyusutan Peralatan
                'expense_coa_id' => 82, // 553 - BOP Penyusutan Peralatan
                'locked' => 1,
                'metode_tahun_pertama' => 'proporsional', // Proporsional sesuai bulan mulai
            ],
            [
                'kode_aset' => 'AST-TEMPLATE-0002',
                'nama_aset' => 'Kendaraan Pengangkut Barang',
                'kategori_aset_id' => 3, // Kendaraan
                'harga_perolehan' => 85000000.00,
                'biaya_perolehan' => 0.00,
                'nilai_residu' => 5000000.00,
                'umur_manfaat' => 5,
                'penyusutan_per_tahun' => 28333333.33,
                'penyusutan_per_bulan' => 2361111.11,
                'nilai_buku' => 85000000.00, // Belum ada penyusutan
                'akumulasi_penyusutan' => 0.00, // Belum ada penyusutan
                'tanggal_beli' => '2022-09-12',
                'tanggal_akuisisi' => '2022-09-12',
                'tanggal_perolehan' => '2022-09-12',
                'status' => 'aktif',
                'metode_penyusutan' => 'sum_of_years_digits',
                'tarif_penyusutan' => null,
                'bulan_mulai' => 1,
                'keterangan' => 'Template aset kendaraan untuk tenant baru',
                'asset_coa_id' => 35, // 123 - Kendaraan
                'accum_depr_coa_id' => 36, // 124 - Akumulasi Penyusutan Kendaraan
                'expense_coa_id' => 83, // 554 - BOP Penyusutan Kendaraan
                'locked' => 1,
                'metode_tahun_pertama' => 'proporsional', // Proporsional sesuai bulan mulai
            ]
        ];

        foreach ($assetTemplates as $template) {
            // Cek apakah template sudah ada
            $existing = DB::table('asets')
                ->where('kode_aset', $template['kode_aset'])
                ->first();
            
            if (!$existing) {
                DB::table('asets')->insert($template);
                $this->command->info("  ✓ Created template: {$template['nama_aset']}");
            } else {
                // Update existing template
                DB::table('asets')
                    ->where('kode_aset', $template['kode_aset'])
                    ->update($template);
                $this->command->info("  ✓ Updated template: {$template['nama_aset']}");
            }
        }

        // Pastikan aset asli juga terkunci
        DB::table('asets')
            ->whereIn('kode_aset', ['AST-202604-0001', 'AST-202604-0002'])
            ->update(['locked' => 1]);

        $this->command->info('Asset templates seeded successfully!');
        $this->command->info('Locked assets: AST-202604-0001, AST-202604-0002');
        $this->command->info('Template assets: AST-TEMPLATE-0001, AST-TEMPLATE-0002');
    }
}
