<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PERBAIKAN MAPPING COA ===\n\n";

// Perbaiki mapping COA untuk bahan baku
echo "📦 MEMPERBAIKI BAHAN BAKU:\n";
$bahanBakus = \App\Models\BahanBaku::all();

foreach ($bahanBakus as $bahan) {
    echo "- {$bahan->nama_bahan}:\n";
    
    // Cek apakah coa_persediaan_id berisi kode atau ID
    if ($bahan->coa_persediaan_id) {
        $currentValue = $bahan->coa_persediaan_id;
        
        // Coba cari berdasarkan ID dulu
        $coaById = \App\Models\Coa::find($currentValue);
        
        if ($coaById) {
            echo "  ✅ COA sudah benar (ID: {$currentValue} -> {$coaById->kode_akun} - {$coaById->nama_akun})\n";
        } else {
            // Coba cari berdasarkan kode
            $coaByCode = \App\Models\Coa::where('kode_akun', $currentValue)->first();
            
            if ($coaByCode) {
                echo "  🔧 Memperbaiki: Kode {$currentValue} -> ID {$coaByCode->id} ({$coaByCode->nama_akun})\n";
                $bahan->coa_persediaan_id = $coaByCode->id;
                $bahan->save();
            } else {
                echo "  ❌ COA tidak ditemukan untuk kode/ID: {$currentValue}\n";
            }
        }
    } else {
        echo "  ⚠️  Tidak ada COA persediaan\n";
    }
}

echo "\n📦 MEMPERBAIKI BAHAN PENDUKUNG:\n";
$bahanPendukungs = \App\Models\BahanPendukung::all();

foreach ($bahanPendukungs as $bahan) {
    echo "- {$bahan->nama_bahan}:\n";
    
    if ($bahan->coa_persediaan_id) {
        $currentValue = $bahan->coa_persediaan_id;
        
        // Coba cari berdasarkan ID dulu
        $coaById = \App\Models\Coa::find($currentValue);
        
        if ($coaById) {
            echo "  ✅ COA sudah benar (ID: {$currentValue} -> {$coaById->kode_akun} - {$coaById->nama_akun})\n";
        } else {
            // Coba cari berdasarkan kode
            $coaByCode = \App\Models\Coa::where('kode_akun', $currentValue)->first();
            
            if ($coaByCode) {
                echo "  🔧 Memperbaiki: Kode {$currentValue} -> ID {$coaByCode->id} ({$coaByCode->nama_akun})\n";
                $bahan->coa_persediaan_id = $coaByCode->id;
                $bahan->save();
            } else {
                echo "  ❌ COA tidak ditemukan untuk kode/ID: {$currentValue}\n";
            }
        }
    } else {
        echo "  ⚠️  Tidak ada COA persediaan\n";
    }
}

echo "\n=== VERIFIKASI HASIL ===\n";
echo "Menjalankan ulang pengecekan...\n\n";

// Verifikasi hasil
$bahanBakus = \App\Models\BahanBaku::with('coaPersediaan')->get();
foreach ($bahanBakus as $bahan) {
    echo "✅ {$bahan->nama_bahan}: ";
    if ($bahan->coaPersediaan) {
        echo "{$bahan->coaPersediaan->kode_akun} - {$bahan->coaPersediaan->nama_akun}\n";
    } else {
        echo "Tidak ada COA\n";
    }
}

echo "\n";
$bahanPendukungs = \App\Models\BahanPendukung::with('coaPersediaan')->get();
foreach ($bahanPendukungs as $bahan) {
    echo "✅ {$bahan->nama_bahan}: ";
    if ($bahan->coaPersediaan) {
        echo "{$bahan->coaPersediaan->kode_akun} - {$bahan->coaPersediaan->nama_akun}\n";
    } else {
        echo "Tidak ada COA\n";
    }
}

echo "\n🎯 PERBAIKAN SELESAI!\n";

?>