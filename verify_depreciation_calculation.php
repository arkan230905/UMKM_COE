<?php

/**
 * Script untuk memverifikasi perhitungan penyusutan berdasarkan data di gambar
 * 
 * Dari gambar terlihat:
 * - Penyusutan Aset Mesin: Rp 1.416.667
 * - Penyusutan Aset Peralatan: Rp 2.833.333  
 * - Penyusutan Aset Kendaraan: Rp 2.361.111
 * 
 * Perlu diverifikasi apakah nominal ini sesuai dengan data aset
 */

echo "=== VERIFIKASI PERHITUNGAN PENYUSUTAN ===\n\n";

// Data dari gambar jurnal umum
$journalData = [
    [
        'nama_aset' => 'Aset Mesin Produksi',
        'jurnal_debit' => 1416667,
        'coa_beban' => '555',
        'coa_akumulasi' => '126',
        'tanggal' => '2026-04-30'
    ],
    [
        'nama_aset' => 'Aset Peralatan Produksi', 
        'jurnal_debit' => 2833333,
        'coa_beban' => '553',
        'coa_akumulasi' => '120',
        'tanggal' => '2026-04-30'
    ],
    [
        'nama_aset' => 'Aset Kendaraan Pengangkut Barang',
        'jurnal_debit' => 2361111,
        'coa_beban' => '554', 
        'coa_akumulasi' => '124',
        'tanggal' => '2026-04-30'
    ]
];

echo "Data dari Jurnal Umum (30/04/2026):\n\n";

foreach ($journalData as $i => $data) {
    echo ($i + 1) . ". {$data['nama_aset']}\n";
    echo "   Nominal Jurnal: Rp " . number_format($data['jurnal_debit'], 0, ',', '.') . "\n";
    echo "   COA Beban: {$data['coa_beban']}\n";
    echo "   COA Akumulasi: {$data['coa_akumulasi']}\n\n";
}

echo "=== KEMUNGKINAN PERHITUNGAN YANG BENAR ===\n\n";

// Simulasi perhitungan berdasarkan nominal jurnal
foreach ($journalData as $i => $data) {
    echo ($i + 1) . ". {$data['nama_aset']}\n";
    
    $monthlyDepreciation = $data['jurnal_debit'];
    $yearlyDepreciation = $monthlyDepreciation * 12;
    
    echo "   Penyusutan per bulan: Rp " . number_format($monthlyDepreciation, 0, ',', '.') . "\n";
    echo "   Penyusutan per tahun: Rp " . number_format($yearlyDepreciation, 0, ',', '.') . "\n";
    
    // Estimasi data aset berdasarkan penyusutan
    // Asumsi metode garis lurus dan umur manfaat 5 tahun
    $estimatedDepreciableValue = $yearlyDepreciation * 5; // 5 tahun
    
    echo "   Estimasi nilai yang disusutkan: Rp " . number_format($estimatedDepreciableValue, 0, ',', '.') . "\n";
    
    // Kemungkinan harga perolehan (asumsi nilai residu 10%)
    $estimatedAcquisitionCost = $estimatedDepreciableValue / 0.9; // 90% disusutkan
    
    echo "   Estimasi harga perolehan: Rp " . number_format($estimatedAcquisitionCost, 0, ',', '.') . "\n";
    echo "   Estimasi nilai residu (10%): Rp " . number_format($estimatedAcquisitionCost * 0.1, 0, ',', '.') . "\n";
    
    echo "\n";
}

echo "=== FORMULA VALIDASI ===\n\n";

echo "Untuk memverifikasi apakah perhitungan benar:\n\n";

echo "1. METODE GARIS LURUS:\n";
echo "   Penyusutan per bulan = (Harga Perolehan + Biaya Perolehan - Nilai Residu) / (Umur Manfaat × 12)\n\n";

echo "2. CONTOH VALIDASI ASET MESIN:\n";
echo "   Jika penyusutan = Rp 1.416.667/bulan\n";
echo "   Maka per tahun = Rp 17.000.004\n";
echo "   Jika umur manfaat = 5 tahun\n";
echo "   Maka nilai disusutkan = Rp 85.000.020\n";
echo "   Jika nilai residu = 10% dari harga perolehan\n";
echo "   Maka harga perolehan ≈ Rp 94.444.467\n\n";

