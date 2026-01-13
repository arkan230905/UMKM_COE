<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;

class CheckProdukTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-produk-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check produk table structure and data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Produk Table...');
        
        $produks = Produk::all();
        
        $this->info('');
        $this->info('Total produk: ' . $produks->count());
        
        if ($produks->count() > 0) {
            $this->info('');
            $this->info('Daftar Produk:');
            foreach ($produks as $produk) {
                $this->line('  ID: ' . $produk->id . ' - ' . $produk->nama_produk);
            }
        } else {
            $this->error('Tidak ada data produk!');
        }
        
        // Check produk ID 10 specifically
        $produk10 = Produk::find(10);
        if ($produk10) {
            $this->info('');
            $this->info('Produk ID 10 ditemukan: ' . $produk10->nama_produk);
        } else {
            $this->error('');
            $this->error('Produk ID 10 TIDAK ditemukan!');
        }
        
        return 0;
    }
}
