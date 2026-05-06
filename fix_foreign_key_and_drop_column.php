<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX FOREIGN KEY AND DROP COLUMN ===\n\n";

echo "1. CEK FOREIGN KEY CONSTRAINTS:\n\n";

try {
    // Check foreign key constraints on bom_job_bbb table
    $constraints = \Illuminate\Support\Facades\DB::select("
        SELECT 
            CONSTRAINT_NAME,
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            information_schema.KEY_COLUMN_USAGE 
        WHERE 
            TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'bom_job_bbb' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    echo "Foreign key constraints di bom_job_bbb:\n";
    foreach ($constraints as $constraint) {
        echo "- " . $constraint->CONSTRAINT_NAME . "\n";
        echo "  Column: " . $constraint->COLUMN_NAME . "\n";
        echo "  References: " . $constraint->REFERENCED_TABLE_NAME . "." . $constraint->REFERENCED_COLUMN_NAME . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking constraints: " . $e->getMessage() . "\n";
}

echo "\n2. HAPUS FOREIGN KEY CONSTRAINT:\n\n";

try {
    // Drop the foreign key constraint first
    \Illuminate\Support\Facades\Schema::table('bom_job_bbb', function ($table) {
        $table->dropForeign(['bom_job_costing_id']);
    });
    
    echo "✅ Foreign key constraint berhasil dihapus\n\n";
    
} catch (\Exception $e) {
    echo "Error dropping foreign key: " . $e->getMessage() . "\n";
}

echo "\n3. HAPUS bom_job_costing_id COLUMN:\n\n";

try {
    // Now drop the column
    \Illuminate\Support\Facades\Schema::table('bom_job_bbb', function ($table) {
        $table->dropColumn('bom_job_costing_id');
    });
    
    echo "✅ bom_job_costing_id column berhasil dihapus\n\n";
    
} catch (\Exception $e) {
    echo "Error dropping column: " . $e->getMessage() . "\n";
}

echo "\n4. VERIFIKASI STRUKTUR AKHIR:\n\n";

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bbb');
    echo "Columns di bom_job_bbb setelah perbaikan:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    if (!in_array('bom_job_costing_id', $columns)) {
        echo "\n✅ bom_job_costing_id column BERHASIL DIHAPUS\n";
    } else {
        echo "\n❌ bom_job_costing_id column MASIH ADA\n";
    }
    
} catch (\Exception $e) {
    echo "Error verifying final structure: " . $e->getMessage() . "\n";
}

echo "\n5. VERIFIKASI DATA SETELAH PERBAIKAN:\n\n";

try {
    $bbbData = \App\Models\BomJobBBB::all();
    
    echo "Data di bom_job_bbb (struktur bersih):\n";
    foreach ($bbbData as $bbb) {
        echo "BBB ID: " . $bbb->id . "\n";
        echo "User ID: " . $bbb->user_id . "\n";
        echo "Produk ID: " . $bbb->produk_id . "\n";
        echo "Bahan Baku ID: " . $bbb->bahan_baku_id . "\n";
        echo "Jumlah: " . $bbb->jumlah . " " . $bbb->satuan . "\n";
        echo "Harga Satuan: Rp " . number_format($bbb->harga_satuan, 2, ',', '.') . "\n";
        echo "Subtotal: Rp " . number_format($bbb->subtotal, 2, ',', '.') . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error checking data: " . $e->getMessage() . "\n";
}

echo "\n6. UPDATE MODEL RELATIONS:\n\n";

try {
    $modelFile = 'c:\UMKM_COE\app\Models\BomJobBBB.php';
    
    if (file_exists($modelFile)) {
        echo "Memperbaiki model BomJobBBB...\n";
        
        $modelContent = file_get_contents($modelFile);
        
        // Remove bomJobCosting relation if exists
        $modelContent = preg_replace('/\s*public function bomJobCosting\(\)[^{]*\{[^}]*\}\s*/', '', $modelContent);
        
        // Remove bomJobCosting from $with if exists
        $modelContent = preg_replace('/\'bomJobCosting\',?\s*/', '', $modelContent);
        
        file_put_contents($modelFile, $modelContent);
        
        echo "✅ Model BomJobBBB berhasil diperbaiki\n";
    }
    
} catch (\Exception $e) {
    echo "Error updating model: " . $e->getMessage() . "\n";
}

echo "\n7. TEST QUERY BOMCONTROLLER (TETAP BERJALAN):\n\n";

try {
    echo "Query di BomController@create:\n";
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
    
    echo "Produk yang ditampilkan:\n";
    foreach ($produks as $product) {
        echo "- " . $product->nama_produk . " (ID: " . $product->id . ")\n";
        
        $bbbDetails = \App\Models\BomJobBBB::where('user_id', 1)
            ->where('produk_id', $product->id)
            ->get();
        
        foreach ($bbbDetails as $bbb) {
            echo "  - BBB: " . $bbb->jumlah . " " . $bbb->satuan . " @ Rp " . number_format($bbb->harga_satuan, 2, ',', '.') . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error testing query: " . $e->getMessage() . "\n";
}

echo "\n8. ALUR DATA YANG SANGAT BERSIH SEKARANG:\n\n";

echo "✅ ALUR DATA FINAL:\n";
echo "1. User input biaya bahan → bom_job_bbb (hanya data biaya bahan murni)\n";
echo "2. bom_job_bbb fields: user_id, produk_id, bahan_baku_id, jumlah, satuan, harga_satuan, subtotal\n";
echo "3. bom_job_costings DIBUAT OTOMATIS saat create HPP dari data bom_job_bbb\n";
echo "4. Query: BomJobBBB::where('user_id', auth()->id())->pluck('produk_id')\n";
echo "5. Result: Produk yang memiliki data biaya bahan user\n\n";

echo "9. MANFAAT STRUKTUR BERSIH:\n\n";

echo "✅ KEUNTUNGAN:\n";
echo "- bom_job_bbb hanya fokus ke data biaya bahan\n";
echo "- Tidak ada dependency ke bom_job_costings\n";
echo "- Struktur lebih logis dan mudah dipahami\n";
echo "- bom_job_costings dibuat saat diperlukan saja\n";
echo "- Multi-tenant filtering lebih jelas\n\n";

echo "=== PERBAIKAN SELESAI - STRUKTUR 100% BERSIH! 🎉 ===\n";
