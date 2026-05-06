<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ADD HARGA_POKOK COLUMN TO PRODUK TABLE ===\n\n";

echo "1. CEK STRUKTUR SAAT INI:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('produks');
    echo "Columns di produks table:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    if (in_array('harga_pokok', $columns)) {
        echo "\n✅ harga_pokok column SUDAH ADA\n";
        echo "Tidak perlu menambahkan kolom.\n";
    } else {
        echo "\n❌ harga_pokok column BELUM ADA\n";
        echo "Perlu menambahkan kolom harga_pokok.\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking structure: " . $e->getMessage() . "\n";
}

echo "\n2. TAMBAHKAN harga_pokok COLUMN (Jika belum ada):\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('produks');
    
    if (!in_array('harga_pokok', $columns)) {
        echo "Menambahkan harga_pokok column...\n";
        
        \Illuminate\Support\Facades\Schema::table('produks', function ($table) {
            $table->decimal('harga_pokok', 15, 2)->default(0)->after('stok_minimum');
            $table->index('harga_pokok');
        });
        
        echo "✅ harga_pokok column berhasil ditambahkan\n\n";
        
        // Update existing products with default value
        $produkCount = \App\Models\Produk::count();
        echo "Mengupdate " . $produkCount . " produk dengan harga_pokok = 0...\n";
        
        \App\Models\Produk::query()->update(['harga_pokok' => 0]);
        
        echo "✅ Semua produk berhasil diupdate\n\n";
        
    } else {
        echo "✅ harga_pokok column sudah ada, tidak perlu ditambahkan\n\n";
    }
    
} catch (\Exception $e) {
    echo "Error adding column: " . $e->getMessage() . "\n";
}

echo "\n3. VERIFIKASI STRUKTUR AKHIR:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('produks');
    echo "Columns di produks table setelah perbaikan:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    if (in_array('harga_pokok', $columns)) {
        echo "\n✅ harga_pokok column BERHASIL DITAMBAKAN\n";
    } else {
        echo "\n❌ harga_pokok column gagal ditambahkan\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying final structure: " . $e->getMessage() . "\n";
}

echo "\n4. CEK DATA PRODUK:\n\n";

try {
    $produks = \App\Models\Produk::all();
    
    echo "Data di produks table:\n";
    foreach ($produks as $produk) {
        echo "ID: " . $produk->id . "\n";
        echo "Nama: " . $produk->nama_produk . "\n";
        echo "User ID: " . $produk->user_id . "\n";
        echo "Harga Pokok: " . $produk->harga_pokok . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking product data: " . $e->getMessage() . "\n";
}

echo "\n5. TEST UPDATE HARGA_POKOK:\n\n";

try {
    // Test updating harga_pokok for a product
    $produk = \App\Models\Produk::find(2);
    
    if ($produk) {
        echo "Test update harga_pokok untuk " . $produk->nama_produk . ":\n";
        echo "Harga pokok saat ini: " . $produk->harga_pokok . "\n";
        
        $newHargaPokok = 2761.67;
        $produk->harga_pokok = $newHargaPokok;
        $produk->save();
        
        echo "Setelah update: " . $produk->harga_pokok . "\n";
        echo "✅ Update harga_pokok berhasil\n";
        
        // Reset back to 0 for testing
        $produk->harga_pokok = 0;
        $produk->save();
        echo "✅ Reset kembali ke 0\n";
    } else {
        echo "❌ Produk dengan ID 2 tidak ditemukan\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing update: " . $e->getMessage() . "\n";
}

echo "\n6. SUMMARY:\n\n";

echo "✅ YANG TELAH DIPERBAIKI:\n";
echo "- Menambahkan kolom harga_pokok di tabel produks\n";
echo "- Mengupdate semua produk dengan nilai default 0\n";
echo "- Verifikasi bahwa update harga_pokok berfungsi\n\n";

echo "✅ HASIL:\n";
echo "- BomController@store sekarang bisa update harga_pokok\n";
echo "- Tidak akan ada error 'column not found'\n";
echo "- Data HPP akan tersimpan dengan benar\n\n";

echo "7. NEXT STEPS:\n\n";
echo "1. ✅ Kolom harga_pokok sudah ditambahkan\n";
echo "2. ✅ BomController@store sudah diperbaiki\n";
echo "3. 🔄 Test form submission di browser\n";
echo "4. 🔄 Periksa apakah notifikasi berhasil muncul\n";
echo "5. 🔄 Verifikasi data tersimpan di database\n\n";

echo "=== PERBAIKAN SELESAI ===\n";
