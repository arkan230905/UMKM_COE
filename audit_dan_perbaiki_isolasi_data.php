<?php
/**
 * AUDIT DAN PERBAIKI ISOLASI DATA
 * ================================
 * Script ini akan:
 * 1. Scan semua model
 * 2. Cek apakah punya kolom user_id
 * 3. Cek apakah punya global scope
 * 4. Generate kode untuk menambahkan global scope
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "==============================================\n";
echo "AUDIT ISOLASI DATA MULTI-TENANT\n";
echo "==============================================\n\n";

// Daftar model yang perlu isolasi data (punya user_id)
$modelsToCheck = [
    // Master Data
    'Coa', 'Satuan', 'Produk', 'BahanBaku', 'BahanPendukung',
    'Pelanggan', 'Vendor', 'Pegawai', 'Jabatan',
    'KategoriProduk', 'KategoriBahanPendukung', 'Aset', 'Asset',
    'ProsesProduksi', 'KomponenBop',
    
    // Transaksi
    'Penjualan', 'PenjualanDetail', 'Pembelian', 'PembelianDetail',
    'Produksi', 'ProduksiDetail', 'JurnalUmum', 'JournalEntry',
    'StockMovement', 'Penggajian', 'BebanOperasional',
    'PembayaranBeban', 'ApSettlement', 'ReturPenjualan',
    'SalesReturn', 'PurchaseReturn',
    
    // BOM & Costing
    'Bom', 'BomDetail', 'BomJobCosting', 'BomJobBahanPendukung',
    'BomJobBOP', 'BomJobBTKL', 'BomProses',
    
    // BOP
    'Bop', 'BopBudget', 'BopLainnya', 'BopProses',
    
    // Lainnya
    'Presensi', 'KartuStok', 'StockLayer',
];

$pdo = DB::connection()->getPdo();

$results = [];
$needsFix = [];

foreach ($modelsToCheck as $modelName) {
    $className = "App\\Models\\{$modelName}";
    
    if (!class_exists($className)) {
        continue;
    }
    
    try {
        $model = new $className;
        $table = $model->getTable();
        
        // Cek apakah tabel punya kolom user_id
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE 'user_id'");
        $hasUserId = $stmt->rowCount() > 0;
        
        if (!$hasUserId) {
            $results[$modelName] = [
                'table' => $table,
                'has_user_id' => false,
                'status' => '⚪ Tidak perlu isolasi (tidak ada user_id)'
            ];
            continue;
        }
        
        // Cek apakah ada global scope
        $reflection = new ReflectionClass($className);
        $hasBootedMethod = $reflection->hasMethod('booted');
        
        $hasGlobalScope = false;
        if ($hasBootedMethod) {
            $method = $reflection->getMethod('booted');
            $filename = $method->getFileName();
            $startLine = $method->getStartLine();
            $endLine = $method->getEndLine();
            $length = $endLine - $startLine;
            
            $file = file($filename);
            $methodCode = implode('', array_slice($file, $startLine - 1, $length + 1));
            
            if (strpos($methodCode, 'addGlobalScope') !== false && 
                strpos($methodCode, 'user_id') !== false) {
                $hasGlobalScope = true;
            }
        }
        
        if ($hasGlobalScope) {
            $results[$modelName] = [
                'table' => $table,
                'has_user_id' => true,
                'has_global_scope' => true,
                'status' => '✅ Sudah aman (ada global scope)'
            ];
        } else {
            $results[$modelName] = [
                'table' => $table,
                'has_user_id' => true,
                'has_global_scope' => false,
                'status' => '❌ BAHAYA! Tidak ada global scope'
            ];
            $needsFix[] = $modelName;
        }
        
    } catch (Exception $e) {
        $results[$modelName] = [
            'status' => '⚠️  Error: ' . $e->getMessage()
        ];
    }
}

// Tampilkan hasil
echo "HASIL AUDIT:\n";
echo "============\n\n";

$safe = 0;
$danger = 0;
$noNeed = 0;

foreach ($results as $model => $result) {
    echo str_pad($model, 30) . " | " . $result['status'] . "\n";
    
    if (strpos($result['status'], '✅') !== false) $safe++;
    if (strpos($result['status'], '❌') !== false) $danger++;
    if (strpos($result['status'], '⚪') !== false) $noNeed++;
}

echo "\n";
echo "==============================================\n";
echo "RINGKASAN\n";
echo "==============================================\n\n";
echo "✅ Aman (ada global scope): {$safe}\n";
echo "❌ BAHAYA (tidak ada global scope): {$danger}\n";
echo "⚪ Tidak perlu isolasi: {$noNeed}\n\n";

if ($danger > 0) {
    echo "==============================================\n";
    echo "⚠️  MODEL YANG PERLU DIPERBAIKI:\n";
    echo "==============================================\n\n";
    
    foreach ($needsFix as $model) {
        echo "- {$model}\n";
    }
    
    echo "\n";
    echo "==============================================\n";
    echo "KODE UNTUK MENAMBAHKAN GLOBAL SCOPE\n";
    echo "==============================================\n\n";
    
    echo "Tambahkan kode berikut di method booted() setiap model:\n\n";
    
    echo "```php\n";
    echo "protected static function booted()\n";
    echo "{\n";
    echo "    parent::booted();\n";
    echo "    \n";
    echo "    // Auto-assign user_id saat creating\n";
    echo "    static::creating(function (\$model) {\n";
    echo "        if (empty(\$model->user_id) && auth()->check()) {\n";
    echo "            \$model->user_id = auth()->id();\n";
    echo "        }\n";
    echo "    });\n";
    echo "    \n";
    echo "    // Global scope untuk data isolation\n";
    echo "    static::addGlobalScope('user', function (\$builder) {\n";
    echo "        if (auth()->check()) {\n";
    echo "            \$builder->where('user_id', auth()->id());\n";
    echo "        }\n";
    echo "    });\n";
    echo "}\n";
    echo "```\n\n";
    
    echo "⚠️  PENTING: Setelah menambahkan, test dengan 2 user berbeda!\n\n";
    
} else {
    echo "🎉 SEMUA MODEL SUDAH AMAN!\n";
    echo "   Data sudah terisolasi dengan benar.\n\n";
}

echo "==============================================\n";
echo "SELESAI\n";
echo "==============================================\n";
