<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Checking ALL Bahan Baku & Pendukung\n";
echo "======================================\n\n";

// Check ALL Bahan Baku
echo "📦 ALL BAHAN BAKU:\n";
$bahanBakus = DB::table('bahan_bakus')
    ->get(['id', 'nama_bahan', 'satuan_id', 'user_id']);

echo "Total: " . $bahanBakus->count() . " records\n\n";

foreach ($bahanBakus as $bahan) {
    $satuan = DB::table('satuans')->where('id', $bahan->satuan_id)->first();
    
    echo "ID: {$bahan->id} | User: {$bahan->user_id} | Nama: {$bahan->nama_bahan} | Satuan ID: {$bahan->satuan_id} | ";
    
    if ($satuan) {
        echo "Satuan: {$satuan->nama} (satuan user_id: {$satuan->user_id})\n";
    } else {
        echo "Satuan: NOT FOUND ❌\n";
    }
}

echo "\n";

// Check ALL Bahan Pendukung
echo "🔧 ALL BAHAN PENDUKUNG:\n";
$bahanPendukungs = DB::table('bahan_pendukungs')
    ->get(['id', 'nama_bahan', 'satuan_id', 'user_id']);

echo "Total: " . $bahanPendukungs->count() . " records\n\n";

foreach ($bahanPendukungs as $bahan) {
    $satuan = DB::table('satuans')->where('id', $bahan->satuan_id)->first();
    
    echo "ID: {$bahan->id} | User: {$bahan->user_id} | Nama: {$bahan->nama_bahan} | Satuan ID: {$bahan->satuan_id} | ";
    
    if ($satuan) {
        echo "Satuan: {$satuan->nama} (satuan user_id: {$satuan->user_id})\n";
    } else {
        echo "Satuan: NOT FOUND ❌\n";
    }
}

echo "\n";

// Check for mismatched user_id
echo "⚠️ CHECKING FOR USER_ID MISMATCH:\n";
$mismatchBahanBaku = DB::table('bahan_bakus as bb')
    ->join('satuans as s', 'bb.satuan_id', '=', 's.id')
    ->whereColumn('bb.user_id', '!=', 's.user_id')
    ->select('bb.id', 'bb.nama_bahan', 'bb.user_id as bahan_user', 's.user_id as satuan_user')
    ->get();

$mismatchBahanPendukung = DB::table('bahan_pendukungs as bp')
    ->join('satuans as s', 'bp.satuan_id', '=', 's.id')
    ->whereColumn('bp.user_id', '!=', 's.user_id')
    ->select('bp.id', 'bp.nama_bahan', 'bp.user_id as bahan_user', 's.user_id as satuan_user')
    ->get();

if ($mismatchBahanBaku->count() > 0) {
    echo "\n❌ BAHAN BAKU with mismatched user_id:\n";
    foreach ($mismatchBahanBaku as $item) {
        echo "  ID: {$item->id} | {$item->nama_bahan} | Bahan user: {$item->bahan_user} | Satuan user: {$item->satuan_user}\n";
    }
}

if ($mismatchBahanPendukung->count() > 0) {
    echo "\n❌ BAHAN PENDUKUNG with mismatched user_id:\n";
    foreach ($mismatchBahanPendukung as $item) {
        echo "  ID: {$item->id} | {$item->nama_bahan} | Bahan user: {$item->bahan_user} | Satuan user: {$item->satuan_user}\n";
    }
}

if ($mismatchBahanBaku->count() == 0 && $mismatchBahanPendukung->count() == 0) {
    echo "✅ No user_id mismatch found\n";
}
