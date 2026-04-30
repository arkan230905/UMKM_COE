<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging HPP COA search...\n";

$userId = 1;

// Check all COA that could be HPP related
echo "\n=== All Potential HPP COA Accounts ===\n";
$hppCoas = \App\Models\Coa::where('user_id', $userId)
    ->where(function($query) {
        $query->where('nama_akun', 'LIKE', '%HPP%')
              ->orWhere('nama_akun', 'LIKE', '%Harga Pokok%')
              ->orWhere('kode_akun', 'LIKE', '5%')
              ->orWhere('tipe_akun', 'Beban')
              ->orWhere('tipe_akun', 'HPP')
              ->orWhere('tipe_akun', 'Expense')
              ->orWhere('tipe_akun', 'Cost');
    })
    ->get();

echo "Found " . $hppCoas->count() . " potential HPP COA accounts:\n";
foreach ($hppCoas as $coa) {
    echo "  " . $coa->kode_akun . " - " . $coa->nama_akun . " (" . $coa->tipe_akun . ")\n";
}

// Check for specific HPP COA (kode 56)
echo "\n=== Specific HPP COA (kode 56) ===\n";
$coa56 = \App\Models\Coa::where('user_id', $userId)
    ->where('kode_akun', '56')
    ->first();

if ($coa56) {
    echo "Found COA 56: " . $coa56->nama_akun . " (" . $coa56->tipe_akun . ")\n";
} else {
    echo "COA 56 not found\n";
}

// Check for Persediaan Barang Jadi COA
echo "\n=== Persediaan Barang Jadi COA ===\n";
$persediaanCoas = \App\Models\Coa::where('user_id', $userId)
    ->where(function($query) {
        $query->where('nama_akun', 'LIKE', '%Persediaan%')
              ->orWhere('nama_akun', 'LIKE', '%Barang Jadi%')
              ->orWhere('kode_akun', 'LIKE', '116%');
    })
    ->get();

echo "Found " . $persediaanCoas->count() . " persediaan COA accounts:\n";
foreach ($persediaanCoas as $coa) {
    echo "  " . $coa->kode_akun . " - " . $coa->nama_akun . " (" . $coa->tipe_akun . ")\n";
}

// Simulate the findCoaHpp method search
echo "\n=== Simulating findCoaHpp Search ===\n";
$namaProduk = 'Jasuke'; // Example product name

// Step 1: Search for specific HPP COA
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

echo "Specific HPP search result: " . ($spesifik ? $spesifik->nama_akun : 'NOT FOUND') . "\n";

// Step 2: Search for general HPP COA
$umum = \App\Models\Coa::withoutGlobalScopes()
    ->where(function($q) {
        $q->where('kode_akun', '56')
          ->orWhere('nama_akun', 'Harga Pokok Penjualan')
          ->orWhere('nama_akun', 'HPP');
    })
    ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
    ->where(function($q) use ($userId) {
        $q->where('user_id', $userId)->orWhereNull('user_id');
    })
    ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId ?? 0])
    ->first();

echo "General HPP search result: " . ($umum ? $umum->nama_akun : 'NOT FOUND') . "\n";

// Simulate findCoaPersediaan search
echo "\n=== Simulating findCoaPersediaan Search ===\n";
$spesifikPersediaan = \App\Models\Coa::withoutGlobalScopes()
    ->where(function($q) use ($namaProduk) {
        $q->where('nama_akun', 'Pers. Barang Jadi ' . $namaProduk)
          ->orWhere('nama_akun', 'Persediaan Barang Jadi ' . $namaProduk)
          ->orWhere('nama_akun', 'like', '%Persediaan%' . $namaProduk . '%')
          ->orWhere('nama_akun', 'like', '%Barang Jadi%' . $namaProduk . '%');
    })
    ->whereIn('tipe_akun', ['Asset', 'Aset'])
    ->where(function($q) use ($userId) {
        $q->where('user_id', $userId)->orWhereNull('user_id');
    })
    ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId ?? 0])
    ->first();

echo "Specific Persediaan search result: " . ($spesifikPersediaan ? $spesifikPersediaan->nama_akun : 'NOT FOUND') . "\n";

$umumPersediaan = \App\Models\Coa::withoutGlobalScopes()
    ->where(function($q) {
        $q->where('kode_akun', '116')
          ->orWhere('nama_akun', 'Persediaan Barang Jadi')
          ->orWhere('nama_akun', 'Pers. Barang Jadi');
    })
    ->whereIn('tipe_akun', ['Asset', 'Aset'])
    ->where(function($q) use ($userId) {
        $q->where('user_id', $userId)->orWhereNull('user_id');
    })
    ->orderByRaw('CASE WHEN user_id = ? THEN 0 ELSE 1 END', [$userId ?? 0])
    ->first();

echo "General Persediaan search result: " . ($umumPersediaan ? $umumPersediaan->nama_akun : 'NOT FOUND') . "\n";

echo "\nHPP COA search debug completed!\n";
