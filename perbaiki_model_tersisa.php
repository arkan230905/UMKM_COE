<?php
/**
 * PERBAIKI MODEL YANG MASIH BAHAYA
 * =================================
 * Model yang sudah punya boot() atau booted() tapi belum punya global scope
 */

$modelsToCheck = [
    'BahanPendukung', 'ProsesProduksi', 'Pembelian', 'StockMovement',
    'BebanOperasional', 'Bom', 'BomDetail', 'BopProses'
];

echo "==============================================\n";
echo "CEK & PERBAIKI MODEL TERSISA\n";
echo "==============================================\n\n";

foreach ($modelsToCheck as $modelName) {
    $filePath = __DIR__ . "/app/Models/{$modelName}.php";
    
    if (!file_exists($filePath)) {
        echo "⚠️  {$modelName}: File tidak ditemukan\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Cek apakah sudah ada addGlobalScope dengan user_id
    if (preg_match('/addGlobalScope.*user.*user_id.*auth/s', $content)) {
        echo "✅ {$modelName}: Sudah ada global scope\n";
        continue;
    }
    
    echo "❌ {$modelName}: Belum ada global scope yang benar\n";
    
    // Cek apakah ada method boot() atau booted()
    $hasBootMethod = strpos($content, 'function boot()') !== false;
    $hasBootedMethod = strpos($content, 'function booted()') !== false;
    
    if ($hasBootMethod) {
        echo "   → Punya method boot(), perlu tambah manual\n";
        // Tampilkan isi method boot
        if (preg_match('/protected static function boot\(\).*?\n    \}/s', $content, $matches)) {
            echo "   Isi method boot():\n";
            echo "   " . str_replace("\n", "\n   ", $matches[0]) . "\n";
        }
    } elseif ($hasBootedMethod) {
        echo "   → Punya method booted(), perlu tambah manual\n";
    } else {
        echo "   → Tidak ada method boot/booted, bisa tambah otomatis\n";
    }
    
    echo "\n";
}

echo "==============================================\n";
echo "REKOMENDASI\n";
echo "==============================================\n\n";

echo "Untuk model yang punya method boot() atau booted(),\n";
echo "tambahkan kode berikut di dalam method tersebut:\n\n";

echo "```php\n";
echo "// Global scope untuk data isolation (multi-tenant)\n";
echo "static::addGlobalScope('user', function (\$builder) {\n";
echo "    if (auth()->check()) {\n";
echo "        \$builder->where('user_id', auth()->id());\n";
echo "    }\n";
echo "});\n\n";

echo "// Auto-assign user_id saat creating\n";
echo "static::creating(function (\$model) {\n";
echo "    if (empty(\$model->user_id) && auth()->check()) {\n";
echo "        \$model->user_id = auth()->id();\n";
echo "    }\n";
echo "});\n";
echo "```\n\n";
