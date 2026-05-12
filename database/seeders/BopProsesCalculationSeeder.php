<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProsesProduksi;
use App\Models\BopProses;
use App\Models\ProsesBop;

class BopProsesCalculationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all proses produksi with their BOP components
        $prosesProduksis = ProsesProduksi::with('prosesBops.komponenBop')->get();

        foreach ($prosesProduksis as $proses) {
            // Calculate total BOP per produk from components
            $totalBopPerProduk = 0;
            $komponenBopData = [];

            foreach ($proses->prosesBops as $prosesBop) {
                $komponenBop = $prosesBop->komponenBop;
                if ($komponenBop) {
                    // Nominal sudah per produk, jadi langsung dijumlahkan
                    $totalBopPerProduk += $komponenBop->tarif_per_satuan;
                    
                    $komponenBopData[] = [
                        'component_name' => $komponenBop->nama_komponen,
                        'rate_per_unit' => $komponenBop->tarif_per_satuan,
                        'description' => $komponenBop->nama_komponen
                    ];
                }
            }

            // Calculate BTKL per produk
            $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $proses->tarif_btkl / $proses->kapasitas_per_jam : 0;
            
            // Total biaya per produk = BTKL per produk + BOP per produk
            $totalBiayaPerProduk = $btklPerProduk + $totalBopPerProduk;

            // Create or update BopProses record
            BopProses::updateOrCreate(
                ['proses_produksi_id' => $proses->id],
                [
                    'komponen_bop' => json_encode($komponenBopData),
                    'total_bop_per_jam' => $totalBopPerProduk, // Sebenarnya per produk, tapi field ini digunakan untuk menyimpan total BOP
                    'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                    'bop_per_unit' => $totalBopPerProduk, // BOP per unit (per produk)
                    'is_active' => true
                ]
            );

            $this->command->info("BOP Proses untuk {$proses->nama_proses}:");
            $this->command->info("- BTKL/produk: Rp " . number_format($btklPerProduk, 2, ',', '.'));
            $this->command->info("- Total BOP/produk: Rp " . number_format($totalBopPerProduk, 0, ',', '.'));
            $this->command->info("- Total Biaya/produk: Rp " . number_format($totalBiayaPerProduk, 2, ',', '.'));
            $this->command->info("- Komponen: " . count($komponenBopData) . " items");
            $this->command->info("");
        }

        $this->command->info('BOP Proses calculation completed successfully!');
        $this->command->info('Semua nominal sudah dihitung per produk, bukan per jam.');
    }
}