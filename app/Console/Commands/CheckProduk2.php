<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\ProsesProduksi;

class CheckProduk2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-produk2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check produk ID 2 data for BOM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Produk ID 2 data...');
        
        $produk = Produk::find(2);
        
        if ($produk) {
            $this->info('');
            $this->info('Produk Data:');
            $this->line('  ID: ' . $produk->id);
            $this->line('  Nama: ' . $produk->nama_produk);
            $this->line('  Biaya Bahan: ' . ($produk->biaya_bahan ?? 'NULL'));
            $this->line('  Harga BOM: ' . ($produk->harga_bom ?? 'NULL'));
            $this->line('  Harga Pokok: ' . ($produk->harga_pokok ?? 'NULL'));
        } else {
            $this->error('Produk ID 2 not found!');
            return 0;
        }
        
        // Check proses produksi data
        $this->info('');
        $this->info('Proses Produksi Data:');
        
        $prosesProduksis = ProsesProduksi::all();
        
        foreach ($prosesProduksis as $proses) {
            $this->line('  ID: ' . $proses->id . ' - ' . $proses->nama_proses);
            $this->line('    Kode: ' . $proses->kode_proses);
            $this->line('    Tarif BTKL: ' . ($proses->tarif_btkl ?? 'NULL'));
            $this->line('    Satuan BTKL: ' . ($proses->satuan_btkl ?? 'NULL'));
            $this->line('    Jumlah Produksi per Jam: ' . ($proses->jumlah_produksi_per_jam ?? 'NULL'));
            $this->line('    Biaya per PCS: ' . $proses->biaya_per_pcs);
            $this->line('');
        }
        
        return 0;
    }
}
