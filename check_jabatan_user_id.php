<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING JABATAN (KUALIFIKASI TENAGA KERJA) DATA ===\n\n";

// Get current user
$currentUser = \App\Models\User::where('email', 'arkan@gmail.com')->first();
if (!$currentUser) {
    echo "❌ User arkan@gmail.com not found!\n";
    exit(1);
}

echo "✅ Current User: {$currentUser->name} (ID: {$currentUser->id})\n\n";

// Check all Jabatan data
$allJabatans = \App\Models\Jabatan::all();
echo "📊 Total Jabatan Records: " . $allJabatans->count() . "\n\n";

if ($allJabatans->isEmpty()) {
    echo "❌ NO JABATAN DATA FOUND!\n";
    echo "   You need to create Kualifikasi Tenaga Kerja first at:\n";
    echo "   http://jobcost.eadtmanufaktur.com/master-data/kualifikasi-tenaga-kerja\n\n";
    exit(0);
}

echo "📋 Jabatan Data:\n";
echo str_repeat("-", 100) . "\n";
printf("%-5s %-20s %-10s %-10s %-15s %-20s\n", "ID", "Nama", "Kategori", "User ID", "Gaji Pokok", "Tarif/Jam");
echo str_repeat("-", 100) . "\n";

$nullUserIdCount = 0;
$wrongUserIdCount = 0;
$correctUserIdCount = 0;

foreach ($allJabatans as $jabatan) {
    $status = '';
    if ($jabatan->user_id === null) {
        $status = '❌ NULL';
        $nullUserIdCount++;
    } elseif ($jabatan->user_id != $currentUser->id) {
        $status = '⚠️  WRONG';
        $wrongUserIdCount++;
    } else {
        $status = '✅ OK';
        $correctUserIdCount++;
    }
    
    printf(
        "%-5s %-20s %-10s %-10s %-15s %-20s %s\n",
        $jabatan->id,
        substr($jabatan->nama, 0, 20),
        strtoupper($jabatan->kategori ?? '-'),
        $jabatan->user_id ?? 'NULL',
        number_format($jabatan->gaji_pokok ?? 0, 0, ',', '.'),
        number_format($jabatan->tarif_per_jam ?? 0, 0, ',', '.'),
        $status
    );
}

echo str_repeat("-", 100) . "\n\n";

echo "📊 Summary:\n";
echo "   ✅ Correct user_id: {$correctUserIdCount}\n";
echo "   ❌ NULL user_id: {$nullUserIdCount}\n";
echo "   ⚠️  Wrong user_id: {$wrongUserIdCount}\n\n";

// Check by kategori
echo "📊 Jabatan by Kategori:\n";
$btkl = \App\Models\Jabatan::where('user_id', $currentUser->id)->where('kategori', 'btkl')->count();
$btktl = \App\Models\Jabatan::where('user_id', $currentUser->id)->where('kategori', 'btktl')->count();
echo "   BTKL: {$btkl}\n";
echo "   BTKTL: {$btktl}\n\n";

if ($nullUserIdCount > 0 || $wrongUserIdCount > 0) {
    echo "⚠️  PROBLEM DETECTED!\n";
    echo "   Some Jabatan records have NULL or wrong user_id.\n";
    echo "   This will cause the dropdown to be empty when you select kategori.\n\n";
    
    echo "🔧 FIX: Run this command to fix:\n";
    echo "   php fix_jabatan_user_id.php\n\n";
} else {
    echo "✅ All Jabatan data has correct user_id!\n";
    echo "   The dropdown should work correctly.\n\n";
}

// Test API endpoint
echo "🧪 Testing API Endpoint:\n";
echo "   Testing: /master-data/api/jabatan/by-kategori?kategori=btkl\n";

$btklJabatans = \App\Models\Jabatan::where('user_id', $currentUser->id)
    ->where('kategori', 'btkl')
    ->select('id', 'nama', 'kategori', 'gaji_pokok', 'tarif_per_jam as tarif', 'tunjangan', 'asuransi')
    ->orderBy('nama')
    ->get();

echo "   Result: " . $btklJabatans->count() . " records found\n";
if ($btklJabatans->count() > 0) {
    echo "   ✅ API should return data correctly\n";
} else {
    echo "   ❌ API will return empty array\n";
}

echo "\n";
