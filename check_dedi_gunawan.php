<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING DEDI GUNAWAN DATA ===\n\n";

// 1. Find Dedi Gunawan in pegawais table
$dedi = DB::table('pegawais')
    ->where('nama', 'like', '%Dedi%')
    ->orWhere('nama', 'like', '%dedi%')
    ->first();

if ($dedi) {
    echo "PEGAWAI FOUND:\n";
    echo "ID: {$dedi->id}\n";
    echo "Nama: {$dedi->nama}\n";
    echo "Jabatan (text): {$dedi->jabatan}\n";
    echo "Jabatan ID: " . ($dedi->jabatan_id ?? 'NULL') . "\n";
    echo "Jenis Pegawai: " . ($dedi->jenis_pegawai ?? 'NULL') . "\n";
    echo "Kategori: " . ($dedi->kategori ?? 'NULL') . "\n";
    echo "Gaji Pokok: " . number_format($dedi->gaji_pokok ?? 0, 0, ',', '.') . "\n";
    echo "Tarif per Jam: " . number_format($dedi->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
    echo "\n";
    
    // 2. Check if jabatan_id exists and get jabatan data
    if ($dedi->jabatan_id) {
        echo "=== JABATAN DATA (from jabatan_id) ===\n";
        $jabatan = DB::table('jabatans')
            ->where('id', $dedi->jabatan_id)
            ->first();
        
        if ($jabatan) {
            echo "Jabatan ID: {$jabatan->id}\n";
            echo "Nama Jabatan: {$jabatan->nama_jabatan}\n";
            echo "Jenis: " . ($jabatan->jenis ?? 'NULL') . "\n";
            echo "Gaji: " . number_format($jabatan->gaji ?? 0, 0, ',', '.') . "\n";
            echo "Tarif: " . number_format($jabatan->tarif ?? 0, 0, ',', '.') . "\n";
            echo "Tunjangan: " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.') . "\n";
            echo "Tunjangan Transport: " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
            echo "Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
            echo "Asuransi: " . number_format($jabatan->asuransi ?? 0, 0, ',', '.') . "\n";
        } else {
            echo "Jabatan with ID {$dedi->jabatan_id} NOT FOUND!\n";
        }
    } else {
        echo "=== NO JABATAN_ID ===\n";
        echo "Dedi Gunawan tidak memiliki jabatan_id (NULL)\n";
        echo "Mencari jabatan berdasarkan nama...\n\n";
        
        // Search by jabatan name
        $jabatanByName = DB::table('jabatans')
            ->where('nama_jabatan', 'like', '%Bagian Gudang%')
            ->orWhere('nama_jabatan', 'like', '%Gudang%')
            ->get();
        
        if ($jabatanByName->count() > 0) {
            echo "JABATAN FOUND BY NAME:\n";
            foreach ($jabatanByName as $j) {
                echo "---\n";
                echo "ID: {$j->id}\n";
                echo "Nama: {$j->nama_jabatan}\n";
                echo "Jenis: " . ($j->jenis ?? 'NULL') . "\n";
                echo "Gaji: " . number_format($j->gaji ?? 0, 0, ',', '.') . "\n";
                echo "Tarif: " . number_format($j->tarif ?? 0, 0, ',', '.') . "\n";
            }
        } else {
            echo "No jabatan found with name 'Bagian Gudang' or 'Gudang'\n";
        }
    }
    
    echo "\n=== ALL JABATANS ===\n";
    $allJabatans = DB::table('jabatans')
        ->orderBy('nama_jabatan')
        ->get();
    
    foreach ($allJabatans as $j) {
        echo "ID: {$j->id} | Nama: {$j->nama_jabatan} | Jenis: " . ($j->jenis ?? 'NULL') . " | Gaji: " . number_format($j->gaji ?? 0, 0, ',', '.') . " | Tarif: " . number_format($j->tarif ?? 0, 0, ',', '.') . "\n";
    }
    
} else {
    echo "Dedi Gunawan NOT FOUND in pegawais table!\n\n";
    
    echo "=== ALL PEGAWAIS ===\n";
    $allPegawais = DB::table('pegawais')
        ->orderBy('nama')
        ->get();
    
    foreach ($allPegawais as $p) {
        echo "ID: {$p->id} | Nama: {$p->nama} | Jabatan: {$p->jabatan} | Jabatan ID: " . ($p->jabatan_id ?? 'NULL') . "\n";
    }
}

echo "\n=== CHECK COMPLETE ===\n";
