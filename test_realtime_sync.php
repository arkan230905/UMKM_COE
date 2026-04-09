<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ProsesProduksi;
use App\Models\BopProses;

echo "=== Test Realtime Sync dari ProsesProduksi ke BOP ===\n\n";

try {
    // 1. Ambil data awal
    echo "=== Data Awal ===\n";
    $prosesAwal = DB::table('proses_produksis')
        ->where('nama_proses', 'Perbumbuan')
        ->first();
    
    $bopAwal = DB::table('bop_proses')
        ->where('proses_produksi_id', $prosesAwal->id)
        ->first();
    
    echo "ProsesProduksi (Perbumbuan):\n";
    echo "  - Tarif BTKL: Rp {$prosesAwal->tarif_btkl}\n";
    echo "  - Kapasitas: {$prosesAwal->kapasitas_per_jam} pcs/jam\n";
    
    echo "BOP Proses (ID: {$bopAwal->id}):\n";
    echo "  - Kapasitas: {$bopAwal->kapasitas_per_jam} pcs/jam\n";
    echo "  - BOP/Unit: Rp {$bopAwal->bop_per_unit}\n";
    
    $btklPerProdukAwal = $prosesAwal->kapasitas_per_jam > 0 ? $prosesAwal->tarif_btkl / $prosesAwal->kapasitas_per_jam : 0;
    $totalBiayaAwal = $bopAwal->bop_per_unit + $btklPerProdukAwal;
    echo "  - Total Biaya/Produk: Rp {$totalBiayaAwal}\n\n";

    // 2. Simulasi perubahan data
    echo "=== Simulasi Perubahan Data ===\n";
    $tarifBaru = 25000;
    $kapasitasBaru = 250;
    
    echo "Mengubah data ProsesProduksi:\n";
    echo "  - Tarif BTKL: Rp {$prosesAwal->tarif_btkl} -> Rp {$tarifBaru}\n";
    echo "  - Kapasitas: {$prosesAwal->kapasitas_per_jam} -> {$kapasitasBaru} pcs/jam\n\n";

    // Update data ProsesProduksi
    DB::table('proses_produksis')
        ->where('id', $prosesAwal->id)
        ->update([
            'tarif_btkl' => $tarifBaru,
            'kapasitas_per_jam' => $kapasitasBaru,
            'updated_at' => now(),
        ]);

    // 3. Trigger observer secara manual
    echo "=== Trigger Observer ===\n";
    $prosesUpdated = ProsesProduksi::find($prosesAwal->id);
    
    // Simulasikan observer yang berjalan
    $bopProses = BopProses::where('proses_produksi_id', $prosesUpdated->id)->first();
    if ($bopProses) {
        $bopProses->update([
            'kapasitas_per_jam' => $kapasitasBaru,
            'updated_at' => now(),
        ]);
        
        // Recalculate BOP per unit
        $totalBop = 0;
        if ($bopProses->komponen_bop) {
            $komponenArray = is_array($bopProses->komponen_bop) ? $bopProses->komponen_bop : json_decode($bopProses->komponen_bop, true);
            if (is_array($komponenArray)) {
                $totalBop = array_sum(array_column($komponenArray, 'rate_per_hour'));
            }
        }
        
        if ($totalBop == 0) {
            $totalBop = 
                floatval($bopProses->listrik_per_jam ?? 0) +
                floatval($bopProses->gas_bbm_per_jam ?? 0) +
                floatval($bopProses->penyusutan_mesin_per_jam ?? 0) +
                floatval($bopProses->maintenance_per_jam ?? 0) +
                floatval($bopProses->gaji_mandor_per_jam ?? 0) +
                floatval($bopProses->lain_lain_per_jam ?? 0);
        }
        
        $bopProses->update([
            'total_bop_per_jam' => $totalBop,
            'bop_per_unit' => $totalBop,
            'updated_at' => now(),
        ]);
        
        echo "Observer berhasil mengupdate BOP Proses\n";
    }

    // 4. Verifikasi hasil
    echo "\n=== Verifikasi Hasil ===\n";
    $prosesSetelah = DB::table('proses_produksis')
        ->where('id', $prosesAwal->id)
        ->first();
    
    $bopSetelah = DB::table('bop_proses')
        ->where('proses_produksi_id', $prosesAwal->id)
        ->first();
    
    echo "ProsesProduksi (Perbumbuan) Setelah Update:\n";
    echo "  - Tarif BTKL: Rp {$prosesSetelah->tarif_btkl}\n";
    echo "  - Kapasitas: {$prosesSetelah->kapasitas_per_jam} pcs/jam\n";
    
    echo "BOP Proses Setelah Update:\n";
    echo "  - Kapasitas: {$bopSetelah->kapasitas_per_jam} pcs/jam\n";
    echo "  - BOP/Unit: Rp {$bopSetelah->bop_per_unit}\n";
    
    $btklPerProdukSetelah = $prosesSetelah->kapasitas_per_jam > 0 ? $prosesSetelah->tarif_btkl / $prosesSetelah->kapasitas_per_jam : 0;
    $totalBiayaSetelah = $bopSetelah->bop_per_unit + $btklPerProdukSetelah;
    echo "  - Total Biaya/Produk: Rp {$totalBiayaSetelah}\n\n";

    // 5. Cek apakah sync berhasil
    echo "=== Hasil Sync ===\n";
    echo "Tarif BTKL Sync: " . ($prosesSetelah->tarif_btkl == $tarifBaru ? "BERHASIL" : "GAGAL") . "\n";
    echo "Kapasitas Sync: " . ($bopSetelah->kapasitas_per_jam == $kapasitasBaru ? "BERHASIL" : "GAGAL") . "\n";
    echo "Perubahan Terdeteksi: " . (($prosesSetelah->tarif_btkl != $prosesAwal->tarif_btkl) ? "YA" : "TIDAK") . "\n";

    // 6. Kembalikan data ke semula
    echo "\n=== Kembalikan Data ke Semula ===\n";
    DB::table('proses_produksis')
        ->where('id', $prosesAwal->id)
        ->update([
            'tarif_btkl' => $prosesAwal->tarif_btkl,
            'kapasitas_per_jam' => $prosesAwal->kapasitas_per_jam,
            'updated_at' => now(),
        ]);

    DB::table('bop_proses')
        ->where('id', $bopAwal->id)
        ->update([
            'kapasitas_per_jam' => $bopAwal->kapasitas_per_jam,
            'total_bop_per_jam' => $bopAwal->total_bop_per_jam,
            'bop_per_unit' => $bopAwal->bop_per_unit,
            'updated_at' => now(),
        ]);

    echo "Data telah dikembalikan ke nilai semula\n";
    echo "\n=== Test Selesai ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
