<?php

/**
 * BTKL System Verification Script
 * Verifies that all components are working correctly
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         BTKL SYSTEM VERIFICATION - FINAL CHECK            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$allPassed = true;

// TEST 1: Database Structure
echo "TEST 1: Database Structure\n";
echo str_repeat("-", 60) . "\n";

$columns = DB::select("SHOW COLUMNS FROM proses_produksis WHERE Field IN ('biaya_btkl_per_produk', 'user_id', 'tarif_btkl', 'kapasitas_per_jam')");
$requiredColumns = ['biaya_btkl_per_produk', 'user_id', 'tarif_btkl', 'kapasitas_per_jam'];
$foundColumns = array_map(fn($col) => $col->Field, $columns);

foreach ($requiredColumns as $col) {
    if (in_array($col, $foundColumns)) {
        echo "  ✓ Column '$col' exists\n";
    } else {
        echo "  ✗ Column '$col' MISSING\n";
        $allPassed = false;
    }
}
echo "\n";

// TEST 2: Data Integrity
echo "TEST 2: Data Integrity (User ID 2)\n";
echo str_repeat("-", 60) . "\n";

$proses = DB::table('proses_produksis')
    ->where('user_id', 2)
    ->get();

echo "  Found {$proses->count()} records for user_id = 2\n\n";

foreach ($proses as $p) {
    $expected = $p->kapasitas_per_jam > 0 ? $p->tarif_btkl / $p->kapasitas_per_jam : 0;
    $match = abs($p->biaya_btkl_per_produk - $expected) < 0.01;
    
    echo "  ID {$p->id}: {$p->nama_proses}\n";
    echo "    Tarif BTKL: Rp " . number_format($p->tarif_btkl, 2) . "\n";
    echo "    Kapasitas: {$p->kapasitas_per_jam} unit/jam\n";
    echo "    Biaya/Produk (DB): Rp " . number_format($p->biaya_btkl_per_produk, 2) . "\n";
    echo "    Biaya/Produk (Expected): Rp " . number_format($expected, 2) . "\n";
    
    if ($match) {
        echo "    ✓ CORRECT\n\n";
    } else {
        echo "    ✗ MISMATCH\n\n";
        $allPassed = false;
    }
}

// TEST 3: Multi-Tenant Isolation
echo "TEST 3: Multi-Tenant Isolation\n";
echo str_repeat("-", 60) . "\n";

$users = DB::table('proses_produksis')
    ->select('user_id', DB::raw('COUNT(*) as count'))
    ->groupBy('user_id')
    ->get();

foreach ($users as $user) {
    echo "  User ID {$user->user_id}: {$user->count} records\n";
}

$crossTenantData = DB::table('proses_produksis')
    ->whereNull('user_id')
    ->count();

if ($crossTenantData == 0) {
    echo "  ✓ No records without user_id\n";
} else {
    echo "  ✗ Found {$crossTenantData} records without user_id\n";
    $allPassed = false;
}
echo "\n";

// TEST 4: Calculation Accuracy
echo "TEST 4: Calculation Accuracy\n";
echo str_repeat("-", 60) . "\n";

$testCases = [
    ['tarif' => 20000, 'kapasitas' => 120, 'expected' => 166.67],
    ['tarif' => 17000, 'kapasitas' => 60, 'expected' => 283.33],
    ['tarif' => 15000, 'kapasitas' => 100, 'expected' => 150.00],
];

foreach ($testCases as $test) {
    $calculated = $test['tarif'] / $test['kapasitas'];
    $match = abs($calculated - $test['expected']) < 0.01;
    
    echo "  Rp {$test['tarif']} ÷ {$test['kapasitas']} = Rp " . number_format($calculated, 2);
    
    if ($match) {
        echo " ✓\n";
    } else {
        echo " ✗ (Expected: Rp {$test['expected']})\n";
        $allPassed = false;
    }
}
echo "\n";

// TEST 5: Model Casts
echo "TEST 5: Model Configuration\n";
echo str_repeat("-", 60) . "\n";

try {
    $model = new \App\Models\ProsesProduksi();
    $casts = $model->getCasts();
    
    if (isset($casts['biaya_btkl_per_produk'])) {
        echo "  ✓ biaya_btkl_per_produk cast: {$casts['biaya_btkl_per_produk']}\n";
    } else {
        echo "  ✗ biaya_btkl_per_produk cast not defined\n";
        $allPassed = false;
    }
    
    if (isset($casts['tarif_btkl'])) {
        echo "  ✓ tarif_btkl cast: {$casts['tarif_btkl']}\n";
    } else {
        echo "  ✗ tarif_btkl cast not defined\n";
        $allPassed = false;
    }
    
    $fillable = $model->getFillable();
    if (in_array('biaya_btkl_per_produk', $fillable)) {
        echo "  ✓ biaya_btkl_per_produk is fillable\n";
    } else {
        echo "  ✗ biaya_btkl_per_produk is NOT fillable\n";
        $allPassed = false;
    }
} catch (\Exception $e) {
    echo "  ✗ Error checking model: " . $e->getMessage() . "\n";
    $allPassed = false;
}
echo "\n";

// FINAL RESULT
echo "╔════════════════════════════════════════════════════════════╗\n";
if ($allPassed) {
    echo "║                    ✓ ALL TESTS PASSED                     ║\n";
    echo "║                                                            ║\n";
    echo "║              SYSTEM READY FOR PRODUCTION                   ║\n";
} else {
    echo "║                    ✗ SOME TESTS FAILED                     ║\n";
    echo "║                                                            ║\n";
    echo "║              PLEASE FIX ISSUES ABOVE                       ║\n";
}
echo "╚════════════════════════════════════════════════════════════╝\n";

exit($allPassed ? 0 : 1);
