<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DEBUGGING UI JOURNAL FILTERING FOR HOSTING\n";

// Get SJ-20260430-001
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')->first();

if (!$penjualan) {
    echo "ERROR: Penjualan SJ-20260430-001 not found!\n";
    exit;
}

echo "\n=== DATABASE VERIFICATION ===\n";
echo "Penjualan ID: " . $penjualan->id . "\n";
echo "Referensi: penjualan#" . $penjualan->id . "\n";

// Check database entries
$allEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)
    ->with('coa')
    ->get();

echo "Total entries in database: " . $allEntries->count() . "\n";

echo "\nAll database entries:\n";
foreach ($allEntries as $jurnal) {
    echo "ID: " . $jurnal->id . ", COA: " . $jurnal->coa->kode_akun . " - " . $jurnal->coa->nama_akun . "\n";
    echo "  Debit: " . ($jurnal->debit > 0 ? "Rp " . number_format($jurnal->debit, 0, ',', '.') : "-") . "\n";
    echo "  Kredit: " . ($jurnal->kredit > 0 ? "Rp " . number_format($jurnal->kredit, 0, ',', '.') : "-") . "\n";
    echo "  User ID: " . ($jurnal->user_id ?? 'NULL') . "\n";
    echo "  Created: " . $jurnal->created_at . "\n";
    echo "---\n";
}

// Check what the UI might be querying
echo "\n=== SIMULATING UI QUERY ===\n";

// Simulate typical UI query for Jurnal Umum
$uiQuery = \App\Models\JurnalUmum::where('user_id', 1)
    ->where('tanggal', '>=', '2026-04-30')
    ->where('tanggal', '<=', '2026-04-30')
    ->where('tipe_referensi', 'penjualan')
    ->with('coa')
    ->get();

echo "UI query results: " . $uiQuery->count() . " entries\n";

$penjualanInUI = $uiQuery->where('referensi', 'penjualan#' . $penjualan->id);
echo "SJ-20260430-001 entries in UI query: " . $penjualanInUI->count() . "\n";

if ($penjualanInUI->count() > 0) {
    echo "Entries found in UI simulation:\n";
    foreach ($penjualanInUI as $jurnal) {
        echo "  - " . $jurnal->coa->kode_akun . " " . $jurnal->coa->nama_akun . "\n";
    }
} else {
    echo "SJ-20260430-001 NOT FOUND in UI query!\n";
    
    // Debug why not found
    echo "\n=== DEBUGGING WHY NOT FOUND ===\n";
    
    // Check each condition
    $checkUser = \App\Models\JurnalUmum::where('user_id', 1)
        ->where('referensi', 'penjualan#' . $penjualan->id)
        ->count();
    echo "With user_id=1: " . $checkUser . " entries\n";
    
    $checkDate = \App\Models\JurnalUmum::where('user_id', 1)
        ->where('tanggal', '2026-04-30')
        ->where('referensi', 'penjualan#' . $penjualan->id)
        ->count();
    echo "With user_id=1 and date: " . $checkDate . " entries\n";
    
    $checkType = \App\Models\JurnalUmum::where('user_id', 1)
        ->where('tanggal', '2026-04-30')
        ->where('tipe_referensi', 'penjualan')
        ->where('referensi', 'penjualan#' . $penjualan->id)
        ->count();
    echo "With user_id=1, date, and type: " . $checkType . " entries\n";
    
    // Check if tipe_referensi is the issue
    $actualTypes = $allEntries->pluck('tipe_referensi')->unique();
    echo "Actual tipe_referensi values: " . $actualTypes->implode(', ') . "\n";
}

// Check if there are any scope issues
echo "\n=== CHECKING GLOBAL SCOPES ===\n";
$withoutScopes = \App\Models\JurnalUmum::withoutGlobalScopes()
    ->where('referensi', 'penjualan#' . $penjualan->id)
    ->with('coa')
    ->get();

echo "Without global scopes: " . $withoutScopes->count() . " entries\n";

// Check if the issue is with the COA relationship
echo "\n=== CHECKING COA RELATIONSHIP ===\n";
foreach ($allEntries as $jurnal) {
    try {
        $coa = $jurnal->coa;
        if (!$coa) {
            echo "Entry ID " . $jurnal->id . " has no COA relationship!\n";
        } else {
            echo "Entry ID " . $jurnal->id . " COA: " . $coa->kode_akun . " - " . $coa->nama_akun . "\n";
        }
    } catch (Exception $e) {
        echo "Error loading COA for entry ID " . $jurnal->id . ": " . $e->getMessage() . "\n";
    }
}

// Final diagnosis
echo "\n=== DIAGNOSIS ===\n";
if ($allEntries->count() === 5) {
    echo "Database: CORRECT (5 entries)\n";
} else {
    echo "Database: INCORRECT (expected 5, found " . $allEntries->count() . ")\n";
}

if ($penjualanInUI->count() === 5) {
    echo "UI Query: CORRECT (5 entries)\n";
} else {
    echo "UI Query: INCORRECT (expected 5, found " . $penjualanInUI->count() . ")\n";
}

if ($withoutScopes->count() === 5) {
    echo "Global Scopes: WORKING\n";
} else {
    echo "Global Scopes: ISSUE\n";
}

echo "\n=== RECOMMENDATION ===\n";
if ($penjualanInUI->count() < 5) {
    echo "ISSUE IDENTIFIED: UI query not finding all entries\n";
    echo "SOLUTION: Check UI filtering logic in JurnalUmumController\n";
    echo "ACTION: Need to debug the actual UI controller method\n";
} else {
    echo "Database and UI query match - issue might be:\n";
    echo "1. Browser cache\n";
    echo "2. Session issue\n";
    echo "3. UI filtering parameters\n";
}

echo "\nDebug completed!\n";
