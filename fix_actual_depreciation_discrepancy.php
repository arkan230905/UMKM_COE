<?php

/**
 * Script untuk memperbaiki ketidaksesuaian penyusutan berdasarkan data aktual
 * 
 * Data Aktual April 2026:
 * - Mesin Produksi: Rp 1.333.333
 * - Peralatan Produksi: Rp 659.474
 * - Kendaraan: Rp 888.889
 * 
 * Data di Jurnal (sebelumnya):
 * - Mesin Produksi: Rp 1.416.667 (selisih: +Rp 83.334)
 * - Peralatan Produksi: Rp 2.833.333 (selisih: +Rp 2.173.859)
 * - Kendaraan: Rp 2.361.111 (selisih: +Rp 1.472.222)
 */

echo "=== PERBAIKAN KETIDAKSESUAIAN PENYUSUTAN ===\n\n";

// Data aktual dari sistem
$actualData = [
    [
        'nama_aset' => 'Mesin Produksi',
        'actual_monthly' => 1333333,
        'journal_monthly' => 1416667,
        'difference' => 83334,
        'coa_expense' => '555',
        'coa_accum' => '126'
    ],
    [
        'nama_aset' => 'Peralatan Produksi',
        'actual_monthly' => 659474,
        'journal_monthly' => 2833333,
        'difference' => 2173859,
        'coa_expense' => '553',
        'coa_accum' => '120'
    ],
    [
        'nama_aset' => 'Kendaraan',
        'actual_monthly' => 888889,
        'journal_monthly' => 2361111,
        'difference' => 1472222,
        'coa_expense' => '554',
        'coa_accum' => '124'
    ]
];

echo "ANALISIS KETIDAKSESUAIAN:\n\n";

$totalDifference = 0;
foreach ($actualData as $i => $data) {
    echo ($i + 1) . ". {$data['nama_aset']}\n";
    echo "   Penyusutan Aktual: Rp " . number_format($data['actual_monthly'], 0, ',', '.') . "\n";
    echo "   Penyusutan di Jurnal: Rp " . number_format($data['journal_monthly'], 0, ',', '.') . "\n";
    echo "   Selisih: Rp " . number_format($data['difference'], 0, ',', '.') . "\n";
    echo "   Persentase Error: " . round(($data['difference'] / $data['actual_monthly']) * 100, 1) . "%\n";
    
    $totalDifference += $data['difference'];
    echo "\n";
}

echo "TOTAL SELISIH: Rp " . number_format($totalDifference, 0, ',', '.') . "\n\n";

echo "=== KEMUNGKINAN PENYEBAB ===\n\n";

echo "1. JURNAL DIPOSTING DENGAN NILAI LAMA\n";
echo "   - Data aset sudah diupdate tapi jurnal belum dikoreksi\n";
echo "   - Sistem masih menggunakan nilai penyusutan yang lama\n\n";

echo "2. POSTING JURNAL MANUAL YANG SALAH\n";
echo "   - Jurnal diinput manual dengan nominal yang tidak sesuai\n";
echo "   - Tidak menggunakan sistem otomatis posting penyusutan\n\n";

echo "3. DATA ASET BERUBAH SETELAH JURNAL DIPOSTING\n";
echo "   - Harga perolehan, nilai residu, atau umur manfaat direvisi\n";
echo "   - Jurnal April belum diupdate sesuai data terbaru\n\n";

echo "=== LANGKAH PERBAIKAN ===\n\n";

echo "LANGKAH 1: HAPUS JURNAL PENYUSUTAN APRIL 2026 YANG SALAH\n\n";

foreach ($actualData as $data) {
    echo "DELETE FROM jurnal_umum \n";
    echo "WHERE keterangan LIKE '%Penyusutan%{$data['nama_aset']}%' \n";
    echo "  AND tanggal = '2026-04-30' \n";
    echo "  AND debit = {$data['journal_monthly']};\n\n";
}

echo "LANGKAH 2: PASTIKAN DATA ASET SUDAH BENAR\n\n";

foreach ($actualData as $data) {
    echo "-- Verifikasi {$data['nama_aset']}\n";
    echo "SELECT \n";
    echo "    nama_aset,\n";
    echo "    harga_perolehan,\n";
    echo "    biaya_perolehan,\n";
    echo "    nilai_residu,\n";
    echo "    umur_manfaat,\n";
    echo "    penyusutan_per_bulan,\n";
    echo "    ROUND((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / (umur_manfaat * 12), 2) as penyusutan_seharusnya\n";
    echo "FROM asets \n";
    echo "WHERE nama_aset LIKE '%{$data['nama_aset']}%';\n\n";
}

echo "LANGKAH 3: UPDATE PENYUSUTAN_PER_BULAN JIKA PERLU\n\n";

foreach ($actualData as $data) {
    echo "-- Update {$data['nama_aset']} jika penyusutan_per_bulan tidak sesuai\n";
    echo "UPDATE asets \n";
    echo "SET penyusutan_per_bulan = {$data['actual_monthly']},\n";
    echo "    penyusutan_per_tahun = " . ($data['actual_monthly'] * 12) . "\n";
    echo "WHERE nama_aset LIKE '%{$data['nama_aset']}%'\n";
    echo "  AND ABS(penyusutan_per_bulan - {$data['actual_monthly']}) > 0.01;\n\n";
}

echo "LANGKAH 4: POST ULANG JURNAL DENGAN NILAI YANG BENAR\n\n";

