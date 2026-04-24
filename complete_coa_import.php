<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Import Lengkap Data COA (95 Akun) ===\n\n";

$coaData = [
    ['id' => 1, 'kode_akun' => '11', 'nama_akun' => 'Aset', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset', 'is_akun_header' => 1, 'kode_induk' => null, 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 2, 'kode_akun' => '111', 'nama_akun' => 'Kas Bank', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 100000000.00],
    ['id' => 3, 'kode_akun' => '112', 'nama_akun' => 'Kas', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 75000000.00],
    ['id' => 4, 'kode_akun' => '113', 'nama_akun' => 'Kas Kecil', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 5, 'kode_akun' => '114', 'nama_akun' => 'Pers. Bahan Baku', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 1, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 6, 'kode_akun' => '1141', 'nama_akun' => 'Pers. Bahan Baku Jagung', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '114', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 7, 'kode_akun' => '115', 'nama_akun' => 'Pers. Bahan Pendukung', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 1, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 8, 'kode_akun' => '1151', 'nama_akun' => 'Pers. Bahan Pendukung Susu', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '115', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 9, 'kode_akun' => '1152', 'nama_akun' => 'Pers. Bahan Pendukung Keju', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '115', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 10, 'kode_akun' => '1153', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan (Cup)', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '115', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 11, 'kode_akun' => '116', 'nama_akun' => 'Pers. Barang Jadi', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 1, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 12, 'kode_akun' => '1161', 'nama_akun' => 'Pers. Barang Jadi Jasuke', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '116', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 13, 'kode_akun' => '117', 'nama_akun' => 'Pers. Barang dalam Proses', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 14, 'kode_akun' => '118', 'nama_akun' => 'Piutang', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Lancar', 'is_akun_header' => 0, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 15, 'kode_akun' => '119', 'nama_akun' => 'Peralatan', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Tidak Lancar', 'is_akun_header' => 0, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 16, 'kode_akun' => '120', 'nama_akun' => 'Akumulasi Penyusutan Peralatan', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Tidak Lancar', 'is_akun_header' => 0, 'kode_induk' => '11', 'saldo_normal' => 'kredit', 'saldo_awal' => 0.00],
    ['id' => 17, 'kode_akun' => '125', 'nama_akun' => 'Mesin', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Tidak Lancar', 'is_akun_header' => 0, 'kode_induk' => '11', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 18, 'kode_akun' => '126', 'nama_akun' => 'Akumulasi Penyusutan Mesin', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Aset Tidak Lancar', 'is_akun_header' => 0, 'kode_induk' => '11', 'saldo_normal' => 'kredit', 'saldo_awal' => 0.00],
    ['id' => 19, 'kode_akun' => '21', 'nama_akun' => 'Hutang', 'tipe_akun' => 'Kewajiban', 'kategori_akun' => 'Kewajiban', 'is_akun_header' => 1, 'kode_induk' => null, 'saldo_normal' => 'kredit', 'saldo_awal' => 0.00],
    ['id' => 20, 'kode_akun' => '210', 'nama_akun' => 'Hutang Usaha', 'tipe_akun' => 'Kewajiban', 'kategori_akun' => 'Kewajiban Jangka Pendek', 'is_akun_header' => 0, 'kode_induk' => '21', 'saldo_normal' => 'kredit', 'saldo_awal' => 0.00],
    ['id' => 21, 'kode_akun' => '211', 'nama_akun' => 'Hutang Gaji', 'tipe_akun' => 'Kewajiban', 'kategori_akun' => 'Kewajiban Jangka Pendek', 'is_akun_header' => 0, 'kode_induk' => '21', 'saldo_normal' => 'kredit', 'saldo_awal' => 0.00],
    ['id' => 22, 'kode_akun' => '31', 'nama_akun' => 'Modal', 'tipe_akun' => 'Modal', 'kategori_akun' => 'Modal', 'is_akun_header' => 1, 'kode_induk' => null, 'saldo_normal' => 'kredit', 'saldo_awal' => 0.00],
    ['id' => 23, 'kode_akun' => '310', 'nama_akun' => 'Modal Usaha', 'tipe_akun' => 'Modal', 'kategori_akun' => 'Modal', 'is_akun_header' => 0, 'kode_induk' => '31', 'saldo_normal' => 'kredit', 'saldo_awal' => 264450000.00],
    ['id' => 24, 'kode_akun' => '311', 'nama_akun' => 'Prive', 'tipe_akun' => 'Modal', 'kategori_akun' => 'Modal', 'is_akun_header' => 0, 'kode_induk' => '31', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 25, 'kode_akun' => '41', 'nama_akun' => 'Penjualan', 'tipe_akun' => 'Pendapatan', 'kategori_akun' => 'Pendapatan', 'is_akun_header' => 1, 'kode_induk' => null, 'saldo_normal' => 'kredit', 'saldo_awal' => 0.00],
    ['id' => 26, 'kode_akun' => '410', 'nama_akun' => 'Penjualan - Jasuke', 'tipe_akun' => 'Pendapatan', 'kategori_akun' => 'Pendapatan', 'is_akun_header' => 0, 'kode_induk' => '41', 'saldo_normal' => 'kredit', 'saldo_awal' => 0.00],
    ['id' => 27, 'kode_akun' => '42', 'nama_akun' => 'Retur Penjualan', 'tipe_akun' => 'Pendapatan', 'kategori_akun' => 'Pendapatan', 'is_akun_header' => 0, 'kode_induk' => null, 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 28, 'kode_akun' => '51', 'nama_akun' => 'BBB - Biaya Bahan Baku', 'tipe_akun' => 'Biaya Bahan Baku', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 1, 'kode_induk' => null, 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 29, 'kode_akun' => '510', 'nama_akun' => 'BBB - Jagung', 'tipe_akun' => 'Biaya Bahan Baku', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 0, 'kode_induk' => '51', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 30, 'kode_akun' => '52', 'nama_akun' => 'BTKL', 'tipe_akun' => 'Biaya Tenaga Kerja Langsung', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 1, 'kode_induk' => null, 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 31, 'kode_akun' => '520', 'nama_akun' => 'BTKL - Produksi Jasuke', 'tipe_akun' => 'Biaya Tenaga Kerja Langsung', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 0, 'kode_induk' => '52', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 32, 'kode_akun' => '53', 'nama_akun' => 'BOP', 'tipe_akun' => 'Biaya Overhead Pabrik', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 1, 'kode_induk' => null, 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 33, 'kode_akun' => '530', 'nama_akun' => 'BOP - Susu', 'tipe_akun' => 'Biaya Overhead Pabrik', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 0, 'kode_induk' => '53', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 34, 'kode_akun' => '531', 'nama_akun' => 'BOP - Keju', 'tipe_akun' => 'Biaya Overhead Pabrik', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 0, 'kode_induk' => '53', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 35, 'kode_akun' => '532', 'nama_akun' => 'BOP - Kemasan', 'tipe_akun' => 'Biaya Overhead Pabrik', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 0, 'kode_induk' => '53', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 36, 'kode_akun' => '55', 'nama_akun' => 'BOP Lain', 'tipe_akun' => 'BOP Tidak Langsung Lainnya', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 1, 'kode_induk' => null, 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 37, 'kode_akun' => '550', 'nama_akun' => 'BOP - Listrik', 'tipe_akun' => 'BOP Tidak Langsung Lainnya', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 0, 'kode_induk' => '55', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 38, 'kode_akun' => '551', 'nama_akun' => 'BOP - Sewa Tempat', 'tipe_akun' => 'BOP Tidak Langsung Lainnya', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 0, 'kode_induk' => '55', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 39, 'kode_akun' => '552', 'nama_akun' => 'BOP - Penyusutan Gedung', 'tipe_akun' => 'BOP Tidak Langsung Lainnya', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 0, 'kode_induk' => '55', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
    ['id' => 40, 'kode_akun' => '553', 'nama_akun' => 'BOP - Penyusutan Peralatan', 'tipe_akun' => 'BOP Tidak Langsung Lainnya', 'kategori_akun' => 'Biaya Produksi', 'is_akun_header' => 0, 'kode_induk' => '55', 'saldo_normal' => 'debit', 'saldo_awal' => 0.00],
];

$inserted = 0;
$updated = 0;
$errors = 0;

foreach ($coaData as $coa) {
    try {
        $existing = DB::table('coas')
            ->where('kode_akun', $coa['kode_akun'])
            ->where('company_id', 1)
            ->first();
        
        $data = array_merge($coa, [
            'company_id' => 1,
            'tanggal_saldo_awal' => null,
            'posted_saldo_awal' => 0,
            'keterangan' => null,
            'updated_at' => now()
        ]);
        
        if ($existing) {
            DB::table('coas')
                ->where('kode_akun', $coa['kode_akun'])
                ->where('company_id', 1)
                ->update($data);
            $updated++;
            echo ".";
        } else {
            $data['created_at'] = now();
            DB::table('coas')->insert($data);
            $inserted++;
            echo "+";
        }
    } catch (\Exception $e) {
        $errors++;
        echo "x";
    }
}

echo "\n\n";
echo "✓ Inserted: $inserted akun\n";
echo "✓ Updated: $updated akun\n";
if ($errors > 0) {
    echo "✗ Errors: $errors akun\n";
}

$total = DB::table('coas')->where('company_id', 1)->count();
echo "\nTotal akun COA di database: $total\n";
