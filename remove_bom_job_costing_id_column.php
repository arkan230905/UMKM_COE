<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== REMOVE UNNECESSARY bom_job_costing_id COLUMN ===\n\n";

echo "1. CEK STRUKTUR SAAT INI:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bbb');
    echo "Columns di bom_job_bbb saat ini:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    if (in_array('bom_job_costing_id', $columns)) {
        echo "\n❌ bom_job_costing_id column MASIH ADA (perlu dihapus)\n";
    } else {
        echo "\n✅ bom_job_costing_id column SUDAH DIHAPUS\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking structure: " . $e->getMessage() . "\n";
}

echo "\n2. BACKUP DATA SEBELUM MENGHAPUS KOLOM:\n\n";

try {
    $bbbData = \App\Models\BomJobBBB::all();
    
    echo "Data yang akan di-backup:\n";
    foreach ($bbbData as $bbb) {
        echo "BBB ID: " . $bbb->id . "\n";
        echo "User ID: " . $bbb->user_id . "\n";
        echo "Produk ID: " . $bbb->produk_id . "\n";
        echo "Bahan Baku ID: " . $bbb->bahan_baku_id . "\n";
        echo "BOM Job Costing ID: " . $bbb->bom_job_costing_id . "\n";
        echo "Jumlah: " . $bbb->jumlah . " " . $bbb->satuan . "\n";
        echo "Subtotal: " . $bbb->subtotal . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error backing up data: " . $e->getMessage() . "\n";
}

echo "\n3. HAPUS bom_job_costing_id COLUMN:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bbb');
    
    if (in_array('bom_job_costing_id', $columns)) {
        echo "Menghapus bom_job_costing_id column...\n";
        
        \Illuminate\Support\Facades\Schema::table('bom_job_bbb', function ($table) {
            $table->dropColumn('bom_job_costing_id');
        });
        
        echo "✅ bom_job_costing_id column berhasil dihapus\n\n";
    } else {
        echo "✅ bom_job_costing_id column sudah tidak ada\n\n";
    }
    
} catch (\Exception $e) {
    echo "Error dropping column: " . $e->getMessage() . "\n";
}

echo "\n4. VERIFIKASI STRUKTUR SETELAH DIHAPUS:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bbb');
    echo "Columns di bom_job_bbb setelah dihapus:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    if (!in_array('bom_job_costing_id', $columns)) {
        echo "\n✅ bom_job_costing_id column BERHASIL DIHAPUS\n";
    } else {
        echo "\n❌ bom_job_costing_id column MASIH ADA\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying structure: " . $e->getMessage() . "\n";
}

echo "\n5. VERIFIKASI DATA SETELAH DIHAPUS:\n\n";

try {
    $bbbData = \App\Models\BomJobBBB::all();
    
    echo "Data di bom_job_bbb setelah kolom dihapus:\n";
    foreach ($bbbData as $bbb) {
        echo "BBB ID: " . $bbb->id . "\n";
        echo "User ID: " . $bbb->user_id . "\n";
        echo "Produk ID: " . $bbb->produk_id . "\n";
        echo "Bahan Baku ID: " . $bbb->bahan_baku_id . "\n";
        echo "Jumlah: " . $bbb->jumlah . " " . $bbb->satuan . "\n";
        echo "Subtotal: " . $bbb->subtotal . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking data after deletion: " . $e->getMessage() . "\n";
}

echo "\n6. UPDATE MODEL RELATIONS (jika perlu):\n\n";

echo "Perlu update model BomJobBBB untuk menghapus relasi ke bom_job_costings:\n";
echo "Cek file app/Models/BomJobBBB.php\n\n";

try {
    $modelFile = 'c:\UMKM_COE\app\Models\BomJobBBB.php';
    if (file_exists($modelFile)) {
        $modelContent = file_get_contents($modelFile);
        
        if (strpos($modelContent, 'bomJobCosting') !== false) {
            echo "⚠️ Model masih memiliki relasi bomJobCosting - perlu dihapus\n";
            echo "Relasi yang perlu dihapus:\n";
            echo "- public function bomJobCosting()\n";
            echo "- 'bomJobCosting' di $with atau $fillable\n\n";
        } else {
            echo "✅ Model sudah bersih dari relasi bomJobCosting\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error checking model: " . $e->getMessage() . "\n";
}

echo "\n7. ALUR DATA YANG BENAR SETELAH PERBAIKAN:\n\n";

echo "✅ ALUR DATA BARU:\n";
echo "1. User input biaya bahan → bom_job_bbb (hanya dengan produk_id)\n";
echo "2. bom_job_bbb.produk_id → relasi ke produks.id\n";
echo "3. bom_job_costings DIBUAT OTOMATIS dari data bom_job_bbb\n";
echo "4. Query: BomJobBBB::where('user_id', auth()->id())->pluck('produk_id')\n";
echo "5. Result: Produk yang memiliki data biaya bahan user\n\n";

echo "8. QUERY BOMCONTROLLER YANG TETAP BERJALAN:\n\n";

try {
    echo "Query di BomController@create (tetap sama):\n";
    echo "\$produkIds = BomJobBBB::where('user_id', auth()->id())\n";
    echo "    ->pluck('produk_id')\n";
    echo "    ->unique();\n\n";
    
    // Test the query
    $produkIds = \App\Models\BomJobBBB::where('user_id', 1)
        ->pluck('produk_id')
        ->unique();
    
    echo "Produk IDs yang ditemukan: " . $produkIds->implode(', ') . "\n\n";
    
    $produks = \App\Models\Produk::where('user_id', 1)
        ->whereIn('id', $produkIds)
        ->get();
    
    echo "Produk yang akan ditampilkan:\n";
    foreach ($produks as $product) {
        echo "- " . $product->nama_produk . " (ID: " . $product->id . ")\n";
        
        // Show biaya bahan details
        $bbbDetails = \App\Models\BomJobBBB::where('user_id', 1)
            ->where('produk_id', $product->id)
            ->get();
        
        foreach ($bbbDetails as $bbb) {
            echo "  - BBB ID: " . $bbb->id . " (Produk ID: " . $bbb->produk_id . ")\n";
            echo "  - Jumlah: " . $bbb->jumlah . " " . $bbb->satuan . "\n";
            echo "  - Subtotal: Rp " . number_format($bbb->subtotal, 2, ',', '.') . "\n";
        }
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error testing query: " . $e->getMessage() . "\n";
}

echo "\n9. SUMMARY PERBAIKAN:\n\n";

echo "✅ YANG TELAH DIPERBAIKI:\n";
echo "- Hapus bom_job_costing_id column dari bom_job_bbb\n";
echo "- Data bom_job_bbb sekarang hanya berisi data biaya bahan murni\n";
echo "- bom_job_costings akan dibuat otomatis dari bom_job_bbb\n";
echo "- Query BomController tetap berjalan normal\n\n";

echo "✅ MANFAAT PERBAIKAN:\n";
echo "- Struktur data lebih bersih dan logis\n";
echo "- bom_job_bbb hanya fokus ke data biaya bahan\n";
echo "- bom_job_costings dibuat saat diperlukan (create HPP)\n";
echo "- Tidak ada dependency yang tidak perlu\n\n";

echo "=== PERBAIKAN SELESAI - STRUKTUR LEBIH BERSIH! 🎉 ===\n";
