<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class FixMissingPembelianDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Checking for pembelian without details...\n";
        
        $pembelianWithoutDetails = Pembelian::doesntHave('details')->get();
        
        echo "Found {$pembelianWithoutDetails->count()} pembelian without details\n";
        
        if ($pembelianWithoutDetails->count() === 0) {
            echo "All pembelian have details. Nothing to fix.\n";
            return;
        }
        
        foreach ($pembelianWithoutDetails as $pembelian) {
            echo "\nProcessing Pembelian ID: {$pembelian->id}\n";
            echo "  Total: Rp " . number_format($pembelian->total_harga, 0, ',', '.') . "\n";
            
            // Karena kita tidak tahu item apa yang dibeli, kita akan membuat detail dummy
            // dengan catatan bahwa ini adalah data yang hilang
            
            // Ambil bahan baku pertama sebagai placeholder
            $bahanBaku = BahanBaku::first();
            
            if (!$bahanBaku) {
                echo "  ERROR: No bahan baku found in database. Skipping.\n";
                continue;
            }
            
            // Buat detail dengan total harga pembelian
            PembelianDetail::create([
                'pembelian_id' => $pembelian->id,
                'bahan_baku_id' => $bahanBaku->id,
                'jumlah' => 1,
                'satuan' => 'unit',
                'harga_satuan' => $pembelian->total_harga,
                'subtotal' => $pembelian->total_harga,
                'faktor_konversi' => 1,
            ]);
            
            echo "  FIXED: Created placeholder detail for pembelian\n";
            echo "  NOTE: Detail ini adalah placeholder. Silakan edit manual jika perlu.\n";
        }
        
        echo "\n✓ Selesai memperbaiki pembelian tanpa detail\n";
    }
}
