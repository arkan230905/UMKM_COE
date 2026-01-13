<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\ProsesProduksi;

class TestBomCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-bom-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test BOM create functionality for produk ID 2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing BOM Create for Produk ID 2...');
        
        // Test produk data
        $produk = Produk::find(2);
        
        if (!$produk) {
            $this->error('Produk ID 2 not found!');
            return 0;
        }
        
        $this->info('');
        $this->info('Produk Data:');
        $this->line('  ID: ' . $produk->id);
        $this->line('  Nama: ' . $produk->nama_produk);
        $this->line('  Biaya Bahan: Rp ' . number_format($produk->biaya_bahan ?? 0, 0, ',', '.'));
        $this->line('  Harga BOM: Rp ' . number_format($produk->harga_bom ?? 0, 0, ',', '.'));
        
        // Test proses produksi data
        $this->info('');
        $this->info('Proses Produksi Available:');
        
        $prosesProduksis = ProsesProduksi::all();
        
        foreach ($prosesProduksis as $index => $proses) {
            $this->line('  ' . ($index + 1) . '. ' . $proses->nama_proses);
            $this->line('     Tarif BTKL: Rp ' . number_format($proses->tarif_btkl, 0, ',', '.') . '/' . ($proses->satuan_btkl ?? 'jam'));
            $this->line('     Produksi/Jam: ' . $proses->jumlah_produksi_per_jam . ' pcs');
            $this->line('     Biaya/PCS: Rp ' . number_format($proses->biaya_per_pcs, 0, ',', '.'));
            $this->line('');
        }
        
        // Calculate expected HPP with sample data
        $this->info('Sample HPP Calculation:');
        $biayaBahanProduk = $produk->biaya_bahan ?? 0;
        
        // Sample: Use all 3 proses with 1 hour duration each
        $totalBTKL = 0;
        foreach ($prosesProduksis as $proses) {
            $totalBTKL += $proses->biaya_per_pcs; // 1 hour each
        }
        
        // Sample BOP: Listrik 1000, Air 500
        $totalBOP = 1500;
        
        $totalHPP = $biayaBahanProduk + $totalBTKL + $totalBOP;
        
        $this->line('  Biaya Bahan Produk: Rp ' . number_format($biayaBahanProduk, 0, ',', '.'));
        $this->line('  Total BTKL (3 proses): Rp ' . number_format($totalBTKL, 0, ',', '.'));
        $this->line('  Total BOP (sample): Rp ' . number_format($totalBOP, 0, ',', '.'));
        $this->line('  -----------------------------');
        $this->line('  TOTAL HPP: Rp ' . number_format($totalHPP, 0, ',', '.'));
        
        $this->info('');
        $this->info('URL untuk testing:');
        $this->line('  http://127.0.0.1:8000/master-data/bom/create?produk_id=2');
        
        return 0;
    }
}
