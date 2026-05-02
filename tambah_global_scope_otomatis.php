<?php
/**
 * TAMBAH GLOBAL SCOPE OTOMATIS
 * =============================
 * Script ini akan menambahkan global scope ke semua model yang belum punya
 * 
 * BACKUP DULU SEBELUM JALANKAN!
 */

$modelsToFix = [
    'Produk', 'BahanBaku', 'BahanPendukung', 'Pelanggan', 'Vendor',
    'Pegawai', 'Jabatan', 'Aset', 'ProsesProduksi',
    'Penjualan', 'PenjualanDetail', 'Pembelian', 'PembelianDetail',
    'Produksi', 'ProduksiDetail', 'JurnalUmum', 'StockMovement',
    'Penggajian', 'BebanOperasional', 'PembayaranBeban',
    'ReturPenjualan', 'PurchaseReturn', 'Bom', 'BomDetail',
    'BopProses', 'Presensi', 'KartuStok', 'StockLayer',
];

$globalScopeCode = <<<'PHP'

    /**
     * Boot method untuk model
     */
    protected static function booted()
    {
        parent::booted();
        
        // Auto-assign user_id saat creating
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
        
        // Global scope untuk data isolation (multi-tenant)
        static::addGlobalScope('user', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }
PHP;

echo "==============================================\n";
echo "TAMBAH GLOBAL SCOPE OTOMATIS\n";
echo "==============================================\n\n";

echo "⚠️  PERINGATAN:\n";
echo "Script ini akan memodifikasi " . count($modelsToFix) . " file model.\n";
echo "Pastikan Anda sudah backup!\n\n";

$success = 0;
$failed = 0;
$skipped = 0;

foreach ($modelsToFix as $modelName) {
    $filePath = __DIR__ . "/app/Models/{$modelName}.php";
    
    if (!file_exists($filePath)) {
        echo "⚠️  {$modelName}: File tidak ditemukan\n";
        $skipped++;
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Cek apakah sudah ada method booted
    if (strpos($content, 'function booted()') !== false) {
        echo "⚪ {$modelName}: Sudah ada method booted(), skip\n";
        $skipped++;
        continue;
    }
    
    // Cari posisi penutup class
    $lastBracePos = strrpos($content, '}');
    
    if ($lastBracePos === false) {
        echo "❌ {$modelName}: Tidak bisa menemukan penutup class\n";
        $failed++;
        continue;
    }
    
    // Insert global scope sebelum penutup class
    $newContent = substr($content, 0, $lastBracePos) . $globalScopeCode . "\n" . substr($content, $lastBracePos);
    
    // Simpan file
    if (file_put_contents($filePath, $newContent)) {
        echo "✅ {$modelName}: Global scope ditambahkan\n";
        $success++;
    } else {
        echo "❌ {$modelName}: Gagal menyimpan file\n";
        $failed++;
    }
}

echo "\n";
echo "==============================================\n";
echo "SELESAI\n";
echo "==============================================\n\n";
echo "✅ Berhasil: {$success}\n";
echo "❌ Gagal: {$failed}\n";
echo "⚪ Dilewati: {$skipped}\n\n";

if ($success > 0) {
    echo "🎉 {$success} model berhasil diperbaiki!\n\n";
    echo "LANGKAH SELANJUTNYA:\n";
    echo "1. Test dengan 2 user berbeda\n";
    echo "2. Pastikan data terisolasi\n";
    echo "3. Jalankan: php test_multi_tenant.php\n\n";
}
