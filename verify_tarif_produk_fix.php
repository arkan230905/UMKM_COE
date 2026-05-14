<?php
/**
 * Verification Script for Tarif Produk Refactoring
 * 
 * This script verifies that all components of the tarif_produk refactoring are working correctly.
 * Run this after clearing caches and before testing in the browser.
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Jabatan;
use App\Models\User;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  TARIF PRODUK REFACTORING - VERIFICATION SCRIPT               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$checks = [];

// Check 1: Database column exists
echo "1. Checking database column 'tarif_produk'...\n";
$result = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'jabatans' AND TABLE_SCHEMA = 'eadt_umkm' AND COLUMN_NAME = 'tarif_produk'");
if ($result[0]->cnt > 0) {
    echo "   ✓ Column 'tarif_produk' exists in jabatans table\n";
    $checks['db_column'] = true;
} else {
    echo "   ✗ Column 'tarif_produk' NOT found in jabatans table\n";
    $checks['db_column'] = false;
}

// Check 2: Old column removed
echo "\n2. Checking that old column 'tarif_per_jam' is removed...\n";
$result = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'jabatans' AND TABLE_SCHEMA = 'eadt_umkm' AND COLUMN_NAME = 'tarif_per_jam'");
if ($result[0]->cnt === 0) {
    echo "   ✓ Old column 'tarif_per_jam' successfully removed\n";
    $checks['old_column_removed'] = true;
} else {
    echo "   ✗ Old column 'tarif_per_jam' still exists (should be removed)\n";
    $checks['old_column_removed'] = false;
}

// Check 3: Migration recorded
echo "\n3. Checking migration status...\n";
$migration = DB::table('migrations')->where('migration', '2026_05_12_rename_tarif_jam_to_produk')->first();
if ($migration) {
    echo "   ✓ Migration '2026_05_12_rename_tarif_jam_to_produk' recorded (Batch: {$migration->batch})\n";
    $checks['migration'] = true;
} else {
    echo "   ✗ Migration '2026_05_12_rename_tarif_jam_to_produk' NOT found\n";
    $checks['migration'] = false;
}

// Check 4: Model fillable
echo "\n4. Checking Jabatan model fillable array...\n";
$jabatan = new Jabatan();
if (in_array('tarif_produk', $jabatan->getFillable())) {
    echo "   ✓ 'tarif_produk' is in Jabatan model fillable array\n";
    $checks['model_fillable'] = true;
} else {
    echo "   ✗ 'tarif_produk' NOT in Jabatan model fillable array\n";
    $checks['model_fillable'] = false;
}

// Check 5: Model casts
echo "\n5. Checking Jabatan model casts...\n";
$casts = $jabatan->getCasts();
if (isset($casts['tarif_produk'])) {
    echo "   ✓ 'tarif_produk' is cast to '{$casts['tarif_produk']}'\n";
    $checks['model_casts'] = true;
} else {
    echo "   ✗ 'tarif_produk' NOT in Jabatan model casts\n";
    $checks['model_casts'] = false;
}

// Check 6: Test create operation
echo "\n6. Testing Jabatan creation with tarif_produk...\n";
$user = User::find(13);
if ($user) {
    auth()->setUser($user);
    try {
        $testJabatan = Jabatan::create([
            'nama' => 'Verification Test ' . time(),
            'kategori' => 'btkl',
            'tunjangan' => 0,
            'tunjangan_transport' => 0,
            'tunjangan_konsumsi' => 0,
            'asuransi' => 0,
            'tarif' => 0,
            'tarif_produk' => 25000,
            'user_id' => 13,
        ]);
        echo "   ✓ Successfully created Jabatan with tarif_produk = {$testJabatan->tarif_produk}\n";
        echo "     (ID: {$testJabatan->id}, Kode: {$testJabatan->kode_jabatan})\n";
        $checks['create_operation'] = true;
        
        // Clean up test record
        $testJabatan->delete();
        echo "   ✓ Test record cleaned up\n";
    } catch (\Exception $e) {
        echo "   ✗ Error creating Jabatan: " . $e->getMessage() . "\n";
        $checks['create_operation'] = false;
    }
} else {
    echo "   ⚠ User 13 not found, skipping create test\n";
    $checks['create_operation'] = null;
}

// Check 7: Verify existing data
echo "\n7. Checking existing Jabatan records...\n";
$count = Jabatan::count();
$withTarif = Jabatan::whereNotNull('tarif_produk')->count();
echo "   Total Jabatan records: {$count}\n";
echo "   Records with tarif_produk: {$withTarif}\n";
if ($count > 0 && $withTarif > 0) {
    echo "   ✓ Existing records have tarif_produk values\n";
    $checks['existing_data'] = true;
} else if ($count === 0) {
    echo "   ⚠ No Jabatan records exist yet\n";
    $checks['existing_data'] = null;
} else {
    echo "   ✗ Some records missing tarif_produk values\n";
    $checks['existing_data'] = false;
}

// Check 8: Test kode_jabatan generation (multi-tenant)
echo "\n8. Testing kode_jabatan generation (multi-tenant)...\n";
if ($user) {
    auth()->setUser($user);
    try {
        // Get current max kode for user 13
        $lastJabatan = Jabatan::where('user_id', 13)
            ->where('kode_jabatan', 'like', 'BT%')
            ->orderBy('kode_jabatan', 'desc')
            ->first();
        
        $currentMax = $lastJabatan ? (int) substr($lastJabatan->kode_jabatan, 2) : 0;
        
        // Create test record
        $testJabatan = Jabatan::create([
            'nama' => 'Kode Test ' . time(),
            'kategori' => 'btkl',
            'tunjangan' => 0,
            'tunjangan_transport' => 0,
            'tunjangan_konsumsi' => 0,
            'asuransi' => 0,
            'tarif' => 0,
            'tarif_produk' => 15000,
            'user_id' => 13,
        ]);
        
        $newNumber = (int) substr($testJabatan->kode_jabatan, 2);
        
        if ($newNumber > $currentMax) {
            echo "   ✓ Kode generation working correctly\n";
            echo "     Previous max: BT" . str_pad($currentMax, 3, '0', STR_PAD_LEFT) . "\n";
            echo "     Generated: {$testJabatan->kode_jabatan}\n";
            $checks['kode_generation'] = true;
        } else {
            echo "   ✗ Kode generation failed (not incrementing)\n";
            $checks['kode_generation'] = false;
        }
        
        // Clean up
        $testJabatan->delete();
    } catch (\Exception $e) {
        echo "   ✗ Error testing kode generation: " . $e->getMessage() . "\n";
        $checks['kode_generation'] = false;
    }
} else {
    echo "   ⚠ User 13 not found, skipping kode generation test\n";
    $checks['kode_generation'] = null;
}

// Summary
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICATION SUMMARY                                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;
$skipped = 0;

foreach ($checks as $check => $result) {
    if ($result === true) {
        $passed++;
    } elseif ($result === false) {
        $failed++;
    } else {
        $skipped++;
    }
}

echo "Passed:  {$passed}\n";
echo "Failed:  {$failed}\n";
echo "Skipped: {$skipped}\n\n";

if ($failed === 0) {
    echo "✓ ALL CHECKS PASSED! The refactoring is complete and working correctly.\n";
    echo "\nYou can now:\n";
    echo "  1. Clear your browser cache (Ctrl+Shift+Delete)\n";
    echo "  2. Test creating a new Kualifikasi Tenaga Kerja\n";
    echo "  3. Test editing existing records\n";
    echo "  4. Verify Penggajian page loads correctly\n";
    exit(0);
} else {
    echo "✗ SOME CHECKS FAILED! Please review the errors above.\n";
    exit(1);
}
