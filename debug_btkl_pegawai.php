<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG BTKL PEGAWAI ===\n\n";

$userId = 20; // Your user ID

echo "Checking for user_id: {$userId}\n\n";

// Check Jabatan BTKL
echo "=== JABATAN BTKL ===\n";
$jabatanBtkl = DB::table('jabatans')
    ->where('user_id', $userId)
    ->where('kategori', 'btkl')
    ->get(['id', 'nama', 'kategori', 'tarif', 'user_id']);

echo "Found " . $jabatanBtkl->count() . " jabatan BTKL\n";
foreach ($jabatanBtkl as $j) {
    echo "  - ID: {$j->id}, Nama: {$j->nama}, Tarif: {$j->tarif}, User: {$j->user_id}\n";
}

// Check Pegawai
echo "\n=== PEGAWAI ===\n";
$pegawais = DB::table('pegawais')
    ->where('user_id', $userId)
    ->get(['id', 'nama', 'jabatan_id', 'user_id']);

echo "Found " . $pegawais->count() . " pegawai\n";
foreach ($pegawais as $p) {
    $jabatan = DB::table('jabatans')->where('id', $p->jabatan_id)->first();
    $jabatanNama = $jabatan ? $jabatan->nama : 'NULL';
    $jabatanKategori = $jabatan ? $jabatan->kategori : 'NULL';
    echo "  - ID: {$p->id}, Nama: {$p->nama}, Jabatan ID: {$p->jabatan_id} ({$jabatanNama} - {$jabatanKategori}), User: {$p->user_id}\n";
}

// Check Pegawai per Jabatan BTKL
echo "\n=== PEGAWAI PER JABATAN BTKL ===\n";
foreach ($jabatanBtkl as $j) {
    $count = DB::table('pegawais')
        ->where('user_id', $userId)
        ->where('jabatan_id', $j->id)
        ->count();
    
    echo "Jabatan: {$j->nama} (ID: {$j->id})\n";
    echo "  Jumlah pegawai: {$count}\n";
    
    if ($count > 0) {
        $pegawaiList = DB::table('pegawais')
            ->where('user_id', $userId)
            ->where('jabatan_id', $j->id)
            ->get(['id', 'nama']);
        
        foreach ($pegawaiList as $p) {
            echo "    - {$p->nama} (ID: {$p->id})\n";
        }
    }
    echo "\n";
}

// Check BTKL records
echo "\n=== BTKL RECORDS ===\n";
$btkls = DB::table('btkls')
    ->where('user_id', $userId)
    ->get(['id', 'kode_proses', 'nama_btkl', 'jabatan_id', 'tarif_per_jam']);

echo "Found " . $btkls->count() . " BTKL records\n";
foreach ($btkls as $b) {
    $jabatan = DB::table('jabatans')->where('id', $b->jabatan_id)->first();
    $jabatanNama = $jabatan ? $jabatan->nama : 'NULL';
    
    $pegawaiCount = DB::table('pegawais')
        ->where('user_id', $userId)
        ->where('jabatan_id', $b->jabatan_id)
        ->count();
    
    echo "  - {$b->kode_proses}: {$b->nama_btkl}\n";
    echo "    Jabatan: {$jabatanNama} (ID: {$b->jabatan_id})\n";
    echo "    Tarif: {$b->tarif_per_jam}\n";
    echo "    Pegawai count: {$pegawaiCount}\n\n";
}

// Test Eloquent relationship
echo "\n=== TEST ELOQUENT RELATIONSHIP ===\n";
$jabatanModel = \App\Models\Jabatan::where('user_id', $userId)
    ->where('kategori', 'btkl')
    ->first();

if ($jabatanModel) {
    echo "Testing Jabatan: {$jabatanModel->nama} (ID: {$jabatanModel->id})\n";
    echo "User ID: {$jabatanModel->user_id}\n";
    
    // Test pegawais relationship
    $pegawaisRelation = $jabatanModel->pegawais;
    echo "Pegawais count via relationship: " . $pegawaisRelation->count() . "\n";
    
    foreach ($pegawaisRelation as $p) {
        echo "  - {$p->nama} (User ID: {$p->user_id})\n";
    }
    
    // Test direct query
    $pegawaisDirect = DB::table('pegawais')
        ->where('jabatan_id', $jabatanModel->id)
        ->where('user_id', $userId)
        ->get();
    
    echo "\nPegawais count via direct query: " . $pegawaisDirect->count() . "\n";
}

echo "\n=== DONE ===\n";