echo "3. VALIDASI DENGAN DATA AKTUAL:\n";
echo "   - Buka halaman detail aset di sistem\n";
echo "   - Periksa: Harga Perolehan, Biaya Perolehan, Nilai Residu, Umur Manfaat\n";
echo "   - Hitung manual: (HP + BP - NR) / (UM × 12)\n";
echo "   - Bandingkan dengan nilai di jurnal\n\n";

echo "=== SCRIPT VALIDASI OTOMATIS ===\n\n";

echo "Untuk validasi otomatis, jalankan query berikut di database:\n\n";

echo "SELECT \n";
echo "    a.id,\n";
echo "    a.nama_aset,\n";
echo "    a.harga_perolehan,\n";
echo "    a.biaya_perolehan,\n";
echo "    a.nilai_residu,\n";
echo "    a.umur_manfaat,\n";
echo "    a.metode_penyusutan,\n";
echo "    a.penyusutan_per_bulan as stored_monthly,\n";
echo "    ROUND(((a.harga_perolehan + COALESCE(a.biaya_perolehan, 0) - COALESCE(a.nilai_residu, 0)) / (a.umur_manfaat * 12)), 2) as calculated_monthly,\n";
echo "    ABS(a.penyusutan_per_bulan - ((a.harga_perolehan + COALESCE(a.biaya_perolehan, 0) - COALESCE(a.nilai_residu, 0)) / (a.umur_manfaat * 12))) as difference\n";
echo "FROM asets a \n";
echo "WHERE a.metode_penyusutan IS NOT NULL \n";
echo "AND a.umur_manfaat > 0\n";
echo "AND a.nama_aset LIKE '%Mesin%' OR a.nama_aset LIKE '%Peralatan%' OR a.nama_aset LIKE '%Kendaraan%'\n";
echo "ORDER BY difference DESC;\n\n";

echo "=== LANGKAH PERBAIKAN ===\n\n";

echo "Jika ditemukan ketidaksesuaian:\n\n";

echo "1. PERIKSA DATA ASET:\n";
echo "   UPDATE asets SET \n";
echo "       penyusutan_per_bulan = ROUND(((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / (umur_manfaat * 12)), 2),\n";
echo "       penyusutan_per_tahun = ROUND(((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / umur_manfaat), 2)\n";
echo "   WHERE metode_penyusutan = 'garis_lurus' AND umur_manfaat > 0;\n\n";

echo "2. VERIFIKASI JURNAL:\n";
echo "   SELECT \n";
echo "       ju.tanggal,\n";
echo "       ju.keterangan,\n";
echo "       ju.debit,\n";
echo "       c.kode_akun,\n";
echo "       c.nama_akun\n";
echo "   FROM jurnal_umum ju\n";
echo "   JOIN coa c ON ju.coa_id = c.id\n";
echo "   WHERE ju.keterangan LIKE '%Penyusutan%'\n";
echo "   AND ju.tanggal = '2026-04-30'\n";
echo "   ORDER BY ju.debit DESC;\n\n";

echo "3. KOREKSI JURNAL (JIKA DIPERLUKAN):\n";
echo "   - Hapus jurnal penyusutan yang salah\n";
echo "   - Post ulang dengan nilai yang benar dari tabel asets\n";
echo "   - Pastikan menggunakan COA yang tepat\n\n";

echo "=== PENCEGAHAN ===\n\n";

echo "Untuk mencegah masalah serupa:\n\n";
echo "1. Selalu gunakan sistem otomatis untuk posting penyusutan\n";
echo "2. Validasi data aset sebelum posting\n";
echo "3. Buat constraint di database untuk memastikan konsistensi\n";
echo "4. Implementasi audit trail untuk perubahan data penyusutan\n";
echo "5. Buat laporan rekonsiliasi bulanan\n\n";

echo "Apakah Anda ingin saya buatkan script untuk memperbaiki data aset dan jurnal secara otomatis?\n";