<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING DEDI GUNAWAN DATA ===\n\n";

// 1. Find Dedi Gunawan in pegawais table
$dedi = DB::table('pegawais')
    ->where('nama', 'like', '%Dedi%')
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
    
    // 2. Search for matching jabatan by name
    echo "=== SEARCHING FOR MATCHING JABATAN ===\n";
    $jabatanByName = DB::table('jabatans')
        ->where('nama', 'like', '%Bagian Gudang%')
        ->orWhere('nama', 'like', '%Gudang%')
        ->get();
    
    if ($jabatanByName->count() > 0) {
        echo "JABATAN FOUND BY NAME:\n";
        foreach ($jabatanByName as $j) {
            echo "---\n";
            echo "ID: {$j->id}\n";
            echo "Nama: {$j->nama}\n";
            echo "Kategori: {$j->kategori}\n";
            echo "Gaji Pokok: " . number_format($j->gaji_pokok ?? 0, 0, ',', '.') . "\n";
            echo "Gaji: " . number_format($j->gaji ?? 0, 0, ',', '.') . "\n";
            echo "Tarif per Jam: " . number_format($j->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
            echo "Tarif: " . number_format($j->tarif ?? 0, 0, ',', '.') . "\n";
            echo "Tunjangan: " . number_format($j->tunjangan ?? 0, 0, ',', '.') . "\n";
            echo "Tunjangan Transport: " . number_format($j->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
            echo "Tunjangan Konsumsi: " . number_format($j->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
            echo "Asuransi: " . number_format($j->asuransi ?? 0, 0, ',', '.') . "\n";
        }
    } else {
        echo "No jabatan found with name containing 'Bagian Gudang' or 'Gudang'\n";
    }
    
    echo "\n=== ALL JABATANS ===\n";
    $allJabatans = DB::table('jabatans')
        ->orderBy('nama')
        ->get();
    
    foreach ($allJabatans as $j) {
        echo "ID: {$j->id} | Nama: {$j->nama} | Kategori: {$j->kategori} | Gaji: " . number_format($j->gaji ?? $j->gaji_pokok ?? 0, 0, ',', '.') . " | Tarif: " . number_format($j->tarif ?? $j->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
    }
    
    echo "\n=== PROBLEM ANALYSIS ===\n";
    echo "MASALAH DITEMUKAN:\n";
    echo "1. Dedi Gunawan memiliki jabatan_id = NULL\n";
    echo "2. Sistem penggajian menggunakan jabatanRelasi (jabatan_id) untuk mengambil data gaji\n";
    echo "3. Karena jabatan_id = NULL, maka data gaji tidak terbaca\n";
    echo "4. Fallback ke data pegawai langsung juga menunjukkan gaji_pokok = 0\n";
    echo "\nSOLUSI:\n";
    echo "1. Set jabatan_id untuk Dedi Gunawan ke jabatan yang sesuai\n";
    echo "2. Atau update gaji_pokok langsung di tabel pegawais\n";
    
} else {
    echo "Dedi Gunawan NOT FOUND!\n";
}

echo "\n=== CHECK COMPLETE ===\n";