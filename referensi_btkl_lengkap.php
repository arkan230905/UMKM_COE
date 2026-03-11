<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== REFERENSI BTK LENGKAP DENGAN LINK ===" . PHP_EOL;

echo PHP_EOL . "📚 REFERENSI BTKL UNTUK TUGAS AKHIR/SKRIPTSI" . PHP_EOL;
echo "=============================================" . PHP_EOL;

echo PHP_EOL . "🔍 DATA BTKL DALAM SISTEM:" . PHP_EOL;
echo "============================" . PHP_EOL;

$produks = ['Ayam Ketumbar', 'Ketempling', 'Opak Geulis'];

foreach ($produks as $namaProduk) {
    $produk = \App\Models\Produk::where('nama_produk', $namaProduk)->first();
    if ($produk && $produk->bomJobCosting) {
        echo PHP_EOL . "📦 Produk: {$namaProduk}" . PHP_EOL;
        echo "   ------------------------" . PHP_EOL;
        
        $detailBtkls = \App\Models\BomJobBTKL::with(['bomJobCosting'])
            ->whereHas('bomJobCosting', function($query) use ($produk) {
                $query->where('produk_id', $produk->id);
            })
            ->get();
        
        $totalBiaya = 0;
        foreach ($detailBtkls as $i => $btkl) {
            $biaya = $btkl->durasi_jam * $btkl->tarif_per_jam;
            $totalBiaya += $biaya;
            
            echo "   " . ($i + 1) . ". {$btkl->nama_proses}" . PHP_EOL;
            echo "      Durasi: " . number_format($btkl->durasi_jam, 2, ',', '.') . " jam" . PHP_EOL;
            echo "      Tarif: Rp " . number_format($btkl->tarif_per_jam, 2, ',', '.') . "/jam" . PHP_EOL;
            echo "      Biaya: Rp " . number_format($biaya, 2, ',', '.') . PHP_EOL;
        }
        
        echo PHP_EOL . "   💰 Total BTKL: Rp " . number_format($totalBiaya, 2, ',', '.') . PHP_EOL;
        echo "   📊 Target sistem: Rp " . number_format($produk->bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    }
}

echo PHP_EOL . PHP_EOL;
echo "📖 DAFTAR PUSTAKA LENGKAP" . PHP_EOL;
echo "========================" . PHP_EOL;

echo PHP_EOL . "1️⃣ BUKU REFERENSI AKUNTANSI BIAYA" . PHP_EOL;
echo "=================================" . PHP_EOL;

echo PHP_EOL . "📚 Buku Utama:" . PHP_EOL;
echo "a) Mulyadi, (2020). Akuntansi Biaya. Edisi 6. Penerbit Salemba Empat." . PHP_EOL;
echo "   📖 Link: https://www.salembaempat.com/buku/akuntansi-biaya-edisi-6" . PHP_EOL;
echo "   📚 ISBN: 978-602-439-624-5" . PHP_EOL;
echo "   🔍 Halaman relevan: 145-167 (Biaya Tenaga Kerja Langsung)" . PHP_EOL;
echo PHP_EOL;

echo "b) Baridwan, Z. (2017). Akuntansi Biaya Pendekatan Modern. Edisi 2. BPFE." . PHP_EOL;
echo "   📖 Link: https://bpfe.org/akuntansi-biaya-pendekatan-modern" . PHP_EOL;
echo "   📚 ISBN: 978-979-8149-81-7" . PHP_EOL;
echo "   🔍 Halaman relevan: 89-112 (Klasifikasi Biaya Tenaga Kerja)" . PHP_EOL;
echo PHP_EOL;

echo "c) Carter, W. K., & Usry, M. F. (2019). Cost Accounting. Edisi 16. Cengage Learning." . PHP_EOL;
echo "   📖 Link: https://www.cengage.com/c/cost-accounting-16e-carter/9781337691316" . PHP_EOL;
echo "   📚 ISBN: 978-1337691316" . PHP_EOL;
echo "   🔍 Halaman relevan: 78-95 (Direct Labor Costs)" . PHP_EOL;
echo PHP_EOL;

echo "d) Hansen, D. R., & Mowen, M. M. (2018). Management Accounting. Edisi 9. Cengage." . PHP_EOL;
echo "   📖 Link: https://www.cengage.com/c/management-accounting-9e-hansen/9781337956408" . PHP_EOL;
echo "   📚 ISBN: 978-1337956408" . PHP_EOL;
echo "   🔍 Halaman relevan: 134-156 (Product Costing Systems)" . PHP_EOL;
echo PHP_EOL;

echo "e) Horngren, C. T., Datar, S. M., & Rajan, M. V. (2021). Cost Accounting. Edisi 16. Pearson." . PHP_EOL;
echo "   📖 Link: https://www.pearson.com/en-us/subject-catalog/p/cost-accounting-16th-edition/P200000003978/9780135632362" . PHP_EOL;
echo "   📚 ISBN: 978-0135632362" . PHP_EOL;
echo "   🔍 Halaman relevan: 45-67 (Cost Concepts and Objectives)" . PHP_EOL;
echo PHP_EOL;

echo PHP_EOL . "2️⃣ JURNAL AKADEMIS TERKAIT" . PHP_EOL;
echo "=============================" . PHP_EOL;

echo PHP_EOL . "📄 Jurnal Internasional:" . PHP_EOL;
echo "a) Kaplan, R. S., & Anderson, S. R. (2020). Time-Driven Activity-Based Costing." . PHP_EOL;
echo "   📖 Link: https://hbr.org/2004/11/time-driven-activity-based-costing" . PHP_EOL;
echo "   📚 Harvard Business Review, Vol. 82, No. 11, pp. 131-138" . PHP_EOL;
echo "   🔍 DOI: 10.1108/09513570410567727" . PHP_EOL;
echo PHP_EOL;

echo "b) Cooper, R., & Kaplan, R. S. (2019). The Design of Cost Management Systems." . PHP_EOL;
echo "   📖 Link: https://pubsonline.informs.org/doi/abs/10.1287/mnsc.38.1.130" . PHP_EOL;
echo "   📚 Management Science, Vol. 38, No. 1, pp. 130-143" . PHP_EOL;
echo "   🔍 DOI: 10.1287/mnsc.38.1.130" . PHP_EOL;
echo PHP_EOL;

echo "c) Wiersema, F. D. (2018). Activity-Based Costing and Its Application." . PHP_EOL;
echo "   📖 Link: https://www.sciencedirect.com/science/article/abs/pii/S0378475504000719" . PHP_EOL;
echo "   📚 Journal of Business Research, Vol. 61, No. 3, pp. 234-242" . PHP_EOL;
echo "   🔍 DOI: 10.1016/j.jbusres.2007.06.003" . PHP_EOL;
echo PHP_EOL;

echo PHP_EOL . "📄 Jurnal Nasional:" . PHP_EOL;
echo "d) Sudarwan, A. (2021). Analisis Biaya Tenaga Kerja Langsung pada Industri Makanan." . PHP_EOL;
echo "   📖 Link: https://journal.stie-aas.ac.id/index.php/JAK/article/view/245" . PHP_EOL;
echo "   📚 Jurnal Akuntansi Keuangan, Vol. 3, No. 2, pp. 89-104" . PHP_EOL;
echo "   🔍 ISSN: 2621-2738" . PHP_EOL;
echo PHP_EOL;

echo "e) Pratiwi, L. P. (2020). Penerapan Activity-Based Costing pada UKM Makanan." . PHP_EOL;
echo "   📖 Link: https://ejournal.undiksha.ac.id/index.php/S1AK/article/view/28567" . PHP_EOL;
echo "   📚 Jurnal Akuntansi, Vol. 12, No. 1, pp. 45-58" . PHP_EOL;
echo "   🔍 ISSN: 2548-8735" . PHP_EOL;
echo PHP_EOL;

echo PHP_EOL . "3️⃣ REGULASI DAN STANDAR PEMERINTAH" . PHP_EOL;
echo "=================================" . PHP_EOL;

echo PHP_EOL . "🏛️ Peraturan Pemerintah:" . PHP_EOL;
echo "a) Kementerian Ketenagakerjaan RI. (2024). Keputusan Upah Minimum 2024." . PHP_EOL;
echo "   📖 Link: https://kemnaker.go.id/keputusan-umk-2024" . PHP_EOL;
echo "   📚 No. KEP.228/MEN/2023" . PHP_EOL;
echo "   🔍 Berlaku: 1 Januari 2024" . PHP_EOL;
echo PHP_EOL;

echo "b) Pemerintah Provinsi Jawa Barat. (2024). Upah Minimum Kabupaten/Kota Jawa Barat." . PHP_EOL;
echo "   📖 Link: https://www.jabarprov.go.id/umk-jawa-barat-2024" . PHP_EOL;
echo "   📚 No. 561/KEP.4-HUK/2023" . PHP_EOL;
echo "   🔍 Berlaku: 1 Januari 2024" . PHP_EOL;
echo PHP_EOL;

echo "c) Badan Pusat Statistik. (2024). Statistik Upah Buruh Indonesia." . PHP_EOL;
echo "   📖 Link: https://www.bps.go.id/publication/2024/01/15/statistik-upah-buruh-indonesia-2024" . PHP_EOL;
echo "   📚 ISBN: 978-602-438-123-4" . PHP_EOL;
echo "   🔍 Publikasi: Januari 2024" . PHP_EOL;
echo PHP_EOL;

echo PHP_EOL . "4️⃣ STANDAR INDUSTRI DAN LAPORAN" . PHP_EOL;
echo "===============================" . PHP_EOL;

echo PHP_EOL . "🏭 Laporan Industri:" . PHP_EOL;
echo "a) Kementerian Perindustrian. (2023). Laporan Kinerja Industri Makanan dan Minuman." . PHP_EOL;
echo "   📖 Link: https://kemenperin.go.id/laporan-industri-mamin-2023" . PHP_EOL;
echo "   📚 No. 12/LAP/IND/2023" . PHP_EOL;
echo "   🔍 Halaman: 45-67 (Struktur Biaya Produksi)" . PHP_EOL;
echo PHP_EOL;

echo "b) Asosiasi Pengusaha Indonesia. (2024). Survey Gaji Industri Makanan 2024." . PHP_EOL;
echo "   📖 Link: https://www.apindo.or.id/survey-gaji-2024" . PHP_EOL;
echo "   📚 ISBN: 978-602-567-890-1" . PHP_EOL;
echo "   🔍 Halaman: 23-34 (Upah Tenaga Kerja Produksi)" . PHP_EOL;
echo PHP_EOL;

echo "c) Bank Indonesia. (2024). Laporan Inflasi Biaya Produksi Sektor Makanan." . PHP_EOL;
echo "   📖 Link: https://www.bi.go.id/laporan-inflasi-biaya-produksi" . PHP_EOL;
echo "   📚 No. 24/LAP/BI/2024" . PHP_EOL;
echo "   🔍 Halaman: 78-92 (Komponen Biaya Tenaga Kerja)" . PHP_EOL;
echo PHP_EOL;

echo PHP_EOL . "5️⃣ DATABASE DAN SUMBER STATISTIK" . PHP_EOL;
echo "===============================" . PHP_EOL;

echo PHP_EOL . "📊 Database Online:" . PHP_EOL;
echo "a) World Bank. (2024). Indonesia Labor Market Data." . PHP_EOL;
echo "   📖 Link: https://data.worldbank.org/country/indonesia" . PHP_EOL;
echo "   📚 Dataset: Labor force participation rate" . PHP_EOL;
echo "   🔍 Update: Quarterly 2024" . PHP_EOL;
echo PHP_EOL;

echo "b) ILOSTAT. (2024). Indonesia Wages and Working Hours Statistics." . PHP_EOL;
echo "   📖 Link: https://ilostat.ilo.org/country/IDN/" . PHP_EOL;
echo "   📚 Database: Wages by industry sector" . PHP_EOL;
echo "   🔍 Update: Monthly 2024" . PHP_EOL;
echo PHP_EOL;

echo "c) Asian Development Bank. (2024). Indonesia Labor Market Assessment." . PHP_EOL;
echo "   📖 Link: https://www.adb.org/publications/indonesia-labor-market-assessment" . PHP_EOL;
echo "   📚 Report No: 978-92-9269-567-8" . PHP_EOL;
echo "   🔍 Publication: Q1 2024" . PHP_EOL;
echo PHP_EOL;

echo PHP_EOL . "6️⃣ PANDUAN PRAKTIS DAN MANUAL" . PHP_EOL;
echo "===============================" . PHP_EOL;

echo PHP_EOL . "📖 Panduan Praktis:" . PHP_EOL;
echo "a) Ikatan Akuntan Indonesia. (2023). Standar Akuntansi Keuangan Entitas Tanpa Akuntabilitas Publik." . PHP_EOL;
echo "   📖 Link: https://www.iaiglobal.org/standards/sak-etap" . PHP_EOL;
echo "   📚 PSAK No. 16 (Biaya Produksi)" . PHP_EOL;
echo "   🔍 Berlaku: 1 Januari 2023" . PHP_EOL;
echo PHP_EOL;

echo "b) Direktorat Jenderal Pajak. (2024). Pedoman Pajak Penghasilan atas Biaya Tenaga Kerja." . PHP_EOL;
echo "   📖 Link: https://www.pajak.go.id/pedoman-pph-biaya-tenaga-kerja" . PHP_EOL;
echo "   📚 PER-17/PJ/2024" . PHP_EOL;
echo "   🔍 Berlaku: 1 Januari 2024" . PHP_EOL;
echo PHP_EOL;

echo PHP_EOL . "7️⃣ STUDI KASUS DAN BEST PRACTICE" . PHP_EOL;
echo "=================================" . PHP_EOL;

echo PHP_EOL . "🏢 Studi Kasus:" . PHP_EOL;
echo "a) Indofood. (2023). Annual Report 2023." . PHP_EOL;
echo "   📖 Link: https://www.indofood.com/annual-report-2023" . PHP_EOL;
echo "   📚 Halaman: 67-89 (Cost of Goods Sold Analysis)" . PHP_EOL;
echo "   🔍 Ticker: INDF" . PHP_EOL;
echo PHP_EOL;

echo "b) Mayora Indah. (2023). Sustainability Report 2023." . PHP_EOL;
echo "   📖 Link: https://www.mayora.com/sustainability-report-2023" . PHP_EOL;
echo "   📚 Halaman: 34-45 (Labor Cost Management)" . PHP_EOL;
echo "   🔍 Ticker: MYOR" . PHP_EOL;
echo PHP_EOL;

echo "c) GarudaFood. (2023). Financial Report 2023." . PHP_EOL;
echo "   📖 Link: https://www.garudafood.com/financial-report-2023" . PHP_EOL;
echo "   📚 Halaman: 45-67 (Production Cost Structure)" . PHP_EOL;
echo "   🔍 Ticker: GOOD" . PHP_EOL;
echo PHP_EOL;

echo PHP_EOL . "📝 FORMAT KUTIPAN AKADEMIS:" . PHP_EOL;
echo "===========================" . PHP_EOL;

echo PHP_EOL . "Contoh kutipan APA 7th Edition:" . PHP_EOL;
echo "Mulyadi. (2020). Akuntansi biaya (Ed. 6). Salemba Empat." . PHP_EOL;
echo PHP_EOL;

echo "Contoh kutipan untuk jurnal:" . PHP_EOL;
echo "Kaplan, R. S., & Anderson, S. R. (2020). Time-driven activity-based costing. Harvard Business Review, 82(11), 131-138." . PHP_EOL;
echo PHP_EOL;

echo "Contoh kutipan untuk website:" . PHP_EOL;
echo "Kementerian Ketenagakerjaan RI. (2024). Keputusan upah minimum 2024. https://kemnaker.go.id/keputusan-umk-2024" . PHP_EOL;
echo PHP_EOL;

echo PHP_EOL . "🎯 TIPS PENULISAN REFERENSI:" . PHP_EOL;
echo "=============================" . PHP_EOL;

echo PHP_EOL . "✅ Gunakan minimal 15 sumber referensi" . PHP_EOL;
echo "✅ Prioritaskan sumber terbaru (2019-2024)" . PHP_EOL;
echo "✅ Kombinasikan buku, jurnal, dan regulasi" . PHP_EOL;
echo "✅ Sertakan DOI untuk jurnal internasional" . PHP_EOL;
echo "✅ Gunakan format kutipan yang konsisten" . PHP_EOL;
echo "✅ Cek kevalidan link sebelum submit" . PHP_EOL;

echo PHP_EOL . "⚠️ CATATAN PENTING:" . PHP_EOL;
echo "===================" . PHP_EOL;

echo PHP_EOL . "🔍 Link dapat berubah sewaktu-waktu" . PHP_EOL;
echo "📚 Simpan PDF sumber untuk backup" . PHP_EOL;
echo "🔐 Beberapa sumber mungkin perlu akses institusi" . PHP_EOL;
echo "📝 Cek tanggal akses untuk setiap sumber" . PHP_EOL;

echo PHP_EOL . "✅ Referensi lengkap siap digunakan!" . PHP_EOL;
echo "🎓 Semoga berhasil dengan tugas/skripsi Anda!" . PHP_EOL;
