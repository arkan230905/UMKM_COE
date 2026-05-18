<?php

/**
 * Script untuk mencari user_id yang tepat melalui terminal
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\JurnalUmum;
use App\Models\BopProses;
use App\Models\Produksi;

echo "=== PENCARIAN USER ID YANG TEPAT ===\n";
echo "Tanggal: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Tampilkan semua user
echo "1. DAFTAR SEMUA USER\n";
echo "====================\n";

$users = User::orderBy('id')->get();

foreach ($users as $user) {
    echo "ID: {$user->id} | Nama: {$user->name} | Email: {$user->email}\n";
}

echo "\n";

// 2. Cari user yang memiliki jurnal BOP bermasalah
echo "2. USER DENGAN JURNAL BOP BERMASALAH\n";
echo "====================================\n";

$problematicUsers = JurnalUmum::with(['user', 'coa'])
    ->whereHas('coa', function($q) {
        $q->where('kode_akun', '531'); // BOP - Susu
    })
    ->where('keterangan', 'LIKE', '%Keju%')
    ->where('tipe_referensi', 'produksi_bop')
    ->get()
    ->groupBy('user_id');

if ($problematicUsers->count() > 0) {
    echo "Ditemukan user dengan masalah BOP COA:\n\n";
    
    foreach ($problematicUsers as $userId => $journals) {
        $user = $journals->first()->user;
        $totalAmount = $journals->sum('kredit');
        
        echo "🔍 USER ID: $userId\n";
        echo "   Nama: {$user->name}\n";
        echo "   Email: {$user->email}\n";
        echo "   Jumlah jurnal bermasalah: {$journals->count()}\n";
        echo "   Total amount: Rp " . number_format($totalAmount, 0, ',', '.') . "\n";
        echo "   ❌ MASALAH: Komponen Keju menggunakan COA 531 (BOP - Susu)\n";
        echo "   ✅ SOLUSI: php artisan fix:bop-coa-mapping $userId\n\n";
    }
} else {
    echo "Tidak ada user dengan masalah BOP COA ditemukan.\n\n";
}

// 3. Cari user yang memiliki data BOP proses
echo "3. USER DENGAN DATA BOP PROSES\n";
echo "==============================\n";

$usersWithBop = User::whereHas('bopProses')->with('bopProses')->get();

if ($usersWithBop->count() > 0) {
    foreach ($usersWithBop as $user) {
        $bopCount = $user->bopProses->count();
        echo "ID: {$user->id} | Nama: {$user->name} | Email: {$user->email} | BOP Proses: $bopCount\n";
    }
} else {
    echo "Tidak ada user dengan data BOP proses.\n";
}

echo "\n";

// 4. Cari user yang memiliki data produksi
echo "4. USER DENGAN DATA PRODUKSI\n";
echo "============================\n";

$usersWithProduction = User::whereHas('produksis')->withCount('produksis')->get();

if ($usersWithProduction->count() > 0) {
    foreach ($usersWithProduction as $user) {
        echo "ID: {$user->id} | Nama: {$user->name} | Email: {$user->email} | Produksi: {$user->produksis_count}\n";
    }
} else {
    echo "Tidak ada user dengan data produksi.\n";
}

echo "\n";

// 5. Rekomendasi user_id
echo "5. REKOMENDASI USER_ID\n";
echo "======================\n";

if ($problematicUsers->count() > 0) {
    $recommendedUserId = $problematicUsers->keys()->first();
    $recommendedUser = User::find($recommendedUserId);
    
    echo "🎯 REKOMENDASI: Gunakan USER_ID $recommendedUserId\n";
    echo "   Nama: {$recommendedUser->name}\n";
    echo "   Email: {$recommendedUser->email}\n";
    echo "   Alasan: User ini memiliki jurnal BOP yang bermasalah\n\n";
    
    echo "🚀 JALANKAN PERBAIKAN:\n";
    echo "   php artisan fix:bop-coa-mapping $recommendedUserId\n\n";
} else {
    // Jika tidak ada yang bermasalah, cari user dengan data BOP terbanyak
    $userWithMostBop = User::withCount(['bopProses', 'produksis'])
        ->having('bop_proses_count', '>', 0)
        ->orHaving('produksis_count', '>', 0)
        ->orderByDesc('bop_proses_count')
        ->orderByDesc('produksis_count')
        ->first();
    
    if ($userWithMostBop) {
        echo "🎯 REKOMENDASI: Gunakan USER_ID {$userWithMostBop->id}\n";
        echo "   Nama: {$userWithMostBop->name}\n";
        echo "   Email: {$userWithMostBop->email}\n";
        echo "   Alasan: User dengan data BOP/produksi terbanyak\n\n";
        
        echo "🚀 JALANKAN PERBAIKAN:\n";
        echo "   php artisan fix:bop-coa-mapping {$userWithMostBop->id}\n\n";
    } else {
        echo "❌ Tidak dapat menentukan user_id yang tepat.\n";
        echo "   Silakan pilih manual dari daftar user di atas.\n\n";
    }
}

// 6. Cara manual jika tidak yakin
echo "6. CARA MANUAL JIKA TIDAK YAKIN\n";
echo "===============================\n";
echo "Jika Anda tidak yakin user_id mana yang tepat:\n";
echo "1. Lihat daftar user di atas\n";
echo "2. Pilih user_id yang sesuai dengan nama/email Anda\n";
echo "3. Atau pilih user_id yang memiliki data BOP/produksi\n";
echo "4. Jalankan: php artisan fix:bop-coa-mapping USER_ID\n\n";

echo "=== SELESAI ===\n";