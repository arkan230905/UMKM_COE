<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Checking Bahan Baku Issue for User 4\n";
echo "==========================================\n\n";

// Check Bahan Baku
echo "📦 BAHAN BAKU:\n";
$bahanBakus = DB::table('bahan_bakus')
    ->where('user_id', 4)
    ->get(['id', 'nama_bahan', 'satuan_id', 'user_id']);

foreach ($bahanBakus as $bahan) {
    $satuan = DB::table('satuans')->where('id', $bahan->satuan_id)->first();
    
    echo "ID: {$bahan->id} | Nama: {$bahan->nama_bahan} | Satuan ID: {$bahan->satuan_id} | ";
    
    if ($satuan) {
        echo "Satuan: {$satuan->nama} (user_id: {$satuan->user_id})\n";
    } else {
        echo "Satuan: NOT FOUND ❌\n";
    }
}

echo "\n";

// Check Bahan Pendukung
echo "🔧 BAHAN PENDUKUNG:\n";
$bahanPendukungs = DB::table('bahan_pendukungs')
    ->where('user_id', 4)
    ->get(['id', 'nama_bahan', 'satuan_id', 'user_id']);

foreach ($bahanPendukungs as $bahan) {
    $satuan = DB::table('satuans')->where('id', $bahan->satuan_id)->first();
    
    echo "ID: {$bahan->id} | Nama: {$bahan->nama_bahan} | Satuan ID: {$bahan->satuan_id} | ";
    
    if ($satuan) {
        echo "Satuan: {$satuan->nama} (user_id: {$satuan->user_id})\n";
    } else {
        echo "Satuan: NOT FOUND ❌\n";
    }
}

echo "\n";

// Check Satuan for User 4
echo "📊 SATUAN USER 4:\n";
$satuans = DB::table('satuans')
    ->where('user_id', 4)
    ->get(['id', 'kode', 'nama', 'user_id']);

echo "Total: " . $satuans->count() . " satuan\n";
foreach ($satuans as $satuan) {
    echo "ID: {$satuan->id} | Kode: {$satuan->kode} | Nama: {$satuan->nama}\n";
}

echo "\n";

// Check for orphaned satuan_id
echo "🔍 CHECKING FOR ISSUES:\n";
$orphanedBahanBaku = DB::table('bahan_bakus as bb')
    ->leftJoin('satuans as s', 'bb.satuan_id', '=', 's.id')
    ->whereNull('s.id')
    ->where('bb.user_id', 4)
    ->count();

$orphanedBahanPendukung = DB::table('bahan_pendukungs as bp')
    ->leftJoin('satuans as s', 'bp.satuan_id', '=', 's.id')
    ->whereNull('s.id')
    ->where('bp.user_id', 4)
    ->count();

echo "Bahan Baku with missing Satuan: {$orphanedBahanBaku}\n";
echo "Bahan Pendukung with missing Satuan: {$orphanedBahanPendukung}\n";

if ($orphanedBahanBaku > 0 || $orphanedBahanPendukung > 0) {
    echo "\n⚠️ PROBLEM FOUND: Some bahan have satuan_id that don't exist!\n";
    echo "This is why 'Tidak Diketahui' appears.\n";
}
