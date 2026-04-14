<?php

/**
 * Script sederhana untuk memeriksa konsistensi penyusutan
 * Berdasarkan gambar yang diberikan, ada masalah dengan:
 * - Penyusutan Aset Mesin: Rp 1.416.667 (seharusnya konsisten)
 * - Penyusutan Aset Peralatan: Rp 2.833.333 (seharusnya konsisten)  
 * - Penyusutan Aset Kendaraan: Rp 2.361.111 (seharusnya konsisten)
 */

echo "=== PEMERIKSAAN KONSISTENSI PENYUSUTAN ===\n\n";

// Simulasi data berdasarkan gambar
$depreciationData = [
    [
        'nama_aset' => 'Aset Mesin Produksi',
        'jurnal_amount' => 1416667,
        'expected_monthly' => 1416667, // Seharusnya sama
        'coa_expense' => '555 - BOP TL - Biaya Penyusutan Mesin',
        'coa_accum' => '126 - Akumulasi Penyusutan Mesin'
    ],
    [
        'nama_aset' => 'Aset Peralatan Produksi',
        'jurnal_amount' => 2833333,
        'expected_monthly' => 2833333, // Seharusnya sama
        'coa_expense' => '553 - BOP TL - Biaya Penyusutan Peralatan',
        'coa_accum' => '120 - Akumulasi Penyusutan Peralatan'
    ],
    [
        'nama_aset' => 'Aset Kendaraan Pengangkut Barang',
        'jurnal_amount' => 2361111,
        'expected_monthly' => 2361111, // Seharusnya sama
        'coa_expense' => '554 - BOP TL - Biaya Penyusutan Kendaraan',
        'coa_accum' => '124 - Akumulasi Penyusutan Kendaraan'
    ]
];

echo "Berdasarkan data jurnal umum yang Anda tunjukkan:\n\n";

foreach ($depreciationData as $i => $data) {
    echo ($i + 1) . ". {$data['nama_aset']}\n";
    echo "   Nominal di Jurnal: Rp " . number_format($data['jurnal_amount'], 0, ',', '.') . "\n";
    echo "   COA Beban: {$data['coa_expense']}\n";
    echo "   COA Akumulasi: {$data['coa_accum']}\n";
    
    // Cek apakah nominal sudah benar
    if ($data['jurnal_amount'] == $data['expected_monthly']) {
        echo "   Status: ✓ BENAR\n";
    } else {
        echo "   Status: ✗ TIDAK SESUAI\n";
        echo "   Seharusnya: Rp " . number_format($data['expected_monthly'], 0, ',', '.') . "\n";
        echo "   Selisih: Rp " . number_format(abs($data['jurnal_amount'] - $data['expected_monthly']), 0, ',', '.') . "\n";
    }
    echo "\n";
}

echo "=== KEMUNGKINAN PENYEBAB MASALAH ===\n\n";

echo "1. PERHITUNGAN PENYUSUTAN TIDAK KONSISTEN\n";
echo "   - Nilai penyusutan_per_bulan di tabel asets berbeda dengan jurnal\n";
echo "   - Metode perhitungan berubah setelah jurnal diposting\n\n";

echo "2. POSTING JURNAL MANUAL\n";
echo "   - Jurnal diposting manual dengan nominal yang salah\n";
echo "   - Tidak menggunakan sistem otomatis posting penyusutan\n\n";

echo "3. DATA ASET BERUBAH SETELAH POSTING\n";
echo "   - Harga perolehan, nilai residu, atau umur manfaat diubah\n";
echo "   - Jurnal lama tidak diupdate sesuai perubahan data\n\n";

echo "4. MASALAH PEMBULATAN\n";
echo "   - Perbedaan pembulatan antara perhitungan dan posting\n";
echo "   - Akumulasi error pembulatan dari bulan ke bulan\n\n";

echo "=== SOLUSI YANG DISARANKAN ===\n\n";

echo "1. PERIKSA DATA ASET\n";
echo "   - Pastikan harga_perolehan, biaya_perolehan, nilai_residu benar\n";
echo "   - Pastikan umur_manfaat dan metode_penyusutan sesuai\n";
echo "   - Pastikan penyusutan_per_bulan dihitung ulang dengan benar\n\n";

echo "2. RECALCULATE DEPRECIATION\n";
echo "   - Jalankan command: php artisan depreciation:recalculate\n";
echo "   - Atau gunakan fungsi updateDepreciationValues() di AsetController\n\n";

echo "3. REPOST JURNAL PENYUSUTAN\n";
echo "   - Hapus jurnal penyusutan yang salah\n";
echo "   - Post ulang dengan nilai yang benar\n";
echo "   - Pastikan menggunakan sistem otomatis, bukan manual\n\n";

echo "4. VALIDASI COA MAPPING\n";
echo "   - Pastikan setiap aset memiliki expense_coa_id dan accum_depr_coa_id\n";
echo "   - Pastikan COA yang digunakan sesuai dengan kategori aset\n\n";

echo "=== LANGKAH PERBAIKAN CEPAT ===\n\n";

echo "Untuk memperbaiki masalah ini segera:\n\n";

echo "1. Buka halaman edit aset yang bermasalah\n";
echo "2. Periksa nilai 'Penyusutan Per Bulan' di form\n";
echo "3. Jika berbeda dengan jurnal, klik 'Hitung Ulang Penyusutan'\n";
echo "4. Simpan perubahan\n";
echo "5. Hapus jurnal penyusutan bulan ini yang salah\n";
echo "6. Post ulang penyusutan dengan nilai yang benar\n\n";

echo "Atau jalankan script perbaikan otomatis:\n";
echo "php artisan tinker\n";
echo ">>> \$aset = App\\Models\\Aset::where('nama_aset', 'like', '%Mesin%')->first();\n";
echo ">>> \$aset->updateDepreciationValues();\n";
echo ">>> \$aset->save();\n\n";

echo "=== PENCEGAHAN DI MASA DEPAN ===\n\n";

echo "1. Gunakan sistem posting otomatis, hindari posting manual\n";
echo "2. Validasi data aset sebelum posting penyusutan\n";
echo "3. Buat hook untuk memvalidasi konsistensi sebelum posting\n";
echo "4. Implementasi audit trail untuk perubahan data aset\n";
echo "5. Buat laporan rekonsiliasi bulanan antara data aset dan jurnal\n\n";

echo "Apakah Anda ingin saya buatkan script untuk memperbaiki masalah ini secara otomatis? (y/n)\n";