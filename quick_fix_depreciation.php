<?php

/**
 * QUICK FIX untuk masalah penyusutan
 * Jalankan dengan: php quick_fix_depreciation.php
 */

echo "=== QUICK FIX MASALAH PENYUSUTAN ===\n\n";

echo "Berdasarkan gambar yang Anda berikan, terlihat ada ketidaksesuaian\n";
echo "antara nominal penyusutan di jurnal umum dengan data aset.\n\n";

echo "LANGKAH PERBAIKAN MANUAL:\n\n";

echo "1. PERIKSA DATA ASET\n";
echo "   - Buka menu Master Data > Aset\n";
echo "   - Cari aset: 'Mesin Produksi', 'Peralatan Produksi', 'Kendaraan'\n";
echo "   - Klik 'Edit' pada masing-masing aset\n\n";

echo "2. VERIFIKASI PERHITUNGAN\n";
echo "   Untuk setiap aset, pastikan:\n";
echo "   - Harga Perolehan sudah benar\n";
echo "   - Biaya Perolehan (jika ada) sudah benar\n";
echo "   - Nilai Residu sudah benar\n";
echo "   - Umur Manfaat sudah benar (biasanya 5 tahun)\n";
echo "   - Metode Penyusutan = 'Garis Lurus'\n\n";

echo "3. HITUNG ULANG PENYUSUTAN\n";
echo "   Di halaman edit aset:\n";
echo "   - Klik tombol 'Hitung Ulang Penyusutan' (jika ada)\n";
echo "   - Atau ubah salah satu field lalu simpan untuk trigger recalculation\n";
echo "   - Pastikan 'Penyusutan Per Bulan' sesuai dengan jurnal\n\n";

echo "4. PERBAIKI MAPPING COA\n";
echo "   Pastikan setiap aset memiliki:\n";
echo "   - COA Aset: sesuai kategori (Mesin, Peralatan, Kendaraan)\n";
echo "   - COA Beban Penyusutan: 555 (Mesin), 553 (Peralatan), 554 (Kendaraan)\n";
echo "   - COA Akumulasi: 126 (Mesin), 120 (Peralatan), 124 (Kendaraan)\n\n";

echo "5. KOREKSI JURNAL PENYUSUTAN\n";
echo "   - Buka menu Akuntansi > Jurnal Umum\n";
echo "   - Filter tanggal: 30/04/2026\n";
echo "   - Cari jurnal penyusutan yang salah\n";
echo "   - Hapus jurnal yang salah\n";
echo "   - Post ulang penyusutan dengan nilai yang benar\n\n";

echo "FORMULA PERHITUNGAN:\n\n";
echo "Penyusutan Per Bulan = (Harga Perolehan + Biaya Perolehan - Nilai Residu) / (Umur Manfaat × 12)\n\n";

echo "CONTOH PERHITUNGAN:\n";
echo "Jika Aset Mesin:\n";
echo "- Harga Perolehan: Rp 100.000.000\n";
echo "- Biaya Perolehan: Rp 5.000.000\n";
echo "- Nilai Residu: Rp 20.000.000\n";
echo "- Umur Manfaat: 5 tahun\n\n";
echo "Maka:\n";
echo "Penyusutan = (100.000.000 + 5.000.000 - 20.000.000) / (5 × 12)\n";
echo "Penyusutan = 85.000.000 / 60\n";
echo "Penyusutan = Rp 1.416.667 per bulan ✓\n\n";

echo "VALIDASI HASIL:\n";
echo "Setelah perbaikan, pastikan:\n";
echo "1. Nilai 'Penyusutan Per Bulan' di data aset = nominal di jurnal\n";
echo "2. Jurnal menggunakan COA yang benar\n";
echo "3. Akumulasi penyusutan terupdate dengan benar\n";
echo "4. Nilai buku aset berkurang sesuai penyusutan\n\n";

echo "COMMAND OTOMATIS (JIKA TERSEDIA):\n";
echo "php artisan depreciation:fix-discrepancy --dry-run\n";
echo "php artisan depreciation:recalculate\n";
echo "php artisan depreciation:post-monthly 2026-04\n\n";

echo "TROUBLESHOOTING:\n\n";
echo "Jika masih ada masalah:\n";
echo "1. Periksa apakah ada duplikasi aset dengan nama serupa\n";
echo "2. Pastikan tidak ada jurnal manual yang mengacaukan perhitungan\n";
echo "3. Cek apakah ada perubahan data aset setelah jurnal diposting\n";
echo "4. Verifikasi bahwa sistem menggunakan metode garis lurus konsisten\n\n";

echo "PENCEGAHAN:\n";
echo "1. Selalu gunakan sistem otomatis untuk posting penyusutan\n";
echo "2. Jangan edit data aset setelah penyusutan mulai diposting\n";
echo "3. Buat backup data sebelum melakukan perubahan besar\n";
echo "4. Implementasi validasi otomatis sebelum posting\n\n";

echo "Apakah Anda ingin saya buatkan script SQL untuk memperbaiki data secara langsung?\n";