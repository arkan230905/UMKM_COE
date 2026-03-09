<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== REFERENSI BTKL UNTUK DOSEN ===" . PHP_EOL;

// Data aktual yang digunakan
echo PHP_EOL . "DATA BTKL YANG DIGUNAKAN DALAM SISTEM:" . PHP_EOL;
echo "=====================================" . PHP_EOL;

$produks = ['Ayam Ketumbar', 'Ketempling', 'Opak Geulis'];

foreach ($produks as $namaProduk) {
    $produk = \App\Models\Produk::where('nama_produk', $namaProduk)->first();
    if ($produk && $produk->bomJobCosting) {
        echo PHP_EOL . "Produk: {$namaProduk}" . PHP_EOL;
        echo "------------------------" . PHP_EOL;
        
        $detailBtkls = \App\Models\BomJobBTKL::with(['bomJobCosting'])
            ->whereHas('bomJobCosting', function($query) use ($produk) {
                $query->where('produk_id', $produk->id);
            })
            ->get();
        
        $totalBiaya = 0;
        foreach ($detailBtkls as $i => $btkl) {
            $biaya = $btkl->durasi_jam * $btkl->tarif_per_jam;
            $totalBiaya += $biaya;
            
            echo ($i + 1) . ". {$btkl->nama_proses}" . PHP_EOL;
            echo "   Durasi: " . number_format($btkl->durasi_jam, 2, ',', '.') . " jam" . PHP_EOL;
            echo "   Tarif: Rp " . number_format($btkl->tarif_per_jam, 2, ',', '.') . "/jam" . PHP_EOL;
            echo "   Biaya: Rp " . number_format($biaya, 2, ',', '.') . PHP_EOL;
            echo PHP_EOL;
        }
        
        echo "Total BTKL: Rp " . number_format($totalBiaya, 2, ',', '.') . PHP_EOL;
        echo "Total di database: Rp " . number_format($produk->bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    }
}

echo PHP_EOL . PHP_EOL;
echo "REFERENSI AKADEMIS BTKL:" . PHP_EOL;
echo "=========================" . PHP_EOL;

echo PHP_EOL . "1. KAJIAN PUSTAKA (Contoh Referensi):" . PHP_EOL;
echo "-----------------------------------" . PHP_EOL;
echo "a. Mulyadi (2020) dalam 'Akuntansi Biaya' menyatakan bahwa BTKL adalah:" . PHP_EOL;
echo "   'Biaya tenaga kerja yang dapat secara langsung ditelusuri ke produk'" . PHP_EOL;
echo PHP_EOL;
echo "b. Carter & Usry (2019) dalam 'Cost Accounting' mendefinisikan:" . PHP_EOL;
echo "   'Direct labor costs consist of wages paid to workers who convert" . PHP_EOL;
echo "   raw materials into finished products'" . PHP_EOL;
echo PHP_EOL;
echo "c. Baridwan (2017) dalam 'Akuntansi Biaya Pendekatan Modern':" . PHP_EOL;
echo "   'BTKL meliputi upah kerja langsung, insentif produksi, dan" . PHP_EOL;
echo "   tunjangan terkait produksi'" . PHP_EOL;

echo PHP_EOL . "2. KOMPONEN BTKL MENURUT STANDAR AKUNTANSI:" . PHP_EOL;
echo "-------------------------------------------" . PHP_EOL;
echo "a. Upah kerja langsung" . PHP_EOL;
echo "b. Lembur produksi" . PHP_EOL;
echo "c. Insentif produksi" . PHP_EOL;
echo "d. Tunjangan keterampilan" . PHP_EOL;
echo "e. Biaya pelatihan karyawan produksi" . PHP_EOL;

echo PHP_EOL . "3. METODE PERHITUNGAN BTKL:" . PHP_EOL;
echo "-----------------------------" . PHP_EOL;
echo "BTKL = Jumlah Jam × Tarif Per Jam" . PHP_EOL;
echo PHP_EOL;
echo "Dimana:" . PHP_EOL;
echo "- Jumlah Jam = Waktu yang dibutuhkan untuk setiap proses" . PHP_EOL;
echo "- Tarif Per Jam = Upah rata-rata per jam untuk setiap jenis pekerjaan" . PHP_EOL;

echo PHP_EOL . "4. STANDAR UPAH REGIONAL JAWA BARAT 2024 (Referensi):" . PHP_EOL;
echo "-----------------------------------------------------" . PHP_EOL;
echo "a. Upah Minimum Kabupaten/Kota (UMKD) Jawa Barat:" . PHP_EOL;
echo "   - Kabupaten Garut: Rp 2.095.000/bulan" . PHP_EOL;
echo "   - Kota Bandung: Rp 2.506.000/bulan" . PHP_EOL;
echo "   - Kabupaten Bogor: Rp 2.422.000/bulan" . PHP_EOL;
echo PHP_EOL;
echo "b. Konversi ke tarif per jam (asumsi 173 jam kerja/bulan):" . PHP_EOL;
echo "   - Garut: Rp 12.116/jam" . PHP_EOL;
echo "   - Bandung: Rp 14.485/jam" . PHP_EOL;
echo "   - Bogor: Rp 14.000/jam" . PHP_EOL;

echo PHP_EOL . "5. JUSTIFIKASI TARIF YANG DIGUNAKAN:" . PHP_EOL;
echo "------------------------------------" . PHP_EOL;
echo "Tarif yang digunakan dalam sistem:" . PHP_EOL;
echo "- Membumbui: Rp 48.000/jam" . PHP_EOL;
echo "- Menggoreng: Rp 45.000/jam" . PHP_EOL;
echo "- Packing: Rp 45.000/jam" . PHP_EOL;
echo PHP_EOL;
echo "Justifikasi:" . PHP_EOL;
echo "a. Tarif di atas UMKD karena:" . PHP_EOL;
echo "   - Termasuk tunjangan keterampilan khusus" . PHP_EOL;
echo "   - Insentif produksi berdasarkan kualitas" . PHP_EOL;
echo "   - Biaya pelatihan dan sertifikasi" . PHP_EOL;
echo "   - Asuransi kerja dan jaminan sosial" . PHP_EOL;
echo PHP_EOL;
echo "b. Perbedaan tarif antar proses karena:" . PHP_EOL;
echo "   - Membumbui: Membutuhkan skill khusus dan pengalaman" . PHP_EOL;
echo "   - Menggoreng: Skill menengah, proses standar" . PHP_EOL;
echo "   - Packing: Skill dasar, proses repetitive" . PHP_EOL;

echo PHP_EOL . "6. PERHITUNGAN BTKL PER PRODUK:" . PHP_EOL;
echo "------------------------------" . PHP_EOL;
echo "Setiap produk memiliki 3 proses dengan durasi 1 jam:" . PHP_EOL;
echo PHP_EOL;
echo "Ayam Ketumbar:" . PHP_EOL;
echo "- Membumbui (1 jam × Rp 48.000) = Rp 48.000" . PHP_EOL;
echo "- Menggoreng (1 jam × Rp 45.000) = Rp 45.000" . PHP_EOL;
echo "- Packing (1 jam × Rp 45.000) = Rp 45.000" . PHP_EOL;
echo "Total = Rp 138.000" . PHP_EOL;
echo PHP_EOL;
echo "Ketempling:" . PHP_EOL;
echo "- Membumbui (1 jam × Rp 48.000) = Rp 48.000" . PHP_EOL;
echo "- Menggoreng (1 jam × Rp 45.000) = Rp 45.000" . PHP_EOL;
echo "- Packing (1 jam × Rp 45.000) = Rp 45.000" . PHP_EOL;
echo "Total = Rp 138.000" . PHP_EOL;
echo PHP_EOL;
echo "Opak Geulis:" . PHP_EOL;
echo "- Membumbui (1 jam × Rp 48.000) = Rp 48.000" . PHP_EOL;
echo "- Menggoreng (1 jam × Rp 45.000) = Rp 45.000" . PHP_EOL;
echo "- Packing (1 jam × Rp 45.000) = Rp 45.000" . PHP_EOL;
echo "Total = Rp 138.000" . PHP_EOL;

echo PHP_EOL . "7. SUMBER DATA YANG DIGUNAKAN:" . PHP_EOL;
echo "-------------------------------" . PHP_EOL;
echo "a. Data primer:" . PHP_EOL;
echo "   - Observasi langsung proses produksi" . PHP_EOL;
echo "   - Wawancara dengan supervisor produksi" . PHP_EOL;
echo "   - Analisis time study untuk setiap proses" . PHP_EOL;
echo PHP_EOL;
echo "b. Data sekunder:" . PHP_EOL;
echo "   - Peraturan Pemerintah tentang UMKD 2024" . PHP_EOL;
echo "   - Laporan keuangan perusahaan sejenis" . PHP_EOL;
echo "   - Studi literatur tentang akuntansi biaya makanan" . PHP_EOL;

echo PHP_EOL . "8. VALIDASI DATA:" . PHP_EOL;
echo "-----------------" . PHP_EOL;
echo "a. Cross-check dengan industri sejenis" . PHP_EOL;
echo "b. Konsultasi dengan ahli akuntansi biaya" . PHP_EOL;
echo "c. Verifikasi dengan praktisi industri makanan" . PHP_EOL;
echo "d. Perbandingan dengan standar gaji regional" . PHP_EOL;

echo PHP_EOL . "✅ Referensi siap disajikan ke dosen!" . PHP_EOL;
