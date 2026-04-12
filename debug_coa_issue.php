<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG COA ISSUE ===\n\n";

// Cek pembelian yang akan ditest
$pembelian = \App\Models\Pembelian::with([
    'details.bahanBaku',
    'details.bahanPendukung'
])->first();

if (!$pembelian) {
    echo "❌ Tidak ada pembelian untuk testing!\n";
    exit;
}

echo "📦 PEMBELIAN: {$pembelian->nomor_pembelian}\n\n";

foreach ($pembelian->details as $index => $detail) {
    echo "DETAIL " . ($index + 1) . ":\n";
    
    if ($detail->bahan_baku_id) {
        echo "  Tipe: Bahan Baku\n";
        echo "  ID Bahan: {$detail->bahan_baku_id}\n";
        
        if ($detail->bahanBaku) {
            echo "  Nama Bahan: {$detail->bahanBaku->nama_bahan}\n";
            echo "  COA Persediaan ID: " . ($detail->bahanBaku->coa_persediaan_id ?? 'NULL') . "\n";
            
            if ($detail->bahanBaku->coa_persediaan_id) {
                // Cek apakah COA ada di database
                $coa = \App\Models\Coa::find($detail->bahanBaku->coa_persediaan_id);
                if ($coa) {
                    echo "  ✅ COA Ditemukan: {$coa->kode_akun} - {$coa->nama_akun}\n";
                } else {
                    echo "  ❌ COA TIDAK DITEMUKAN dengan ID: {$detail->bahanBaku->coa_persediaan_id}\n";
                    
                    // Cek apakah ada COA dengan kode yang mirip
                    $similarCoas = \App\Models\Coa::where('nama_akun', 'like', '%ayam potong%')->get();
                    echo "  COA dengan nama mirip:\n";
                    foreach ($similarCoas as $similarCoa) {
                        echo "    - ID: {$similarCoa->id}, Kode: {$similarCoa->kode_akun}, Nama: {$similarCoa->nama_akun}\n";
                    }
                }
            } else {
                echo "  ⚠️  COA Persediaan ID tidak diset\n";
            }
            
            // Cek relasi COA
            echo "  Relasi coaPersediaan: " . ($detail->bahanBaku->coaPersediaan ? 'LOADED' : 'NULL') . "\n";
            
        } else {
            echo "  ❌ Bahan Baku tidak ditemukan!\n";
        }
        
    } elseif ($detail->bahan_pendukung_id) {
        echo "  Tipe: Bahan Pendukung\n";
        echo "  ID Bahan: {$detail->bahan_pendukung_id}\n";
        
        if ($detail->bahanPendukung) {
            echo "  Nama Bahan: {$detail->bahanPendukung->nama_bahan}\n";
            echo "  COA Persediaan ID: " . ($detail->bahanPendukung->coa_persediaan_id ?? 'NULL') . "\n";
            
            if ($detail->bahanPendukung->coa_persediaan_id) {
                $coa = \App\Models\Coa::find($detail->bahanPendukung->coa_persediaan_id);
                if ($coa) {
                    echo "  ✅ COA Ditemukan: {$coa->kode_akun} - {$coa->nama_akun}\n";
                } else {
                    echo "  ❌ COA TIDAK DITEMUKAN dengan ID: {$detail->bahanPendukung->coa_persediaan_id}\n";
                }
            } else {
                echo "  ⚠️  COA Persediaan ID tidak diset\n";
            }
            
        } else {
            echo "  ❌ Bahan Pendukung tidak ditemukan!\n";
        }
    }
    
    echo "\n";
}

// Cek semua COA yang ada
echo "=== SEMUA COA PERSEDIAAN ===\n";
$coaPersediaan = \App\Models\Coa::where('kode_akun', 'like', '114%')
    ->orWhere('kode_akun', 'like', '115%')
    ->orderBy('kode_akun')
    ->get();

foreach ($coaPersediaan as $coa) {
    echo "ID: {$coa->id} | Kode: {$coa->kode_akun} | Nama: {$coa->nama_akun}\n";
}

?>