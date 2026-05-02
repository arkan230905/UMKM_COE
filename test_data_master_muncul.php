<?php
/**
 * TEST: Verifikasi data master muncul untuk user arkam@gmail.com
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "==============================================\n";
echo "TEST DATA MASTER UNTUK USER arkan@gmail.com\n";
echo "==============================================\n\n";

// Cari user arkan@gmail.com
$user = \App\Models\User::where('email', 'arkan@gmail.com')->first();

if (!$user) {
    echo "❌ User arkan@gmail.com tidak ditemukan!\n";
    exit(1);
}

echo "✓ User ditemukan: {$user->name} (ID: {$user->id})\n\n";

// Login sebagai user ini
auth()->login($user);

echo "Testing dengan user yang sudah login...\n\n";

// Test 1: Query COA dengan global scope
echo "[TEST 1] Query COA dengan global scope:\n";
$coas = \App\Models\Coa::whereNotNull('nama_akun')
    ->where('nama_akun', '!=', '')
    ->get();

echo "  Total COA: " . $coas->count() . "\n";
echo "  COA Master (user_id = NULL): " . $coas->where('user_id', null)->count() . "\n";
echo "  COA User (user_id = {$user->id}): " . $coas->where('user_id', $user->id)->count() . "\n\n";

if ($coas->count() > 0) {
    echo "  Sample 5 COA pertama:\n";
    foreach ($coas->take(5) as $coa) {
        $badge = $coa->user_id === null ? '[MASTER]' : '[USER]';
        echo "    {$badge} {$coa->kode_akun} - {$coa->nama_akun}\n";
    }
} else {
    echo "  ❌ TIDAK ADA DATA COA!\n";
}

echo "\n";

// Test 2: Query Satuan dengan global scope
echo "[TEST 2] Query Satuan dengan global scope:\n";
$satuans = \App\Models\Satuan::orderBy('kode', 'asc')->get();

echo "  Total Satuan: " . $satuans->count() . "\n";
echo "  Satuan Master (user_id = NULL): " . $satuans->where('user_id', null)->count() . "\n";
echo "  Satuan User (user_id = {$user->id}): " . $satuans->where('user_id', $user->id)->count() . "\n\n";

if ($satuans->count() > 0) {
    echo "  Sample 5 Satuan pertama:\n";
    foreach ($satuans->take(5) as $satuan) {
        $badge = $satuan->user_id === null ? '[MASTER]' : '[USER]';
        echo "    {$badge} {$satuan->kode} - {$satuan->nama}\n";
    }
} else {
    echo "  ❌ TIDAK ADA DATA SATUAN!\n";
}

echo "\n";

// Test 3: Query langsung ke database (bypass global scope)
echo "[TEST 3] Query langsung ke database (bypass global scope):\n";
$pdo = \DB::connection()->getPdo();

$stmt = $pdo->query("SELECT COUNT(*) as total, 
    SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) as master,
    SUM(CASE WHEN user_id = {$user->id} THEN 1 ELSE 0 END) as user_data
    FROM coas WHERE nama_akun IS NOT NULL AND nama_akun != ''");
$coaStats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "  COA di database:\n";
echo "    Total: {$coaStats['total']}\n";
echo "    Master: {$coaStats['master']}\n";
echo "    User: {$coaStats['user_data']}\n\n";

$stmt = $pdo->query("SELECT COUNT(*) as total,
    SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) as master,
    SUM(CASE WHEN user_id = {$user->id} THEN 1 ELSE 0 END) as user_data
    FROM satuans");
$satuanStats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "  Satuan di database:\n";
echo "    Total: {$satuanStats['total']}\n";
echo "    Master: {$satuanStats['master']}\n";
echo "    User: {$satuanStats['user_data']}\n\n";

// Kesimpulan
echo "==============================================\n";
echo "KESIMPULAN\n";
echo "==============================================\n\n";

if ($coas->count() >= 50 && $satuans->count() >= 16) {
    echo "✅ BERHASIL! Data master muncul dengan benar!\n";
    echo "   - COA: {$coas->count()} records (termasuk " . $coas->where('user_id', null)->count() . " master)\n";
    echo "   - Satuan: {$satuans->count()} records (termasuk " . $satuans->where('user_id', null)->count() . " master)\n\n";
    echo "Silakan refresh browser dan cek halaman:\n";
    echo "  - http://127.0.0.1:8000/master-data/coa\n";
    echo "  - http://127.0.0.1:8000/master-data/satuan-dashboard\n";
} else {
    echo "❌ MASIH ADA MASALAH!\n";
    echo "   Expected: 50 COA, 16 Satuan\n";
    echo "   Got: {$coas->count()} COA, {$satuans->count()} Satuan\n\n";
    
    if ($coaStats['master'] >= 50 && $satuanStats['master'] >= 16) {
        echo "   Data master ADA di database, tapi tidak muncul di query.\n";
        echo "   Kemungkinan masalah di global scope.\n";
    } else {
        echo "   Data master TIDAK ADA di database.\n";
        echo "   Jalankan: php persiapan_hosting_lengkap.php\n";
    }
}

echo "\n";
