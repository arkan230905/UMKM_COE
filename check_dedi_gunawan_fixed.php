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
    
    // 2. Check jabatans table structure first
    echo "=== JABATANS TABLE STRUCTURE ===\n";
    $columns = DB::select("DESCRIBE jabatans");
    foreach ($columns as $col) {
        echo "Column: {$col->Field} | Type: {$col->Type}\n";
    }
    echo "\n";
    
    // 3. Check if jabatan_id exists and get jabatan data
    if ($dedi->jabatan_id) {
        echo "=== JABATAN DATA (from jabatan_id) ===\n";
        $jabatan = DB::table('jabatans')
            ->where('id', $dedi->jabatan_id)
            ->first();
        
        if ($jabatan) {
            echo "Jabatan ID: {$jabatan->id}\n";
            // Use correct column name based on table structure
            $namaJabatan = $jabatan->nama ?? $jabatan->jabatan ?? $jabatan->nama_jabatan ?? 'Unknown';
            echo "Nama Jabatan: {$namaJabatan}\n";
            echo "Jenis: " . ($jabatan->jenis ?? 'NULL') . "\n";
            echo "Gaji: " . number_format($jabatan->gaji ?? $jabatan->gaji_pokok ?? 0, 0, ',', '.') . "\n";
            echo "Tarif: " . number_format($jabatan->tarif ?? $jabatan->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
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
        
        // Search by jabatan name using correct column
        $jabatanByName = DB::table('jabatans')
            ->where(function($query) {
                $query->where('nama', 'like', '%Bagian Gudang%')
                      ->orWhere('nama', 'like', '%Gudang%')
                      ->orWhere('jabatan', 'like', '%Bagian Gudang%')
                      ->orWhere('jabatan', 'like', '%Gudang%');
            })
            ->get();
        
        if ($jabatanByName->count() > 0) {
            echo "JABATAN FOUND BY NAME:\n";
            foreach ($jabatanByName as $j) {
                echo "---\n";
                echo "ID: {$j->id}\n";
                $namaJabatan = $j->nama ?? $j->jabatan ?? $j->nama_jabatan ?? 'Unknown';
                echo "Nama: {$namaJabatan}\n";
                echo "Jenis: " . ($j->jenis ?? 'NULL') . "\n";
                echo "Gaji: " . number_format($j->gaji ?? $j->gaji_pokok ?? 0, 0, ',', '.') . "\n";
                echo "Tarif: " . number_format($j->tarif ?? $j->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
            }
        } else {
            echo "No jabatan found with name 'Bagian Gudang' or 'Gudang'\n";
        }
    }
    
    echo "\n=== ALL JABATANS ===\n";
    $allJabatans = DB::table('jabatans')
        ->get();
    
    foreach ($allJabatans as $j) {
        $namaJabatan = $j->nama ?? $j->jabatan ?? $j->nama_jabatan ?? 'Unknown';
        echo "ID: {$j->id} | Nama: {$namaJabatan} | Jenis: " . ($j->jenis ?? 'NULL') . " | Gaji: " . number_format($j->gaji ?? $j->gaji_pokok ?? 0, 0, ',', '.') . " | Tarif: " . number_format($j->tarif ?? $j->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
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