foreach ($actualData as $data) {
    echo "-- Jurnal Penyusutan {$data['nama_aset']} - April 2026\n";
    echo "INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi) \n";
    echo "SELECT \n";
    echo "    (SELECT id FROM coa WHERE kode_akun = '{$data['coa_expense']}') as coa_id,\n";
    echo "    '2026-04-30' as tanggal,\n";
    echo "    'Penyusutan Aset {$data['nama_aset']} (GL) 2026-04' as keterangan,\n";
    echo "    {$data['actual_monthly']} as debit,\n";
    echo "    0 as kredit,\n";
    echo "    'DEPR-" . date('Ymd') . "' as referensi,\n";
    echo "    'depr' as tipe_referensi;\n\n";
    
    echo "INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi) \n";
    echo "SELECT \n";
    echo "    (SELECT id FROM coa WHERE kode_akun = '{$data['coa_accum']}') as coa_id,\n";
    echo "    '2026-04-30' as tanggal,\n";
    echo "    'Penyusutan Aset {$data['nama_aset']} (GL) 2026-04' as keterangan,\n";
    echo "    0 as debit,\n";
    echo "    {$data['actual_monthly']} as kredit,\n";
    echo "    'DEPR-" . date('Ymd') . "' as referensi,\n";
    echo "    'depr' as tipe_referensi;\n\n";
}

echo "LANGKAH 5: UPDATE AKUMULASI PENYUSUTAN DI TABEL ASET\n\n";

foreach ($actualData as $data) {
    echo "-- Update akumulasi penyusutan {$data['nama_aset']}\n";
    echo "UPDATE asets a\n";
    echo "SET akumulasi_penyusutan = (\n";
    echo "    SELECT COALESCE(SUM(ju.debit), 0)\n";
    echo "    FROM jurnal_umum ju\n";
    echo "    JOIN coa c ON ju.coa_id = c.id\n";
    echo "    WHERE c.kode_akun = '{$data['coa_expense']}'\n";
    echo "      AND ju.keterangan LIKE '%{$data['nama_aset']}%'\n";
    echo "      AND ju.debit > 0\n";
    echo ")\n";
    echo "WHERE a.nama_aset LIKE '%{$data['nama_aset']}%';\n\n";
}

echo "LANGKAH 6: UPDATE NILAI BUKU\n\n";

foreach ($actualData as $data) {
    echo "-- Update nilai buku {$data['nama_aset']}\n";
    echo "UPDATE asets \n";
    echo "SET nilai_buku = GREATEST(\n";
    echo "    (harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(akumulasi_penyusutan, 0)),\n";
    echo "    COALESCE(nilai_residu, 0)\n";
    echo ")\n";
    echo "WHERE nama_aset LIKE '%{$data['nama_aset']}%';\n\n";
}

echo "=== VALIDASI HASIL ===\n\n";

echo "-- Periksa konsistensi data setelah perbaikan\n";
echo "SELECT \n";
echo "    a.nama_aset,\n";
echo "    a.penyusutan_per_bulan,\n";
echo "    COALESCE(SUM(CASE WHEN ju.debit > 0 THEN ju.debit ELSE 0 END), 0) as total_jurnal_debit,\n";
echo "    a.akumulasi_penyusutan,\n";
echo "    a.nilai_buku,\n";
echo "    CASE \n";
echo "        WHEN ABS(a.penyusutan_per_bulan - COALESCE(AVG(CASE WHEN ju.debit > 0 THEN ju.debit ELSE NULL END), 0)) < 0.01 \n";
echo "        THEN 'KONSISTEN' \n";
echo "        ELSE 'TIDAK KONSISTEN' \n";
echo "    END as status\n";
echo "FROM asets a\n";
echo "LEFT JOIN jurnal_umum ju ON ju.keterangan LIKE CONCAT('%', a.nama_aset, '%') AND ju.keterangan LIKE '%Penyusutan%'\n";
echo "WHERE a.nama_aset IN ('Mesin Produksi', 'Peralatan Produksi', 'Kendaraan')\n";
echo "GROUP BY a.id, a.nama_aset, a.penyusutan_per_bulan, a.akumulasi_penyusutan, a.nilai_buku\n";
echo "ORDER BY a.nama_aset;\n\n";

echo "=== PENCEGAHAN DI MASA DEPAN ===\n\n";

echo "1. GUNAKAN SISTEM OTOMATIS\n";
echo "   - Selalu gunakan fitur 'Post Monthly Depreciation'\n";
echo "   - Jangan input jurnal penyusutan secara manual\n\n";

echo "2. VALIDASI SEBELUM POSTING\n";
echo "   - Periksa nilai penyusutan_per_bulan sebelum posting\n";
echo "   - Pastikan COA mapping sudah benar\n\n";

echo "3. IMPLEMENTASI KONTROL\n";
echo "   - Buat validasi otomatis sebelum posting jurnal\n";
echo "   - Implementasi approval workflow untuk perubahan data aset\n\n";

echo "4. MONITORING RUTIN\n";
echo "   - Buat laporan rekonsiliasi bulanan\n";
echo "   - Monitor selisih antara data aset dan jurnal\n\n";

echo "CATATAN PENTING:\n";
echo "- Backup database sebelum menjalankan script\n";
echo "- Jalankan di environment testing terlebih dahulu\n";
echo "- Verifikasi setiap langkah sebelum melanjutkan\n";
echo "- Koordinasi dengan tim accounting sebelum koreksi\n\n";