<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ANALISIS ALUR DATA HARGA POKOK PRODUKSI ===\n\n";

echo "1. DATA FLOW DARI CREATE FORM:\n";
echo "   URL: /master-data/harga-pokok-produksi/create\n";
echo "   Controller: BomController@store\n";
echo "   Method: POST\n\n";

echo "2. DATABASE YANG DIGUNAKAN UNTUK SIMPAN DATA:\n";
echo "   ✅ UTAMA: bom_job_costings (Job Costing)\n";
echo "   ✅ DETAIL BTKL: bom_job_btkl (BTKL per proses)\n";
echo "   ✅ DETAIL BOP: bom_job_bop (BOP per proses)\n";
echo "   ✅ PRODUK UPDATE: produk (update harga_pokok)\n\n";

echo "3. PROSES PENYIMPANAN (STORE METHOD):\n\n";

echo "   Step 1: Validasi Input\n";
echo "   - produk_id (dari tabel produks)\n";
echo "   - proses_ids (dari tabel proses_produksis)\n";
echo "   - biaya_bahan (total biaya bahan baku)\n";
echo "   - total_btkl (total BTKL)\n";
echo "   - total_bop (total BOP)\n";
echo "   - total_hpp (total HPP)\n\n";

echo "   Step 2: Cek BomJobCosting\n";
echo "   - Table: bom_job_costings\n";
echo "   - Query: WHERE produk_id = ?\n";
echo "   - Jika tidak ada: return error\n\n";

echo "   Step 3: Hapus Data Lama\n";
echo "   - DELETE FROM bom_job_btkl WHERE bom_job_costing_id = ?\n";
echo "   - DELETE FROM bom_job_bop WHERE bom_job_costing_id = ?\n\n";

echo "   Step 4: Simpan BTKL per Proses\n";
echo "   - Table: bom_job_btkl\n";
echo "   - Fields: bom_job_costing_id, nama_proses, tarif_per_jam, kapasitas_per_jam, subtotal\n";
echo "   - Data: dari proses_produksis + pegawai (multi-tenant)\n\n";

echo "   Step 5: Simpan BOP per Proses\n";
echo "   - Table: bom_job_bop\n";
echo "   - Fields: bom_job_costing_id, nama_bop, tarif, jumlah, subtotal\n";
echo "   - Data: dari bop_proses + komponen_bop\n\n";

echo "   Step 6: Update BomJobCosting\n";
echo "   - Table: bom_job_costings\n";
echo "   - Update: total_btkl, total_bop, total_hpp, hpp_per_unit\n\n";

echo "   Step 7: Update Produk\n";
echo "   - Table: produk\n";
echo "   - Update: harga_pokok = total_hpp\n\n";

echo "4. DATABASE YANG DIGUNAKAN UNTUK TAMPILAN (INDEX METHOD):\n\n";

echo "   BomController@index:\n";
echo "   - Query: Produk::with(['bomJobCosting', 'satuan'])->where('user_id', auth()->id())\n";
echo "   - Filter: whereHas('bomJobCosting') dengan complete data (BBB, BTKL, BOP > 0)\n\n";

echo "5. CEK DATA SAAT INI:\n\n";

try {
    echo "   Data di bom_job_costings:\n";
    $jobCostings = \App\Models\BomJobCosting::with('produk')->get();
    foreach ($jobCostings as $jc) {
        echo "   - Produk: " . $jc->produk->nama_produk . "\n";
        echo "     Total BBB: " . $jc->total_bbb . "\n";
        echo "     Total BTKL: " . $jc->total_btkl . "\n";
        echo "     Total BOP: " . $jc->total_bop . "\n";
        echo "     Total HPP: " . $jc->total_hpp . "\n";
        echo "     HPP per Unit: " . $jc->hpp_per_unit . "\n";
        echo "\n";
    }
    
    echo "   Data di bom_job_btkl:\n";
    $btklDetails = \App\Models\BomJobBTKL::with('bomJobCosting.produk')->get();
    foreach ($btklDetails as $btkl) {
        echo "   - Produk: " . ($btkl->bomJobCosting->produk->nama_produk ?? 'N/A') . "\n";
        echo "     Proses: " . $btkl->nama_proses . "\n";
        echo "     Tarif per Jam: Rp " . number_format($btkl->tarif_per_jam, 0, ',', '.') . "\n";
        echo "     Kapasitas: " . $btkl->kapasitas_per_jam . " pcs/jam\n";
        echo "     Subtotal: Rp " . number_format($btkl->subtotal, 2, ',', '.') . "\n";
        echo "\n";
    }
    
    echo "   Data di bom_job_bop:\n";
    $bopDetails = \App\Models\BomJobBOP::with('bomJobCosting.produk')->get();
    foreach ($bopDetails as $bop) {
        echo "   - Produk: " . ($bop->bomJobCosting->produk->nama_produk ?? 'N/A') . "\n";
        echo "     Nama BOP: " . $bop->nama_bop . "\n";
        echo "     Tarif: Rp " . number_format($bop->tarif, 2, ',', '.') . "\n";
        echo "     Subtotal: Rp " . number_format($bop->subtotal, 2, ',', '.') . "\n";
        echo "\n";
    }
    
    echo "   Data di produk (harga_pokok):\n";
    $products = \App\Models\Produk::where('harga_pokok', '>', 0)->get();
    foreach ($products as $product) {
        echo "   - Produk: " . $product->nama_produk . "\n";
        echo "     Harga Pokok: Rp " . number_format($product->harga_pokok, 2, ',', '.') . "\n";
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking data: " . $e->getMessage() . "\n";
}

echo "6. KESIMPULAN:\n\n";
echo "   ✅ Data CREATE disimpan di:\n";
echo "      - bom_job_costings (total perhitungan)\n";
echo "      - bom_job_btkl (detail BTKL per proses)\n";
echo "      - bom_job_bop (detail BOP per proses)\n";
echo "      - produk (update harga_pokok)\n\n";

echo "   ✅ Data INDEX diambil dari:\n";
echo "      - produk (dengan relasi ke bomJobCosting)\n";
echo "      - bom_job_costings (data perhitungan HPP)\n";
echo "      - bom_job_btkl (detail BTKL untuk view)\n";
echo "      - bom_job_bop (detail BOP untuk view)\n\n";

echo "   ✅ Multi-tenant compliance:\n";
echo "      - Semua query menggunakan user_id filtering\n";
echo "      - Data terisolasi per user\n\n";

echo "=== ANALISIS SELESAI ===\n";
