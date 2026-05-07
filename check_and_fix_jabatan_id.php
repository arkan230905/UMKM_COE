<?php

/**
 * Script untuk cek dan fix jabatan_id di tabel pegawais
 * Masalah: jabatan_id NULL sehingga tunjangan tidak terbaca
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK DAN FIX JABATAN_ID ===\n\n";

// 1. Cek pegawai yang jabatan_id nya NULL
$pegawaisNullJabatan = DB::table('pegawais')
    ->whereNull('jabatan_id')
    ->orWhere('jabatan_id', 0)
    ->get();

echo "Total pegawai dengan jabatan_id NULL/0: " . $pegawaisNullJabatan->count() . "\n\n";

if ($pegawaisNullJabatan->isEmpty()) {
    echo "✅ Semua pegawai sudah memiliki jabatan_id!\n";
    
    // Tampilkan data pegawai untuk verifikasi
    echo "\nData pegawai saat ini:\n";
    echo str_repeat("-", 120) . "\n";
    printf("%-5s %-20s %-20s %-10s %-15s %-15s %-15s\n", 
        "ID", "Nama", "Jabatan", "Jabatan ID", "Tunjangan Jab", "Tunjangan Trans", "Tunjangan Kons");
    echo str_repeat("-", 120) . "\n";
    
    $allPegawais = DB::table('pegawais')
        ->leftJoin('jabatans', 'pegawais.jabatan_id', '=', 'jabatans.id')
        ->select('pegawais.*', 'jabatans.nama as nama_jabatan', 'jabatans.tunjangan', 'jabatans.tunjangan_transport', 'jabatans.tunjangan_konsumsi')
        ->get();
    
    foreach ($allPegawais as $p) {
        printf("%-5s %-20s %-20s %-10s %-15s %-15s %-15s\n",
            $p->id,
            substr($p->nama, 0, 20),
            substr($p->nama_jabatan ?? $p->jabatan ?? 'N/A', 0, 20),
            $p->jabatan_id ?? 'NULL',
            number_format($p->tunjangan ?? 0, 0, ',', '.'),
            number_format($p->tunjangan_transport ?? 0, 0, ',', '.'),
            number_format($p->tunjangan_konsumsi ?? 0, 0, ',', '.')
        );
    }
    echo str_repeat("-", 120) . "\n";
    
    exit(0);
}

// 2. Tampilkan detail pegawai yang bermasalah
echo "Detail pegawai dengan jabatan_id NULL:\n";
echo str_repeat("-", 100) . "\n";
printf("%-5s %-20s %-20s %-10s %-10s\n", 
    "ID", "Nama", "Jabatan (string)", "User ID", "Jabatan ID");
echo str_repeat("-", 100) . "\n";

foreach ($pegawaisNullJabatan as $p) {
    printf("%-5s %-20s %-20s %-10s %-10s\n",
        $p->id,
        substr($p->nama, 0, 20),
        substr($p->jabatan ?? 'N/A', 0, 20),
        $p->user_id,
        $p->jabatan_id ?? 'NULL'
    );
}
echo str_repeat("-", 100) . "\n\n";

// 3. Fix jabatan_id berdasarkan nama jabatan
echo "Memperbaiki jabatan_id...\n";

$fixed = 0;
$notFound = 0;

foreach ($pegawaisNullJabatan as $pegawai) {
    if (empty($pegawai->jabatan)) {
        echo "⚠️  Pegawai ID {$pegawai->id} ({$pegawai->nama}) tidak memiliki nama jabatan\n";
        $notFound++;
        continue;
    }
    
    // Cari jabatan berdasarkan nama dan user_id
    $jabatan = DB::table('jabatans')
        ->where('user_id', $pegawai->user_id)
        ->where(function($query) use ($pegawai) {
            $query->where('nama', $pegawai->jabatan)
                  ->orWhere('nama', 'LIKE', '%' . $pegawai->jabatan . '%');
        })
        ->first();
    
    if ($jabatan) {
        // Update jabatan_id
        DB::table('pegawais')
            ->where('id', $pegawai->id)
            ->update(['jabatan_id' => $jabatan->id]);
        
        echo "✅ Pegawai ID {$pegawai->id} ({$pegawai->nama}) → Jabatan ID {$jabatan->id} ({$jabatan->nama})\n";
        echo "   Tunjangan: Jabatan Rp " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.') . 
             ", Transport Rp " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') .
             ", Konsumsi Rp " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        $fixed++;
    } else {
        echo "❌ Pegawai ID {$pegawai->id} ({$pegawai->nama}) - Jabatan '{$pegawai->jabatan}' tidak ditemukan\n";
        $notFound++;
    }
}

echo "\n=== SELESAI ===\n";
echo "Total diperbaiki: $fixed\n";
echo "Total tidak ditemukan: $notFound\n";

// 4. Verifikasi hasil
$remaining = DB::table('pegawais')
    ->whereNull('jabatan_id')
    ->orWhere('jabatan_id', 0)
    ->count();

if ($remaining == 0) {
    echo "✅ Semua pegawai sudah memiliki jabatan_id!\n";
} else {
    echo "⚠️  Masih ada $remaining pegawai tanpa jabatan_id.\n";
}

echo "\n";
