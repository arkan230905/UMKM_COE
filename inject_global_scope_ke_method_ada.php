<?php
/**
 * INJECT GLOBAL SCOPE KE METHOD BOOT/BOOTED YANG SUDAH ADA
 * ==========================================================
 */

$modelsToFix = [
    'ProsesProduksi', 'Pembelian', 'StockMovement',
    'BebanOperasional', 'Bom', 'BomDetail', 'BopProses'
];

$globalScopeCode = <<<'PHP'

        // ===== MULTI-TENANT ISOLATION =====
        // Auto-assign user_id saat creating
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
        
        // Global scope untuk data isolation
        static::addGlobalScope('user', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
        // ===== END MULTI-TENANT ISOLATION =====

PHP;

echo "==============================================\n";
echo "INJECT GLOBAL SCOPE KE METHOD YANG ADA\n";
echo "==============================================\n\n";

$success = 0;
$failed = 0;

foreach ($modelsToFix as $modelName) {
    $filePath = __DIR__ . "/app/Models/{$modelName}.php";
    
    if (!file_exists($filePath)) {
        echo "⚠️  {$modelName}: File tidak ditemukan\n";
        $failed++;
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Cek apakah sudah ada global scope
    if (strpos($content, 'MULTI-TENANT ISOLATION') !== false) {
        echo "⚪ {$modelName}: Sudah ada global scope\n";
        continue;
    }
    
    // Cari method boot() atau booted()
    $pattern = '/(protected static function (boot|booted)\(\)\s*\{)\s*(parent::(boot|booted)\(\);)?/';
    
    if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        $insertPos = $matches[0][1] + strlen($matches[0][0]);
        
        // Insert global scope setelah parent::boot/booted()
        $newContent = substr($content, 0, $insertPos) . $globalScopeCode . substr($content, $insertPos);
        
        if (file_put_contents($filePath, $newContent)) {
            echo "✅ {$modelName}: Global scope ditambahkan\n";
            $success++;
        } else {
            echo "❌ {$modelName}: Gagal menyimpan file\n";
            $failed++;
        }
    } else {
        echo "❌ {$modelName}: Tidak bisa menemukan method boot/booted\n";
        $failed++;
    }
}

echo "\n";
echo "==============================================\n";
echo "SELESAI\n";
echo "==============================================\n\n";
echo "✅ Berhasil: {$success}\n";
echo "❌ Gagal: {$failed}\n\n";

if ($success > 0) {
    echo "🎉 {$success} model berhasil diperbaiki!\n\n";
}
