<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging HPP search in detail...\n";

$userId = 1;
$namaProduk = 'Jasuke';

// Check the exact query that findCoaHpp uses
echo "\n=== Exact findCoaHpp Query Debug ===\n";

// Step 1: Specific search
echo "Step 1: Specific search for 'HPP {$namaProduk}'\n";
$spesifik = \App\Models\Coa::withoutGlobalScopes()
    ->where(function($q) use ($namaProduk) {
        $q->where('nama_akun', 'HPP ' . $namaProduk)
          ->orWhere('nama_akun', 'Harga Pokok Penjualan ' . $namaProduk)
          ->orWhere('nama_akun', 'like', '%HPP%' . $namaProduk . '%');
    })
    ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
    ->where(function($q) use ($userId) {
        $q->where('user_id', $userId)->orWhereNull('user_id');
    })
    ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId ?? 0])
    ->first();

echo "Result: " . ($spesifik ? "FOUND - " . $spesifik->nama_akun : "NOT FOUND") . "\n";

// Step 2: General search
echo "\nStep 2: General search for HPP COA\n";
$umum = \App\Models\Coa::withoutGlobalScopes()
    ->where(function($q) {
        $q->where('kode_akun', '56')
          ->orWhere('nama_akun', 'Harga Pokok Penjualan')
          ->orWhere('nama_akun', 'HPP');
    })
    ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost', 'Biaya'])
    ->where(function($q) use ($userId) {
        $q->where('user_id', $userId)->orWhereNull('user_id');
    })
    ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId ?? 0])
    ->first();

echo "Result: " . ($umum ? "FOUND - " . $umum->nama_akun : "NOT FOUND") . "\n";

// Check the actual COA 56 record
echo "\n=== Actual COA 56 Record ===\n";
$coa56 = \App\Models\Coa::withoutGlobalScopes()
    ->where('kode_akun', '56')
    ->where('user_id', $userId)
    ->first();

if ($coa56) {
    echo "COA 56 found:\n";
    echo "  Kode: " . $coa56->kode_akun . "\n";
    echo "  Nama: " . $coa56->nama_akun . "\n";
    echo "  Tipe: " . $coa56->tipe_akun . "\n";
    echo "  User ID: " . $coa56->user_id . "\n";
    echo "  Company ID: " . $coa56->company_id . "\n";
    
    // Check if it matches the search criteria
    echo "\nChecking match criteria:\n";
    echo "  Kode akun = 56: " . ($coa56->kode_akun == '56' ? 'YES' : 'NO') . "\n";
    echo "  Nama akun = 'Harga Pokok Penjualan': " . ($coa56->nama_akun == 'Harga Pokok Penjualan' ? 'YES' : 'NO') . "\n";
    echo "  Nama akun LIKE '%HPP%': " . (strpos($coa56->nama_akun, 'HPP') !== false ? 'YES' : 'NO') . "\n";
    echo "  Tipe akun IN ['Beban', 'HPP', 'Expense', 'Cost']: " . (in_array($coa56->tipe_akun, ['Beban', 'HPP', 'Expense', 'Cost']) ? 'YES' : 'NO') . "\n";
    echo "  User ID = {$userId}: " . ($coa56->user_id == $userId ? 'YES' : 'NO') . "\n";
} else {
    echo "COA 56 not found\n";
}

// Test the query step by step
echo "\n=== Step-by-step Query Test ===\n";

// Test 1: Just kode_akun = 56
$test1 = \App\Models\Coa::withoutGlobalScopes()
    ->where('kode_akun', '56')
    ->where('user_id', $userId)
    ->first();
echo "Test 1 (kode_akun = 56): " . ($test1 ? "FOUND" : "NOT FOUND") . "\n";

// Test 2: Add tipe_akun condition
$test2 = \App\Models\Coa::withoutGlobalScopes()
    ->where('kode_akun', '56')
    ->where('user_id', $userId)
    ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost', 'Biaya'])
    ->first();
echo "Test 2 (+ tipe_akun): " . ($test2 ? "FOUND" : "NOT FOUND") . "\n";

// Test 3: Add the OR condition
$test3 = \App\Models\Coa::withoutGlobalScopes()
    ->where(function($q) {
        $q->where('kode_akun', '56')
          ->orWhere('nama_akun', 'Harga Pokok Penjualan')
          ->orWhere('nama_akun', 'HPP');
    })
    ->where('user_id', $userId)
    ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost', 'Biaya'])
    ->first();
echo "Test 3 (+ OR condition): " . ($test3 ? "FOUND" : "NOT FOUND") . "\n";

echo "\nHPP search debug completed!\n";
