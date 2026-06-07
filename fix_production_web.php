<?php
/**
 * WEB-BASED PRODUCTION FIX
 * 
 * Akses via browser: https://your-domain.com/fix_production_web.php
 * 
 * PENTING: Hapus file ini setelah selesai!
 */

// Simple security check
$secret = isset($_GET['secret']) ? $_GET['secret'] : '';
if ($secret !== 'fix-coa-nayla-2026') {
    die('Access denied. Use: ?secret=fix-coa-nayla-2026');
}

echo "<pre>";
echo "═══════════════════════════════════════════════════════════\n";
echo "         PRODUCTION FIX - COA MAPPING UPDATE\n";
echo "═══════════════════════════════════════════════════════════\n\n";

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "STEP 1: Updating Bahan Baku COA Mappings...\n";
    echo "───────────────────────────────────────────────────────────\n";
    
    $bahanBakuMappings = [
        ['Ayam Potong', '1141'],
        ['Ayam Kampung', '1142'],
        ['Bebek', '1143'],
    ];
    
    foreach ($bahanBakuMappings as [$nama, $coa]) {
        $bb = \App\Models\BahanBaku::where('nama_bahan', $nama)->first();
        if ($bb) {
            $bb->coa_persediaan_id = $coa;
            $bb->save();
            echo "  ✓ {$nama} → {$coa}\n";
        } else {
            echo "  ⚠ {$nama} tidak ditemukan\n";
        }
    }
    
    echo "\nSTEP 2: Updating Bahan Pendukung COA Mappings...\n";
    echo "───────────────────────────────────────────────────────────\n";
    
    $bahanPendukungMappings = [
        ['Tepung Terigu', '1152'],
        ['Tepung Maizena', '1153'],
        ['Lada', '1154'],
        ['Bubuk Kaldu Ayam', '1155'],
        ['Bubuk Bawang Putih', '1156'],
        ['Minyak Goreng', '1151'],
        ['Air Galon', '1150'],
        ['Kemasan Makanan', '1157'],
    ];
    
    foreach ($bahanPendukungMappings as [$nama, $coa]) {
        $bp = \App\Models\BahanPendukung::where('nama_bahan', $nama)->first();
        if ($bp) {
            $bp->coa_persediaan_id = $coa;
            $bp->save();
            echo "  ✓ {$nama} → {$coa}\n";
        } else {
            echo "  ⚠ {$nama} tidak ditemukan\n";
        }
    }
    
    echo "\nSTEP 3: Clearing Caches...\n";
    echo "───────────────────────────────────────────────────────────\n";
    
    Artisan::call('config:clear');
    echo "  ✓ Config cache cleared\n";
    
    Artisan::call('cache:clear');
    echo "  ✓ Application cache cleared\n";
    
    Artisan::call('view:clear');
    echo "  ✓ View cache cleared\n";
    
    Artisan::call('route:clear');
    echo "  ✓ Route cache cleared\n";
    
    echo "\nSTEP 4: Verification...\n";
    echo "───────────────────────────────────────────────────────────\n";
    
    $ayam = \App\Models\BahanBaku::where('nama_bahan', 'Ayam Potong')->first();
    if ($ayam && $ayam->coa_persediaan_id == '1141') {
        $coa = \App\Models\Coa::where('kode_akun', '1141')->where('user_id', $ayam->user_id)->first();
        if ($coa) {
            echo "  ✓ Ayam Potong: {$coa->nama_akun}\n";
        } else {
            echo "  ⚠ COA 1141 tidak ditemukan di database\n";
        }
    }
    
    $tepung = \App\Models\BahanPendukung::where('nama_bahan', 'Tepung Terigu')->first();
    if ($tepung && $tepung->coa_persediaan_id == '1152') {
        $coa = \App\Models\Coa::where('kode_akun', '1152')->where('user_id', $tepung->user_id)->first();
        if ($coa) {
            echo "  ✓ Tepung Terigu: {$coa->nama_akun}\n";
        } else {
            echo "  ⚠ COA 1152 tidak ditemukan di database\n";
        }
    }
    
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "                    ✓✓✓ SUCCESS ✓✓✓\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "Next Steps:\n";
    echo "1. Hard refresh browser (Ctrl+F5)\n";
    echo "2. Go to: Transaksi > Pembelian > Tambah Pembelian\n";
    echo "3. Select 'Ayam Potong'\n";
    echo "4. Check Preview Jurnal should show specific COA\n";
    echo "5. IMPORTANT: Delete this file (fix_production_web.php)!\n\n";
    
} catch (\Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
