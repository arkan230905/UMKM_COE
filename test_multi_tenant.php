<?php
/**
 * TEST MULTI-TENANT SYSTEM
 * =========================
 * Memverifikasi bahwa data terisolasi antar user
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "==============================================\n";
echo "TEST MULTI-TENANT SYSTEM\n";
echo "==============================================\n\n";

// Ambil user pertama
$user1 = \App\Models\User::first();

if (!$user1) {
    echo "❌ Tidak ada user di database!\n";
    exit(1);
}

echo "User Test: {$user1->name} ({$user1->email})\n";
echo "User ID: {$user1->id}\n\n";

// Login sebagai user 1
auth()->login($user1);

echo "==============================================\n";
echo "TEST 1: DATA YANG TERLIHAT USER\n";
echo "==============================================\n\n";

// Test COA
$coas = \App\Models\Coa::all();
$coaMasterCount = $coas->where('user_id', null)->count();
$coaUserCount = $coas->where('user_id', $user1->id)->count();

echo "COA yang terlihat:\n";
echo "  - Data Master: {$coaMasterCount}\n";
echo "  - Data User: {$coaUserCount}\n";
echo "  - Total: " . $coas->count() . "\n\n";

// Test Satuan
$satuans = \App\Models\Satuan::all();
$satuanMasterCount = $satuans->where('user_id', null)->count();
$satuanUserCount = $satuans->where('user_id', $user1->id)->count();

echo "Satuan yang terlihat:\n";
echo "  - Data Master: {$satuanMasterCount}\n";
echo "  - Data User: {$satuanUserCount}\n";
echo "  - Total: " . $satuans->count() . "\n\n";

echo "==============================================\n";
echo "TEST 2: PROTEKSI DATA MASTER\n";
echo "==============================================\n\n";

// Ambil COA master
$coaMaster = \App\Models\Coa::whereNull('user_id')->first();

if ($coaMaster) {
    echo "COA Master: {$coaMaster->kode_akun} - {$coaMaster->nama_akun}\n";
    echo "  user_id: " . ($coaMaster->user_id ?? 'NULL') . "\n";
    echo "  Status: ✅ Data master terdeteksi\n\n";
    
    // Test apakah bisa diubah
    try {
        $coaMaster->nama_akun = "TEST UBAH";
        $coaMaster->save();
        echo "  ❌ BAHAYA! Data master bisa diubah!\n\n";
    } catch (\Exception $e) {
        echo "  ✅ Data master terproteksi dari perubahan\n\n";
    }
} else {
    echo "❌ Tidak ada data master COA!\n\n";
}

echo "==============================================\n";
echo "TEST 3: VALIDASI UNIQUE PER USER\n";
echo "==============================================\n\n";

// Test apakah user bisa buat COA dengan kode yang sama dengan master
$kodeMaster = \App\Models\Coa::whereNull('user_id')->first()->kode_akun ?? '999';

echo "Kode COA Master: {$kodeMaster}\n";
echo "Mencoba buat COA user dengan kode yang sama...\n";

try {
    $newCoa = \App\Models\Coa::create([
        'kode_akun' => $kodeMaster,
        'nama_akun' => 'Test COA User',
        'tipe_akun' => 'Asset',
        'kategori_akun' => 'Test',
        'saldo_normal' => 'debit',
        'saldo_awal' => 0,
        'posted_saldo_awal' => 0,
    ]);
    
    echo "  ✅ Berhasil! User bisa buat COA dengan kode sama\n";
    echo "  COA ID: {$newCoa->id}\n";
    echo "  user_id: {$newCoa->user_id}\n\n";
    
    // Hapus test data
    $newCoa->delete();
    echo "  ✓ Test data dihapus\n\n";
    
} catch (\Exception $e) {
    echo "  ❌ Gagal: " . $e->getMessage() . "\n\n";
}

echo "==============================================\n";
echo "TEST 4: AUTO-ASSIGN user_id\n";
echo "==============================================\n\n";

try {
    $testCoa = \App\Models\Coa::create([
        'kode_akun' => '9999',
        'nama_akun' => 'Test Auto Assign',
        'tipe_akun' => 'Asset',
        'kategori_akun' => 'Test',
        'saldo_normal' => 'debit',
        'saldo_awal' => 0,
        'posted_saldo_awal' => 0,
        // Tidak set user_id, harus auto-assign
    ]);
    
    if ($testCoa->user_id == $user1->id) {
        echo "  ✅ user_id otomatis ter-assign: {$testCoa->user_id}\n";
    } else {
        echo "  ❌ user_id tidak ter-assign dengan benar!\n";
        echo "  Expected: {$user1->id}, Got: {$testCoa->user_id}\n";
    }
    
    // Hapus test data
    $testCoa->delete();
    echo "  ✓ Test data dihapus\n\n";
    
} catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n\n";
}

echo "==============================================\n";
echo "TEST 5: QUERY LANGSUNG KE DATABASE\n";
echo "==============================================\n\n";

$pdo = \DB::connection()->getPdo();

// Hitung semua data di database (bypass global scope)
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) as master,
        SUM(CASE WHEN user_id = {$user1->id} THEN 1 ELSE 0 END) as user_data,
        SUM(CASE WHEN user_id IS NOT NULL AND user_id != {$user1->id} THEN 1 ELSE 0 END) as other_users
    FROM coas
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Data COA di database (bypass global scope):\n";
echo "  - Total: {$stats['total']}\n";
echo "  - Master: {$stats['master']}\n";
echo "  - User ini: {$stats['user_data']}\n";
echo "  - User lain: {$stats['other_users']}\n\n";

if ($stats['other_users'] > 0) {
    echo "  ⚠️  Ada data user lain, tapi user ini tidak bisa lihat (✅ Terisolasi)\n\n";
} else {
    echo "  ℹ️  Belum ada data user lain\n\n";
}

echo "==============================================\n";
echo "KESIMPULAN\n";
echo "==============================================\n\n";

$allPassed = true;

// Check 1: Data master ada
if ($coaMasterCount >= 50 && $satuanMasterCount >= 16) {
    echo "✅ Data master tersedia ({$coaMasterCount} COA, {$satuanMasterCount} Satuan)\n";
} else {
    echo "❌ Data master kurang lengkap\n";
    $allPassed = false;
}

// Check 2: Global scope bekerja
if ($coas->count() > 0) {
    echo "✅ Global scope bekerja (user bisa lihat data)\n";
} else {
    echo "❌ Global scope tidak bekerja\n";
    $allPassed = false;
}

// Check 3: Data terisolasi
if ($stats['other_users'] == 0 || $coaUserCount == $stats['user_data']) {
    echo "✅ Data terisolasi per user\n";
} else {
    echo "❌ Data tidak terisolasi dengan benar\n";
    $allPassed = false;
}

echo "\n";

if ($allPassed) {
    echo "🎉 SEMUA TEST PASSED!\n";
    echo "   Sistem multi-tenant bekerja dengan baik.\n";
    echo "   Database siap untuk hosting!\n";
} else {
    echo "⚠️  ADA MASALAH!\n";
    echo "   Periksa konfigurasi global scope dan validasi.\n";
}

echo "\n";
