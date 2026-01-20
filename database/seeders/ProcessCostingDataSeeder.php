<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\Bom;
use App\Models\BomDetail;
use App\Models\Satuan;
use App\Models\KategoriProduk;
use Illuminate\Support\Facades\DB;

class ProcessCostingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini membuat data lengkap untuk perhitungan HPP Process Costing
     * dengan contoh produk "Nasi Ayam Ketumbar"
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            // 1. Pastikan satuan ada
            $this->createSatuans();
            
            // 2. Pastikan kategori produk ada
            $kategori = $this->createKategoriProduk();
            
            // 3. Buat bahan baku
            $bahanBakus = $this->createBahanBakus();
            
            // 4. Buat produk
            $produk = $this->createProduk($kategori);
            
            // 5. Buat BOM dengan detail lengkap
            $bom = $this->createBomWithDetails($produk, $bahanBakus);
            
            // 6. Hitung HPP dan update produk
            $this->calculateHPP($bom, $produk);
            
            DB::commit();
            
            $this->command->info('✓ Process Costing Data berhasil di-seed!');
            $this->command->info('✓ Produk: ' . $produk->nama_produk);
            $this->command->info('✓ HPP: Rp ' . number_format($produk->harga_bom, 2));
            $this->command->info('✓ Harga Jual: Rp ' . number_format($produk->harga_jual, 2));
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create satuans if not exists
     */
    private function createSatuans()
    {
        $satuans = [
            ['nama' => 'Kilogram', 'kode' => 'KG'],
            ['nama' => 'Gram', 'kode' => 'G'],
            ['nama' => 'Liter', 'kode' => 'L'],
            ['nama' => 'Mililiter', 'kode' => 'ML'],
            ['nama' => 'Porsi', 'kode' => 'PORSI'],
            ['nama' => 'Pieces', 'kode' => 'PCS'],
        ];
        
        foreach ($satuans as $satuan) {
            Satuan::firstOrCreate(
                ['kode' => $satuan['kode']],
                $satuan
            );
        }
    }
    
    /**
     * Create kategori produk (skip jika tidak ada tabel)
     */
    private function createKategoriProduk()
    {
        // Skip kategori jika tabel tidak ada
        try {
            if (class_exists(KategoriProduk::class)) {
                return KategoriProduk::firstOrCreate(
                    ['nama' => 'Makanan Siap Saji'],
                    [
                        'kode_kategori' => 'MSS',
                        'deskripsi' => 'Kategori untuk makanan siap saji'
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->command->warn('Tabel kategori_produks tidak ada, skip kategori');
        }
        return null;
    }
    
    /**
     * Create bahan bakus
     */
    private function createBahanBakus()
    {
        $satuanKg = Satuan::where('kode', 'KG')->first();
        $satuanL = Satuan::where('kode', 'L')->first();
        
        $bahanBakuData = [
            [
                'kode_bahan' => 'BB-001',
                'nama_bahan' => 'Beras Premium',
                'satuan' => 'KG',
                'satuan_id' => $satuanKg->id,
                'harga_satuan' => 12000,
                'stok' => 100,
                'stok_minimum' => 20,
                'keterangan' => 'Beras premium untuk nasi'
            ],
            [
                'kode_bahan' => 'BB-002',
                'nama_bahan' => 'Daging Ayam Fillet',
                'satuan' => 'KG',
                'satuan_id' => $satuanKg->id,
                'harga_satuan' => 35000,
                'stok' => 50,
                'stok_minimum' => 10,
                'keterangan' => 'Daging ayam fillet tanpa tulang'
            ],
            [
                'kode_bahan' => 'BB-003',
                'nama_bahan' => 'Ketumbar Bubuk',
                'satuan' => 'KG',
                'satuan_id' => $satuanKg->id,
                'harga_satuan' => 50000,
                'stok' => 10,
                'stok_minimum' => 2,
                'keterangan' => 'Ketumbar bubuk untuk bumbu'
            ],
            [
                'kode_bahan' => 'BB-004',
                'nama_bahan' => 'Minyak Goreng',
                'satuan' => 'L',
                'satuan_id' => $satuanL->id,
                'harga_satuan' => 25000,
                'stok' => 30,
                'stok_minimum' => 5,
                'keterangan' => 'Minyak goreng untuk memasak'
            ],
            [
                'kode_bahan' => 'BB-005',
                'nama_bahan' => 'Bawang Putih',
                'satuan' => 'KG',
                'satuan_id' => $satuanKg->id,
                'harga_satuan' => 40000,
                'stok' => 15,
                'stok_minimum' => 3,
                'keterangan' => 'Bawang putih untuk bumbu'
            ],
            [
                'kode_bahan' => 'BB-006',
                'nama_bahan' => 'Garam',
                'satuan' => 'KG',
                'satuan_id' => $satuanKg->id,
                'harga_satuan' => 8000,
                'stok' => 20,
                'stok_minimum' => 5,
                'keterangan' => 'Garam untuk penyedap'
            ],
        ];
        
        $bahanBakus = [];
        foreach ($bahanBakuData as $data) {
            $bahanBakus[] = BahanBaku::firstOrCreate(
                ['kode_bahan' => $data['kode_bahan']],
                $data
            );
        }
        
        return $bahanBakus;
    }
    
    /**
     * Create produk
     */
    private function createProduk($kategori)
    {
        $satuanPorsi = Satuan::where('kode', 'PORSI')->first();
        
        return Produk::firstOrCreate(
            ['kode_produk' => 'PRD-001'],
            [
                'nama_produk' => 'Nasi Ayam Ketumbar',
                'deskripsi' => 'Nasi ayam dengan bumbu ketumbar khas',
                'kategori_id' => $kategori ? $kategori->id : null,
                'satuan_id' => $satuanPorsi->id,
                'harga_jual' => 0, // Akan dihitung
                'harga_bom' => 0, // Akan dihitung
                'stok' => 0,
                'stok_minimum' => 10,
                'margin_percent' => 40, // Margin 40%
                'btkl_default' => 0,
                'bop_default' => 0,
            ]
        );
    }
    
    /**
     * Create BOM with details
     */
    private function createBomWithDetails($produk, $bahanBakus)
    {
        // Hapus BOM lama jika ada
        Bom::where('produk_id', $produk->id)->delete();
        
        // Buat BOM baru
        $bom = Bom::create([
            'produk_id' => $produk->id,
            'bahan_baku_id' => $bahanBakus[0]->id, // Beras sebagai bahan utama
            'jumlah' => 1, // 1 porsi
            'satuan_resep' => 'PORSI',
            'total_biaya' => 0, // Akan dihitung
            'btkl_per_unit' => 0,
            'bop_per_unit' => 0,
            'total_btkl' => 0,
            'total_bop' => 0,
            'periode' => now()->format('Y-m'),
        ]);
        
        // Detail BOM - Resep untuk 1 porsi
        $bomDetails = [
            [
                'bahan_baku_id' => $bahanBakus[0]->id, // Beras
                'jumlah' => 0.2, // 200 gram
                'satuan' => 'KG',
                'harga_per_satuan' => 12000,
            ],
            [
                'bahan_baku_id' => $bahanBakus[1]->id, // Ayam
                'jumlah' => 0.15, // 150 gram
                'satuan' => 'KG',
                'harga_per_satuan' => 35000,
            ],
            [
                'bahan_baku_id' => $bahanBakus[2]->id, // Ketumbar
                'jumlah' => 0.01, // 10 gram
                'satuan' => 'KG',
                'harga_per_satuan' => 50000,
            ],
            [
                'bahan_baku_id' => $bahanBakus[3]->id, // Minyak
                'jumlah' => 0.02, // 20 ml
                'satuan' => 'L',
                'harga_per_satuan' => 25000,
            ],
            [
                'bahan_baku_id' => $bahanBakus[4]->id, // Bawang Putih
                'jumlah' => 0.015, // 15 gram
                'satuan' => 'KG',
                'harga_per_satuan' => 40000,
            ],
            [
                'bahan_baku_id' => $bahanBakus[5]->id, // Garam
                'jumlah' => 0.005, // 5 gram
                'satuan' => 'KG',
                'harga_per_satuan' => 8000,
            ],
        ];
        
        foreach ($bomDetails as $detail) {
            $totalHarga = $detail['jumlah'] * $detail['harga_per_satuan'];
            
            BomDetail::create([
                'bom_id' => $bom->id,
                'bahan_baku_id' => $detail['bahan_baku_id'],
                'jumlah' => $detail['jumlah'],
                'satuan' => $detail['satuan'],
                'harga_per_satuan' => $detail['harga_per_satuan'],
                'total_harga' => $totalHarga,
            ]);
        }
        
        return $bom->fresh(['details']);
    }
    
    /**
     * Calculate HPP using Process Costing Method
     */
    private function calculateHPP($bom, $produk)
    {
        // 1. Hitung Total Biaya Bahan Baku Langsung (BBL)
        $totalBBL = $bom->details->sum('subtotal');
        
        // 2. Hitung Biaya Tenaga Kerja Langsung (BTKL)
        // Asumsi: 20 jam kerja @ Rp 15,000/jam untuk 100 porsi
        // BTKL per porsi = (20 × 15,000) / 100 = Rp 3,000
        $jamKerja = 20;
        $tarifPerJam = 15000;
        $unitProduksi = 100;
        $btkl_per_unit = ($jamKerja * $tarifPerJam) / $unitProduksi;
        
        // 3. Hitung Biaya Overhead Pabrik (BOP)
        // Komponen BOP:
        // - Listrik: Rp 50,000
        // - Gas: Rp 30,000
        // - Penyusutan: Rp 20,000
        // Total BOP: Rp 100,000 untuk 100 porsi
        // BOP per porsi = 100,000 / 100 = Rp 1,000
        $totalBOP = 50000 + 30000 + 20000; // Rp 100,000
        $bop_per_unit = $totalBOP / $unitProduksi;
        
        // 4. Hitung Total Biaya Produksi per Unit (HPP)
        $hpp_per_unit = $totalBBL + $btkl_per_unit + $bop_per_unit;
        
        // 5. Update BOM
        $bom->update([
            'total_biaya' => $totalBBL,
            'btkl_per_unit' => $btkl_per_unit,
            'total_btkl' => $btkl_per_unit,
            'bop_per_unit' => $bop_per_unit,
            'total_bop' => $bop_per_unit,
        ]);
        
        // 6. Update Produk
        $margin = $produk->margin_percent ?? 40;
        $hargaJual = $hpp_per_unit * (1 + ($margin / 100));
        
        $produk->update([
            'harga_bom' => $hpp_per_unit,
            'harga_jual' => $hargaJual,
            'btkl_default' => $btkl_per_unit,
            'bop_default' => $bop_per_unit,
        ]);
        
        // 7. Output detail perhitungan
        $this->command->info("\n=== PERHITUNGAN HPP PROCESS COSTING ===");
        $this->command->info("Produk: {$produk->nama_produk}");
        $this->command->info("\nA. BIAYA BAHAN BAKU LANGSUNG (BBL):");
        foreach ($bom->details as $detail) {
            $this->command->info(sprintf(
                "   - %s: %.4f %s × Rp %s = Rp %s",
                $detail->bahanBaku->nama_bahan,
                $detail->jumlah,
                $detail->satuan,
                number_format($detail->harga_per_satuan, 0),
                number_format($detail->total_harga, 2)
            ));
        }
        $this->command->info(sprintf("   TOTAL BBL: Rp %s", number_format($totalBBL, 2)));
        
        $this->command->info("\nB. BIAYA TENAGA KERJA LANGSUNG (BTKL):");
        $this->command->info(sprintf("   - Jam Kerja: %d jam", $jamKerja));
        $this->command->info(sprintf("   - Tarif: Rp %s/jam", number_format($tarifPerJam, 0)));
        $this->command->info(sprintf("   - Unit Produksi: %d porsi", $unitProduksi));
        $this->command->info(sprintf("   - BTKL per unit: Rp %s", number_format($btkl_per_unit, 2)));
        
        $this->command->info("\nC. BIAYA OVERHEAD PABRIK (BOP):");
        $this->command->info("   - Listrik: Rp 50,000");
        $this->command->info("   - Gas: Rp 30,000");
        $this->command->info("   - Penyusutan: Rp 20,000");
        $this->command->info(sprintf("   - Total BOP: Rp %s", number_format($totalBOP, 0)));
        $this->command->info(sprintf("   - BOP per unit: Rp %s", number_format($bop_per_unit, 2)));
        
        $this->command->info("\nD. HARGA POKOK PRODUKSI (HPP):");
        $this->command->info(sprintf("   - BBL: Rp %s", number_format($totalBBL, 2)));
        $this->command->info(sprintf("   - BTKL: Rp %s", number_format($btkl_per_unit, 2)));
        $this->command->info(sprintf("   - BOP: Rp %s", number_format($bop_per_unit, 2)));
        $this->command->info(sprintf("   - HPP per unit: Rp %s", number_format($hpp_per_unit, 2)));
        
        $this->command->info("\nE. HARGA JUAL:");
        $this->command->info(sprintf("   - Margin: %d%%", $margin));
        $this->command->info(sprintf("   - Harga Jual: Rp %s", number_format($hargaJual, 2)));
        $this->command->info("\n" . str_repeat("=", 50));
    }
}
