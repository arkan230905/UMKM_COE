<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini aman untuk data yang sudah ada:
     * - Jika bahan BELUM ADA: insert dengan stok_awal
     * - Jika bahan SUDAH ADA: update harga & satuan saja, tidak mengubah stok
     */
    public function run(): void
    {
        // Data bahan baku sesuai Excel dengan COA yang berbeda per bahan
        $bahanBakuData = [
            [
                'kode_bahan' => 'AYM001',
                'nama_bahan' => 'Ayam Potong',
                'satuan_utama' => 'Kilogram',
                'harga_satuan' => 32000,
                'stok_awal' => 50,
                'stok_minimum' => 5,
                'deskripsi' => 'Ayam potong segar',
                'coa_pembelian' => '510',
                'coa_persediaan' => '1141',
                'coa_hpp' => '510',
                'konversi' => [
                    ['satuan' => 'Gram', 'nilai' => 1000],      // 1 Kg = 1000 Gram
                    ['satuan' => 'Potong', 'nilai' => 4],       // 1 Kg = 4 Potong
                    ['satuan' => 'Ons', 'nilai' => 10]          // 1 Kg = 10 Ons
                ]
            ],
            [
                'kode_bahan' => 'AYM002',
                'nama_bahan' => 'Ayam Kampung',
                'satuan_utama' => 'Ekor',
                'harga_satuan' => 45000,
                'stok_awal' => 40,
                'stok_minimum' => 5,
                'deskripsi' => 'Ayam kampung segar',
                'coa_pembelian' => '511',
                'coa_persediaan' => '1142',
                'coa_hpp' => '511',
                'konversi' => [
                    ['satuan' => 'Potong', 'nilai' => 6],       // 1 Ekor = 6 Potong
                    ['satuan' => 'Kilogram', 'nilai' => 1.5],   // 1 Ekor = 1.5 Kilogram
                    ['satuan' => 'Gram', 'nilai' => 1500]       // 1 Ekor = 1500 Gram
                ]
            ]
        ];

        echo "=== BAHAN BAKU SEEDER (SESUAI EXCEL) ===\n";
        echo "Memproses " . count($bahanBakuData) . " bahan baku...\n\n";

        $insertCount = 0;
        $updateCount = 0;
        $konversiCount = 0;

        try {
            // Mulai transaction untuk keamanan
            DB::beginTransaction();

            foreach ($bahanBakuData as $bahan) {
                echo "Memproses: {$bahan['nama_bahan']}\n";

                // 1. Ambil satuan_id berdasarkan nama satuan utama
                $satuanId = $this->getSatuanId($bahan['satuan_utama']);

                // 2. Process sub_satuan fields (calculate before checking existing bahan)
                $subSatuanData = [];
                if (isset($bahan['konversi']) && is_array($bahan['konversi'])) {
                    echo "  🔄 Memproses sub satuan...\n";
                    
                    for ($i = 0; $i < min(3, count($bahan['konversi'])); $i++) {
                        $konversi = $bahan['konversi'][$i];
                        $subSatuanId = $this->getSatuanId($konversi['satuan']);
                        
                        // Calculate price per sub unit: harga_utama / nilai_konversi
                        $hargaPerUnit = $bahan['harga_satuan'] / $konversi['nilai'];
                        
                        $subSatuanData['sub_satuan_' . ($i + 1) . '_id'] = $subSatuanId;
                        $subSatuanData['sub_satuan_' . ($i + 1) . '_konversi'] = $konversi['nilai'];
                        $subSatuanData['sub_satuan_' . ($i + 1) . '_nilai'] = $hargaPerUnit;
                        
                        echo "    - {$konversi['satuan']}: {$konversi['nilai']} = Rp " . number_format($hargaPerUnit, 0, ',', '.') . "\n";
                    }
                }

                // 3. Cek apakah bahan sudah ada berdasarkan nama_bahan atau kode_bahan
                $existingBahan = DB::table('bahan_bakus')
                    ->where('nama_bahan', $bahan['nama_bahan'])
                    ->orWhere('kode_bahan', $bahan['kode_bahan'])
                    ->first();

                if ($existingBahan) {
                    // BAHAN SUDAH ADA - UPDATE HARGA & COA SAJA, JANGAN UBAH STOK
                    echo "  ✅ Bahan sudah ada (ID: {$existingBahan->id})\n";
                    echo "  🔄 Update harga, COA, dan sub satuan...\n";

                    $updateData = array_merge([
                        'kode_bahan' => $bahan['kode_bahan'],
                        'satuan_id' => $satuanId,
                        'harga_satuan' => $bahan['harga_satuan'],
                        'stok_minimum' => $bahan['stok_minimum'],
                        'deskripsi' => $bahan['deskripsi'],
                        'coa_pembelian_id' => $bahan['coa_pembelian'],
                        'coa_persediaan_id' => $bahan['coa_persediaan'],
                        'coa_hpp_id' => $bahan['coa_hpp'],
                        'updated_at' => now()
                    ], $subSatuanData);

                    DB::table('bahan_bakus')
                        ->where('id', $existingBahan->id)
                        ->update($updateData);

                    $bahanId = $existingBahan->id;
                    $updateCount++;
                    
                    echo "  ℹ️  Stok tidak diubah (saat ini: {$existingBahan->stok})\n";
                } else {
                    // BAHAN BELUM ADA - INSERT BARU LENGKAP
                    echo "  ➕ Bahan baru, melakukan insert...\n";

                    // Prepare insert data with sub_satuan
                    $insertData = [
                        'kode_bahan' => $bahan['kode_bahan'],
                        'satuan_id' => $satuanId,
                        'nama_bahan' => $bahan['nama_bahan'],
                        'harga_satuan' => $bahan['harga_satuan'],
                        'stok' => $bahan['stok_awal'],
                        'stok_minimum' => $bahan['stok_minimum'],
                        'deskripsi' => $bahan['deskripsi'],
                        'coa_pembelian_id' => $bahan['coa_pembelian'],
                        'coa_persediaan_id' => $bahan['coa_persediaan'],
                        'coa_hpp_id' => $bahan['coa_hpp'],
                        'harga_rata_rata' => $bahan['harga_satuan'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    // Add sub_satuan data if available
                    if (!empty($subSatuanData)) {
                        $insertData = array_merge($insertData, $subSatuanData);
                    }

                    $bahanId = DB::table('bahan_bakus')->insertGetId($insertData);

                    $insertCount++;
                    echo "  ✅ Insert berhasil (ID: {$bahanId}, Stok: {$bahan['stok_awal']})\n";
                }

                echo "  ✅ Selesai\n\n";
            }

            // Commit transaction
            DB::commit();

            echo str_repeat("=", 50) . "\n";
            echo "🎉 SEEDER BERHASIL DIJALANKAN!\n";
            echo "📊 Ringkasan:\n";
            echo "   - Bahan Baku Baru: {$insertCount}\n";
            echo "   - Bahan Baku Diupdate: {$updateCount}\n";
            echo "   - Total Sub Satuan: " . ($konversiCount) . "\n";
            echo "   - Total Bahan: " . ($insertCount + $updateCount) . "\n";
            echo "   - Status: SUCCESS\n";

            if ($updateCount > 0) {
                echo "\n💡 Catatan:\n";
                echo "   - Stok bahan yang sudah ada TIDAK diubah\n";
                echo "   - Hanya harga dan COA yang diupdate\n";
                echo "   - Data transaksi tetap aman\n";
                echo "   - COA sesuai Excel (tidak hardcode)\n";
                echo "   - Sub satuan dihitung otomatis\n";
            }

        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollback();
            
            echo "\n❌ ERROR TERJADI!\n";
            echo "Pesan: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo "\n🔄 Transaction di-rollback, tidak ada data yang berubah.\n";
            
            throw $e;
        }
    }

    /**
     * Ambil atau buat satuan berdasarkan nama
     */
    private function getSatuanId($namaSatuan)
    {
        // Cari satuan yang sudah ada
        $satuan = DB::table('satuans')
            ->where('nama', $namaSatuan)
            ->first();

        if ($satuan) {
            return $satuan->id;
        }

        // Buat satuan baru jika tidak ditemukan
        echo "    🆕 Membuat satuan baru: {$namaSatuan}\n";
        
        return DB::table('satuans')->insertGetId([
            'nama' => $namaSatuan,
            'kode' => strtoupper(substr($namaSatuan, 0, 3)),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

}