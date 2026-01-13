<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\Satuan;
use App\Models\ProsesProduksi;

class TestProduksi extends Command
{
    protected $signature = 'test:produksi';
    protected $description = 'Test setup produksi dengan data sample';

    public function handle()
    {
        $this->info('Setting up test data for produksi...');
        
        // 1. Pastikan ada satuan
        $satuanPcs = Satuan::firstOrCreate(['kode' => 'PCS'], ['nama' => 'Pieces', 'kategori' => 'jumlah']);
        $satuanKg = Satuan::firstOrCreate(['kode' => 'KG'], ['nama' => 'Kilogram', 'kategori' => 'berat']);
        
        // 2. Buat bahan baku sample
        $tepung = BahanBaku::firstOrCreate([
            'nama_bahan' => 'Tepung Terigu'
        ], [
            'kode_bahan' => 'BB001',
            'satuan' => 'KG',
            'satuan_id' => $satuanKg->id,
            'harga_satuan' => 15000,
            'stok' => 100
        ]);
        
        $gula = BahanBaku::firstOrCreate([
            'nama_bahan' => 'Gula Pasir'
        ], [
            'kode_bahan' => 'BB002',
            'satuan' => 'KG', 
            'satuan_id' => $satuanKg->id,
            'harga_satuan' => 12000,
            'stok' => 50
        ]);
        
        // 3. Buat produk sample
        $roti = Produk::firstOrCreate([
            'nama_produk' => 'Roti Tawar'
        ], [
            'kode_produk' => 'PRD001',
            'satuan_id' => $satuanPcs->id,
            'harga_jual' => 25000,
            'stok' => 0
        ]);
        
        // 4. Buat proses produksi sample
        $proses = ProsesProduksi::firstOrCreate([
            'nama_proses' => 'Mixing & Baking'
        ], [
            'kode_proses' => 'PRS001',
            'tarif_btkl' => 50000,
            'satuan_btkl' => 'jam',
            'jumlah_produksi_per_jam' => 10
        ]);
        
        // 5. Buat BOM
        $bom = Bom::firstOrCreate([
            'produk_id' => $roti->id
        ], [
            'kode_bom' => 'BOM001',
            'biaya_bahan' => 0,
            'total_btkl' => 0,
            'total_bop' => 0,
            'total_biaya' => 0
        ]);
        
        // 6. Buat BOM details
        $bom->details()->firstOrCreate([
            'bahan_baku_id' => $tepung->id
        ], [
            'jumlah' => 0.5,
            'satuan' => 'KG',
            'satuan_resep' => 'KG',
            'harga_satuan' => $tepung->harga_satuan,
            'subtotal' => 0.5 * $tepung->harga_satuan
        ]);
        
        $bom->details()->firstOrCreate([
            'bahan_baku_id' => $gula->id
        ], [
            'jumlah' => 0.2,
            'satuan' => 'KG',
            'satuan_resep' => 'KG', 
            'harga_satuan' => $gula->harga_satuan,
            'subtotal' => 0.2 * $gula->harga_satuan
        ]);
        
        // 7. Buat BOM proses
        $bomProses = $bom->proses()->firstOrCreate([
            'proses_produksi_id' => $proses->id
        ], [
            'durasi' => 0.1, // 0.1 jam = 6 menit
            'satuan_durasi' => 'jam',
            'biaya_btkl' => 5000, // 0.1 jam * 50000
            'biaya_bop' => 1000
        ]);
        
        // 8. Update BOM totals
        $totalBahan = $bom->details->sum('subtotal');
        $totalBtkl = $bom->proses->sum('biaya_btkl');
        $totalBop = $bom->proses->sum('biaya_bop');
        
        $bom->update([
            'biaya_bahan' => $totalBahan,
            'total_btkl' => $totalBtkl,
            'total_bop' => $totalBop,
            'total_biaya' => $totalBahan + $totalBtkl + $totalBop
        ]);
        
        $this->info('Test data created successfully!');
        $this->info("Produk: {$roti->nama_produk} (ID: {$roti->id})");
        $this->info("BOM Total: Rp " . number_format($bom->total_biaya));
        $this->info("Bahan baku tersedia:");
        $this->info("- {$tepung->nama_bahan}: {$tepung->stok} {$satuanKg->nama}");
        $this->info("- {$gula->nama_bahan}: {$gula->stok} {$satuanKg->nama}");
        
        return 0;
    }
}