<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Manual Sync BTKL to BOP ===\n\n";

try {
    // Get all ProsesProduksi records
    $prosesRecords = DB::table('proses_produksis')->get();
    echo "Found {$prosesRecords->count()} ProsesProduksi records\n\n";

    foreach ($prosesRecords as $proses) {
        echo "Processing Proses: {$proses->nama_proses} (ID: {$proses->id})\n";
        echo "  - Tarif: Rp {$proses->tarif_btkl}\n";
        echo "  - Kapasitas: {$proses->kapasitas_per_jam} pcs/jam\n";
        echo "  - Jabatan ID: {$proses->jabatan_id}\n";

        // Find related BOP Proses
        $bopProses = DB::table('bop_proses')
            ->where('proses_produksi_id', $proses->id)
            ->first();

        if ($bopProses) {
            echo "  - Found BOP Proses (ID: {$bopProses->id})\n";
            echo "    Current kapasitas: {$bopProses->kapasitas_per_jam}\n";

            // Update BOP Proses with new data
            DB::table('bop_proses')
                ->where('id', $bopProses->id)
                ->update([
                    'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                    'updated_at' => now(),
                ]);

            echo "    Updated BOP kapasitas to: {$proses->kapasitas_per_jam}\n";

            // Recalculate BOP per unit
            $totalBop = 0;
            
            // Get komponen_bop data
            $komponenBop = $bopProses->komponen_bop;
            if ($komponenBop) {
                $komponenArray = is_array($komponenBop) ? $komponenBop : json_decode($komponenBop, true);
                if (is_array($komponenArray)) {
                    $totalBop = array_sum(array_column($komponenArray, 'rate_per_hour'));
                }
            }

            // If no komponen_bop, calculate from individual fields
            if ($totalBop == 0) {
                $totalBop = 
                    floatval($bopProses->listrik_per_jam ?? 0) +
                    floatval($bopProses->gas_bbm_per_jam ?? 0) +
                    floatval($bopProses->penyusutan_mesin_per_jam ?? 0) +
                    floatval($bopProses->maintenance_per_jam ?? 0) +
                    floatval($bopProses->gaji_mandor_per_jam ?? 0) +
                    floatval($bopProses->lain_lain_per_jam ?? 0);
            }

            // Update BOP per unit
            DB::table('bop_proses')
                ->where('id', $bopProses->id)
                ->update([
                    'total_bop_per_jam' => $totalBop,
                    'bop_per_unit' => $totalBop,
                    'updated_at' => now(),
                ]);

            echo "    Recalculated BOP per unit: Rp {$totalBop}\n";
            
            // Calculate BTKL per produk
            $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $proses->tarif_btkl / $proses->kapasitas_per_jam : 0;
            echo "    BTKL per produk: Rp {$btklPerProduk}\n";
            
            // Calculate total biaya per produk
            $totalBiayaPerProduk = $totalBop + $btklPerProduk;
            echo "    Total biaya per produk: Rp {$totalBiayaPerProduk}\n";
            
        } else {
            echo "  - No BOP Proses found for this process\n";
        }
        echo "\n";
    }

    echo "=== Manual sync completed successfully ===\n";
    echo "\n=== Verification ===\n";
    
    // Verify the sync
    foreach ($prosesRecords as $proses) {
        $bopProses = DB::table('bop_proses')
            ->where('proses_produksi_id', $proses->id)
            ->first();
            
        if ($bopProses) {
            echo "Proses: {$proses->nama_proses}\n";
            echo "  Proses Kapasitas: {$proses->kapasitas_per_jam} -> BOP Kapasitas: {$bopProses->kapasitas_per_jam}\n";
            echo "  Match: " . ($proses->kapasitas_per_jam == $bopProses->kapasitas_per_jam ? "YES" : "NO") . "\n\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
