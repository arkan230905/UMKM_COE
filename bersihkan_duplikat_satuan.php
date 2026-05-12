<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== BERSIHKAN DUPLIKAT SATUAN ===" . PHP_EOL;

// Duplikat yang ditemukan
$duplikats = [
    'galon' => [
        'keep' => 41, // GALON - lebih lengkap
        'delete' => [14] // GL
    ],
    'gram' => [
        'keep' => 37, // GRAM - lebih lengkap  
        'delete' => [5] // G
    ],
    'liter' => [
        'keep' => 39, // LITER - lebih lengkap
        'delete' => [6] // LTR
    ],
    'mililiter' => [
        'keep' => 40, // MILILITER - lebih lengkap
        'delete' => [4] // ML
    ]
];

echo "RENCANA PEMBERSIHAN:" . PHP_EOL;
echo "===================" . PHP_EOL;

foreach ($duplikats as $nama => $config) {
    $keep = \App\Models\Satuan::find($config['keep']);
    echo PHP_EOL . "Nama: " . ucwords($nama) . PHP_EOL;
    echo "  Dipertahankan: ID {$keep->id} ({$keep->kode}) - {$keep->nama}" . PHP_EOL;
    
    foreach ($config['delete'] as $deleteId) {
        $delete = \App\Models\Satuan::find($deleteId);
        echo "  Dihapus: ID {$delete->id} ({$delete->kode}) - {$delete->nama}" . PHP_EOL;
    }
}

echo PHP_EOL . "MULAI PEMBERSIHAN..." . PHP_EOL;

try {
    \DB::beginTransaction();
    
    foreach ($duplikats as $nama => $config) {
        $keepId = $config['keep'];
        $deleteIds = $config['delete'];
        
        $keep = \App\Models\Satuan::find($keepId);
        echo PHP_EOL . "Memproses " . ucwords($nama) . ":" . PHP_EOL;
        
        // Cek apakah satuan yang akan dihapus digunakan
        $usedInBahanBaku = \App\Models\BahanBaku::whereIn('satuan_id', $deleteIds)->count();
        $usedInBahanPendukung = \App\Models\BahanPendukung::whereIn('satuan_id', $deleteIds)->count();
        
        if ($usedInBahanBaku > 0 || $usedInBahanPendukung > 0) {
            echo "  WARNING: Satuan ini digunakan! Memindahkan referensi..." . PHP_EOL;
            
            // Pindahkan referensi ke satuan yang dipertahankan
            \App\Models\BahanBaku::whereIn('satuan_id', $deleteIds)
                ->update(['satuan_id' => $keepId]);
            
            \App\Models\BahanPendukung::whereIn('satuan_id', $deleteIds)
                ->update(['satuan_id' => $keepId]);
            
            echo "  Referensi berhasil dipindahkan ke ID {$keepId}" . PHP_EOL;
        }
        
        // Hapus satuan duplikat
        foreach ($deleteIds as $deleteId) {
            $delete = \App\Models\Satuan::find($deleteId);
            echo "  Menghapus ID {$deleteId} ({$delete->kode})" . PHP_EOL;
            $delete->delete();
        }
        
        echo "  ✅ Selesai!" . PHP_EOL;
    }
    
    \DB::commit();
    
    echo PHP_EOL . "✅ PEMBERSIHAN SELESAI!" . PHP_EOL;
    
    // Verifikasi
    echo PHP_EOL . "VERIFIKASI HASIL:" . PHP_EOL;
    echo "==================" . PHP_EOL;
    
    $totalSatuan = \App\Models\Satuan::count();
    echo "Total satuan sekarang: {$totalSatuan}" . PHP_EOL;
    
    // Cek lagi duplikat
    $satuans = \App\Models\Satuan::orderBy('nama')->get();
    $groups = [];
    foreach ($satuans as $satuan) {
        $namaLower = strtolower($satuan->nama);
        if (!isset($groups[$namaLower])) {
            $groups[$namaLower] = [];
        }
        $groups[$namaLower][] = $satuan;
    }
    
    $duplikats = [];
    foreach ($groups as $nama => $items) {
        if (count($items) > 1) {
            $duplikats[$nama] = $items;
        }
    }
    
    echo "Sisa duplikat: " . count($duplikats) . PHP_EOL;
    
    if (count($duplikats) > 0) {
        echo PHP_EOL . "SISA DUPLIKAT:" . PHP_EOL;
        foreach ($duplikats as $nama => $items) {
            echo ucwords($nama) . ": ";
            foreach ($items as $item) {
                echo "ID {$item->id} ({$item->kode}) ";
            }
            echo PHP_EOL;
        }
    } else {
        echo "✅ TIDAK ADA DUPLIKAT LAGI!" . PHP_EOL;
    }
    
} catch (\Exception $e) {
    \DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}

echo PHP_EOL . "✅ Selesai!" . PHP_EOL;
