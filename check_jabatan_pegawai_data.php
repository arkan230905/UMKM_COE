<?php
/**
 * CHECK JABATAN vs PEGAWAI DATA
 * 
 * Script untuk mengecek inkonsistensi data antara jabatans dan pegawais
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECK JABATAN vs PEGAWAI DATA ===\n\n";

$userId = 1; // User ID yang sedang login

// 1. Check Jabatans BTKL
echo "1. JABATANS (user_id=$userId, kategori=BTKL):\n";
$jabatans = DB::table('jabatans')
    ->where('user_id', $userId)
    ->where('kategori', 'btkl')
    ->orderBy('nama')
    ->get(['id', 'nama', 'kategori', 'tarif']);

foreach ($jabatans as $j) {
    echo "   ID: {$j->id} | Nama: {$j->nama} | Tarif: Rp " . number_format($j->tarif) . "\n";
}

if ($jabatans->isEmpty()) {
    echo "   (Tidak ada data jabatan BTKL)\n";
}

echo "\n";

// 2. Check Pegawais
echo "2. PEGAWAIS (user_id=$userId):\n";
$pegawais = DB::table('pegawais')
    ->where('user_id', $userId)
    ->orderBy('nama')
    ->get(['id', 'nama', 'jabatan', 'jabatan_id', 'kategori']);

foreach ($pegawais as $p) {
    $jabatanId = $p->jabatan_id ?? 'NULL';
    echo "   ID: {$p->id} | Nama: {$p->nama} | Jabatan: {$p->jabatan} | Jabatan_ID: {$jabatanId} | Kategori: {$p->kategori}\n";
}

if ($pegawais->isEmpty()) {
    echo "   (Tidak ada data pegawai)\n";
}

echo "\n";

// 3. Check Pegawais BTKL specifically
echo "3. PEGAWAIS BTKL (user_id=$userId, kategori=btkl):\n";
$pegawaisBtkl = DB::table('pegawais')
    ->where('user_id', $userId)
    ->where('kategori', 'btkl')
    ->orderBy('nama')
    ->get(['id', 'nama', 'jabatan', 'jabatan_id']);

foreach ($pegawaisBtkl as $p) {
    $jabatanId = $p->jabatan_id ?? 'NULL';
    
    // Check if jabatan_id exists in jabatans table
    if ($jabatanId !== 'NULL') {
        $jabatanExists = DB::table('jabatans')
            ->where('id', $jabatanId)
            ->where('user_id', $userId)
            ->exists();
        $status = $jabatanExists ? '✅' : '❌ JABATAN NOT FOUND';
    } else {
        $status = '⚠️  NO JABATAN_ID';
    }
    
    echo "   {$status} | Pegawai: {$p->nama} | Jabatan: {$p->jabatan} | Jabatan_ID: {$jabatanId}\n";
}

if ($pegawaisBtkl->isEmpty()) {
    echo "   (Tidak ada pegawai BTKL)\n";
}

echo "\n";

// 4. Check for inconsistencies
echo "4. CHECKING INCONSISTENCIES:\n\n";

// 4a. Pegawai dengan jabatan_id NULL
$pegawaiNoJabatanId = DB::table('pegawais')
    ->where('user_id', $userId)
    ->whereNull('jabatan_id')
    ->count();

if ($pegawaiNoJabatanId > 0) {
    echo "   ⚠️  WARNING: {$pegawaiNoJabatanId} pegawai tidak memiliki jabatan_id\n";
    
    $pegawaisNoId = DB::table('pegawais')
        ->where('user_id', $userId)
        ->whereNull('jabatan_id')
        ->get(['id', 'nama', 'jabatan']);
    
    foreach ($pegawaisNoId as $p) {
        echo "      - {$p->nama} (jabatan: {$p->jabatan})\n";
    }
    echo "\n";
} else {
    echo "   ✅ Semua pegawai memiliki jabatan_id\n\n";
}

// 4b. Pegawai dengan jabatan_id yang tidak ada di tabel jabatans
$pegawaisWithInvalidJabatan = DB::table('pegawais as p')
    ->leftJoin('jabatans as j', function($join) use ($userId) {
        $join->on('p.jabatan_id', '=', 'j.id')
             ->where('j.user_id', '=', $userId);
    })
    ->where('p.user_id', $userId)
    ->whereNotNull('p.jabatan_id')
    ->whereNull('j.id')
    ->get(['p.id', 'p.nama', 'p.jabatan', 'p.jabatan_id']);

if ($pegawaisWithInvalidJabatan->isNotEmpty()) {
    echo "   ❌ ERROR: " . $pegawaisWithInvalidJabatan->count() . " pegawai memiliki jabatan_id yang tidak valid:\n";
    
    foreach ($pegawaisWithInvalidJabatan as $p) {
        echo "      - {$p->nama} (jabatan: {$p->jabatan}, jabatan_id: {$p->jabatan_id})\n";
    }
    echo "\n";
} else {
    echo "   ✅ Semua jabatan_id valid\n\n";
}

// 4c. Nama jabatan tidak match dengan jabatans.nama
$pegawaisWithMismatchedName = DB::table('pegawais as p')
    ->join('jabatans as j', function($join) use ($userId) {
        $join->on('p.jabatan_id', '=', 'j.id')
             ->where('j.user_id', '=', $userId);
    })
    ->where('p.user_id', $userId)
    ->whereRaw('LOWER(p.jabatan) != LOWER(j.nama)')
    ->get(['p.id', 'p.nama as pegawai_nama', 'p.jabatan as pegawai_jabatan', 'j.nama as jabatan_nama', 'p.jabatan_id']);

if ($pegawaisWithMismatchedName->isNotEmpty()) {
    echo "   ⚠️  WARNING: " . $pegawaisWithMismatchedName->count() . " pegawai memiliki nama jabatan yang tidak match:\n";
    
    foreach ($pegawaisWithMismatchedName as $p) {
        echo "      - {$p->pegawai_nama}:\n";
        echo "        Pegawai.jabatan: '{$p->pegawai_jabatan}'\n";
        echo "        Jabatan.nama: '{$p->jabatan_nama}'\n";
        echo "        Jabatan_ID: {$p->jabatan_id}\n\n";
    }
} else {
    echo "   ✅ Semua nama jabatan match\n\n";
}

// 5. Summary
echo "=== SUMMARY ===\n\n";
echo "Total Jabatans (user_id=$userId): " . DB::table('jabatans')->where('user_id', $userId)->count() . "\n";
echo "Total Jabatans BTKL (user_id=$userId): " . $jabatans->count() . "\n";
echo "Total Pegawais (user_id=$userId): " . $pegawais->count() . "\n";
echo "Total Pegawais BTKL (user_id=$userId): " . $pegawaisBtkl->count() . "\n\n";

// 6. Recommendation
echo "=== RECOMMENDATION ===\n\n";

if ($pegawaiNoJabatanId > 0 || $pegawaisWithInvalidJabatan->isNotEmpty() || $pegawaisWithMismatchedName->isNotEmpty()) {
    echo "⚠️  ADA MASALAH YANG PERLU DIPERBAIKI!\n\n";
    
    if ($pegawaiNoJabatanId > 0) {
        echo "1. Perbaiki pegawai yang tidak memiliki jabatan_id:\n";
        echo "   - Update pegawais.jabatan_id berdasarkan pegawais.jabatan\n\n";
    }
    
    if ($pegawaisWithInvalidJabatan->isNotEmpty()) {
        echo "2. Perbaiki pegawai dengan jabatan_id yang tidak valid:\n";
        echo "   - Cari jabatan yang sesuai di tabel jabatans\n";
        echo "   - Update pegawais.jabatan_id dengan ID yang benar\n\n";
    }
    
    if ($pegawaisWithMismatchedName->isNotEmpty()) {
        echo "3. Sinkronkan nama jabatan:\n";
        echo "   - Update pegawais.jabatan dengan jabatans.nama\n\n";
    }
    
    echo "Jalankan script fix: php fix_jabatan_pegawai_inconsistency.php\n";
} else {
    echo "✅ TIDAK ADA MASALAH!\n";
    echo "   Semua data jabatan dan pegawai sudah konsisten.\n";
}
