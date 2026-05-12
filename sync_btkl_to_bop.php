<?php

require_once 'vendor/autoload.php';

use App\Models\Btkl;
use App\Models\ProsesProduksi;
use App\Models\BopProses;
use Illuminate\Support\Facades\DB;

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Sync BTKL to BOP ===\n\n";

try {
    // 1. Get all BTKL records
    $btklRecords = Btkl::with('jabatan')->get();
    echo "Found {$btklRecords->count()} BTKL records\n\n";

    foreach ($btklRecords as $btkl) {
        echo "Processing BTKL: {$btkl->nama_btkl} (ID: {$btkl->id})\n";
        echo "  - Tarif: Rp {$btkl->tarif_per_jam}\n";
        echo "  - Kapasitas: {$btkl->kapasitas_per_jam} pcs/jam\n";
        echo "  - Jabatan ID: {$btkl->jabatan_id}\n";

        // 2. Find related ProsesProduksi records
        $prosesRecords = ProsesProduksi::where('jabatan_id', $btkl->jabatan_id)
            ->orWhere('nama_proses', $btkl->nama_btkl)
            ->get();

        echo "  - Found {$prosesRecords->count()} related ProsesProduksi records\n";

        foreach ($prosesRecords as $proses) {
            echo "    - Proses: {$proses->nama_proses} (ID: {$proses->id})\n";
            echo "      Current tarif_btkl: Rp {$proses->tarif_btkl}\n";
            echo "      Current kapasitas: {$proses->kapasitas_per_jam}\n";

            // 3. Update ProsesProduksi with BTKL data
            $proses->update([
                'tarif_btkl' => $btkl->tarif_per_jam,
                'kapasitas_per_jam' => $btkl->kapasitas_per_jam,
                'satuan_btkl' => $btkl->satuan,
                'deskripsi' => $btkl->deskripsi_proses,
            ]);

            echo "      Updated tarif_btkl to: Rp {$btkl->tarif_per_jam}\n";
            echo "      Updated kapasitas to: {$btkl->kapasitas_per_jam}\n";

            // 4. Find and update related BOP Proses
            $bopProses = BopProses::where('proses_produksi_id', $proses->id)->first();
            if ($bopProses) {
                echo "    - Found BOP Proses (ID: {$bopProses->id})\n";
                echo "      Current kapasitas: {$bopProses->kapasitas_per_jam}\n";

                $bopProses->update([
                    'kapasitas_per_jam' => $btkl->kapasitas_per_jam,
                ]);

                echo "      Updated BOP kapasitas to: {$btkl->kapasitas_per_jam}\n";

                // 5. Recalculate BOP per unit
                $totalBop = 0;
                if ($bopProses->komponen_bop) {
                    $komponenBop = is_array($bopProses->komponen_bop) ? $bopProses->komponen_bop : json_decode($bopProses->komponen_bop, true);
                    if (is_array($komponenBop)) {
                        $totalBop = array_sum(array_column($komponenBop, 'rate_per_hour'));
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
                ]);

                echo "      Recalculated BOP per unit: Rp {$totalBop}\n";
            } else {
                echo "    - No BOP Proses found for this process\n";
            }
        }
        echo "\n";
    }

    echo "=== Sync completed successfully ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
