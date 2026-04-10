<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Update BTKL to Correct Data ===\n\n";

try {
    // Data BTKL yang benar dari halaman master-data
    $correctBtklData = [
        [
            'nama_proses' => 'Perbumbuan',
            'tarif_btkl' => 22000,
            'kapasitas_per_jam' => 200,
            'biaya_per_produk' => 110, // 22000 / 200
        ],
        [
            'nama_proses' => 'Penggorengan', 
            'tarif_btkl' => 22200,
            'kapasitas_per_jam' => 50,
            'biaya_per_produk' => 444, // 22200 / 50
        ],
        [
            'nama_proses' => 'Pengemasan',
            'tarif_btkl' => 21800,
            'kapasitas_per_jam' => 50,
            'biaya_per_produk' => 436, // 21800 / 50
        ]
    ];

    foreach ($correctBtklData as $data) {
        echo "Updating: {$data['nama_proses']}\n";
        echo "  - New Tarif: Rp {$data['tarif_btkl']}\n";
        echo "  - New Kapasitas: {$data['kapasitas_per_jam']} pcs/jam\n";
        echo "  - New Biaya/Produk: Rp {$data['biaya_per_produk']}\n";

        // Update ProsesProduksi
        $updated = DB::table('proses_produksis')
            ->where('nama_proses', $data['nama_proses'])
            ->update([
                'tarif_btkl' => $data['tarif_btkl'],
                'kapasitas_per_jam' => $data['kapasitas_per_jam'],
                'updated_at' => now(),
            ]);

        echo "  - ProsesProduksi updated: " . ($updated ? "YES" : "NO") . "\n";

        // Get the updated ProsesProduksi record
        $proses = DB::table('proses_produksis')
            ->where('nama_proses', $data['nama_proses'])
            ->first();

        if ($proses) {
            // Update related BOP Proses
            $bopUpdated = DB::table('bop_proses')
                ->where('proses_produksi_id', $proses->id)
                ->update([
                    'kapasitas_per_jam' => $data['kapasitas_per_jam'],
                    'updated_at' => now(),
                ]);

            echo "  - BOP Proses updated: " . ($bopUpdated ? "YES" : "NO") . "\n";

            // Recalculate BOP per unit
            $bopProses = DB::table('bop_proses')
                ->where('proses_produksi_id', $proses->id)
                ->first();

            if ($bopProses) {
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

                echo "  - BOP per unit recalculated: Rp {$totalBop}\n";
                
                // Calculate new totals
                $totalBiayaPerProduk = $totalBop + $data['biaya_per_produk'];
                echo "  - Total biaya per produk: Rp {$totalBiayaPerProduk}\n";
            }
        }
        echo "\n";
    }

    echo "=== Update completed ===\n\n";

    // Verification
    echo "=== Verification ===\n";
    foreach ($correctBtklData as $data) {
        $proses = DB::table('proses_produksis')
            ->where('nama_proses', $data['nama_proses'])
            ->first();
            
        if ($proses) {
            echo "Proses: {$data['nama_proses']}\n";
            echo "  Expected Tarif: Rp {$data['tarif_btkl']} -> Actual: Rp {$proses->tarif_btkl}\n";
            echo "  Expected Kapasitas: {$data['kapasitas_per_jam']} -> Actual: {$proses->kapasitas_per_jam}\n";
            echo "  Tarif Match: " . ($proses->tarif_btkl == $data['tarif_btkl'] ? "YES" : "NO") . "\n";
            echo "  Kapasitas Match: " . ($proses->kapasitas_per_jam == $data['kapasitas_per_jam'] ? "YES" : "NO") . "\n\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
