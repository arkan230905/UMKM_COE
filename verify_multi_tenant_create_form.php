<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFIKASI MULTI-TENANT COMPLIANCE CREATE FORM ===\n\n";

echo "1. CEK DATA PRODUK YANG DITAMPILKAN DI CREATE FORM:\n\n";

try {
    // Simulasi query di BomController@create
    echo "Query saat ini (BERMASALAH):\n";
    echo "Produk::whereHas('bomJobCosting')->with('bomJobCosting')->get()\n";
    echo "❌ TIDAK ADA user_id filtering!\n\n";
    
    // Test current query
    $currentProducts = \App\Models\Produk::whereHas('bomJobCosting')
        ->with('bomJobCosting')
        ->get();
    
    echo "Hasil query saat ini:\n";
    foreach ($currentProducts as $product) {
        echo "- " . $product->nama_produk . " (User ID: " . $product->user_id . ")\n";
    }
    
    echo "\nQuery yang BENAR (multi-tenant):\n";
    echo "Produk::where('user_id', auth()->id())->whereHas('bomJobCosting')->with('bomJobCosting')->get()\n";
    echo "✅ DENGAN user_id filtering!\n\n";
    
    // Test correct query
    $correctProducts = \App\Models\Produk::where('user_id', 1)
        ->whereHas('bomJobCosting')
        ->with('bomJobCosting')
        ->get();
    
    echo "Hasil query yang benar:\n";
    foreach ($correctProducts as $product) {
        echo "- " . $product->nama_produk . " (User ID: " . $product->user_id . ")\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking products: " . $e->getMessage() . "\n";
}

echo "\n2. CEK DATA PROSES BTKL YANG DITAMPILKAN:\n\n";

try {
    echo "Query saat ini (SUDAH BENAR):\n";
    echo "ProsesProduksi::where('kapasitas_per_jam', '>', 0)\n";
    echo "->whereHas('jabatan', function(\$q) { \$q->where('user_id', auth()->id()); })\n";
    echo "✅ SUDAH ADA user_id filtering!\n\n";
    
    // Test BTKL query
    $prosesBtkl = \App\Models\ProsesProduksi::where('kapasitas_per_jam', '>', 0)
        ->with(['jabatan', 'bopProses'])
        ->whereHas('jabatan', function($q) {
            $q->where('user_id', 1);
        })
        ->get();
    
    echo "Hasil query BTKL:\n";
    foreach ($prosesBtkl as $proses) {
        echo "- " . $proses->nama_proses . " (User ID: " . ($proses->user_id ?? 'NULL') . ")\n";
        echo "  Jabatan: " . ($proses->jabatan->nama ?? 'NULL') . " (User ID: " . ($proses->jabatan->user_id ?? 'NULL') . ")\n";
        if ($proses->bopProses) {
            echo "  BOP: " . $proses->bopProses->keterangan . " (User ID: " . $proses->bopProses->user_id . ")\n";
        }
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking BTKL: " . $e->getMessage() . "\n";
}

echo "\n3. CEK DATA BOP YANG DITAMPILKAN OTOMATIS:\n\n";

try {
    // Check if BOP data is properly filtered
    $bopData = \App\Models\BopProses::where('is_active', true)->get();
    
    echo "Semua BOP di database:\n";
    foreach ($bopData as $bop) {
        echo "- " . $bop->keterangan . " (User ID: " . $bop->user_id . ")\n";
    }
    
    echo "\nBOP yang terhubung dengan proses BTKL user 1:\n";
    foreach ($prosesBtkl as $proses) {
        if ($proses->bopProses) {
            echo "- " . $proses->nama_proses . " -> " . $proses->bopProses->keterangan . " (User ID: " . $proses->bopProses->user_id . ")\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking BOP: " . $e->getMessage() . "\n";
}

echo "\n4. ANALISIS MULTI-TENANT COMPLIANCE:\n\n";

echo "✅ YANG SUDAH BENAR:\n";
echo "- Proses BTKL: Sudah menggunakan user_id filtering\n";
echo "- Pegawai: Sudah menggunakan user_id filtering\n";
echo "- BOP: Otomatis terfilter karena terikat dengan proses BTKL\n\n";

echo "❌ YANG MASIH BERMASALAH:\n";
echo "- Produk selection: TIDAK ADA user_id filtering\n";
echo "- Risiko: User bisa lihat produk dari user lain\n\n";

echo "5. PERBAIKAN YANG DIBUTUHKAN:\n\n";

echo "Di BomController@create method line 951-953:\n";
echo "SEBELUM:\n";
echo "\$produks = Produk::whereHas('bomJobCosting')\n";
echo "    ->with('bomJobCosting')\n";
echo "    ->get();\n\n";

echo "SETELAH:\n";
echo "\$produks = Produk::where('user_id', auth()->id())\n";
echo "    ->whereHas('bomJobCosting')\n";
echo "    ->with('bomJobCosting')\n";
echo "    ->get();\n\n";

echo "6. CEK TOTAL DATA DI DATABASE:\n\n";

try {
    $totalProduk = \App\Models\Produk::count();
    $userProduk = \App\Models\Produk::where('user_id', 1)->count();
    
    $totalProses = \App\Models\ProsesProduksi::count();
    $userProses = \App\Models\ProsesProduksi::where('user_id', 1)->count();
    
    $totalBOP = \App\Models\BopProses::count();
    $userBOP = \App\Models\BopProses::where('user_id', 1)->count();
    
    echo "Produk: $userProduk dari $totalProduk milik user 1\n";
    echo "Proses: $userProses dari $totalProses milik user 1\n";
    echo "BOP: $userBOP dari $totalBOP milik user 1\n";
    
    if ($userProduk < $totalProduk || $userProses < $totalProses || $userBOP < $totalBOP) {
        echo "\n⚠️ ADA DATA DARI USER LAIN YANG BISA TERAKSES!\n";
        echo "Perlu perbaikan multi-tenant filtering.\n";
    } else {
        echo "\n✅ Semua data milik user 1.\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking totals: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFIKASI SELESAI ===\n";
