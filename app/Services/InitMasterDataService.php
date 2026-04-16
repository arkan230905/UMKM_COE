<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InitMasterDataService
{
    /**
     * Initialize master data untuk user baru
     * Membuat bahan baku, bahan pendukung, dan konversi default
     */
    public function initializeForUser($userId)
    {
        try {
            // Mulai transaction untuk keamanan
            DB::beginTransaction();
            
            Log::info("Initializing master data for user: {$userId}");
            
            // Cek apakah user sudah memiliki data master
            if ($this->userHasMasterData($userId)) {
                Log::info("User {$userId} already has master data, skipping initialization");
                DB::rollback();
                return false;
            }
            
            // Initialize bahan baku
            $bahanBakuCount = $this->initializeBahanBaku($userId);
            
            // Initialize bahan pendukung
            $bahanPendukungCount = $this->initializeBahanPendukung($userId);
            
            // Commit transaction
            DB::commit();
            
            Log::info("Master data initialized successfully for user {$userId}: {$bahanBakuCount} bahan baku, {$bahanPendukungCount} bahan pendukung");
            
            return [
                'success' => true,
                'bahan_baku_count' => $bahanBakuCount,
                'bahan_pendukung_count' => $bahanPendukungCount
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to initialize master data for user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cek apakah user sudah memiliki master data
     */
    private function userHasMasterData($userId)
    {
        $bahanBakuExists = DB::table('bahan_bakus')
            ->where('user_id', $userId)
            ->exists();
            
        $bahanPendukungExists = DB::table('bahan_pendukungs')
            ->where('user_id', $userId)
            ->exists();
            
        return $bahanBakuExists || $bahanPendukungExists;
    }
    
    /**
     * Initialize bahan baku default untuk user
     */
    private function initializeBahanBaku($userId)
    {
        $bahanBakuData = [
            [
                'kode_bahan' => 'AYM001',
                'nama_bahan' => 'Ayam Potong',
                'satuan_utama' => 'Kilogram',
                'harga_satuan' => 32000,
                'stok_awal' => 0, // Stok awal 0 untuk user baru
                'stok_minimum' => 5,
                'deskripsi' => 'Ayam potong segar',
                'coa_pembelian' => '510',
                'coa_persediaan' => '1141',
                'coa_hpp' => '510',
                'sub_satuan' => [
                    ['satuan' => 'Gram', 'konversi' => 1000, 'harga_per_unit' => 32],
                    ['satuan' => 'Potong', 'konversi' => 4, 'harga_per_unit' => 8000],
                    ['satuan' => 'Ons', 'konversi' => 10, 'harga_per_unit' => 3200]
                ]
            ],
            [
                'kode_bahan' => 'AYM002',
                'nama_bahan' => 'Ayam Kampung',
                'satuan_utama' => 'Ekor',
                'harga_satuan' => 45000,
                'stok_awal' => 0,
                'stok_minimum' => 5,
                'deskripsi' => 'Ayam kampung segar',
                'coa_pembelian' => '511',
                'coa_persediaan' => '1142',
                'coa_hpp' => '511',
                'sub_satuan' => [
                    ['satuan' => 'Potong', 'konversi' => 6, 'harga_per_unit' => 7500],
                    ['satuan' => 'Kilogram', 'konversi' => 1.5, 'harga_per_unit' => 30000],
                    ['satuan' => 'Gram', 'konversi' => 1500, 'harga_per_unit' => 30]
                ]
            ]
        ];
        
        $insertCount = 0;
        
        foreach ($bahanBakuData as $bahan) {
            // Cek apakah bahan sudah ada untuk user ini
            $exists = DB::table('bahan_bakus')
                ->where('user_id', $userId)
                ->where('nama_bahan', $bahan['nama_bahan'])
                ->exists();
                
            if ($exists) {
                continue; // Skip jika sudah ada
            }
            
            // Ambil satuan_id
            $satuanId = $this->getSatuanId($bahan['satuan_utama']);
            
            // Siapkan data sub satuan
            $subSatuanData = [];
            for ($i = 0; $i < min(3, count($bahan['sub_satuan'])); $i++) {
                if (isset($bahan['sub_satuan'][$i])) {
                    $subSatuan = $bahan['sub_satuan'][$i];
                    $subSatuanId = $this->getSatuanId($subSatuan['satuan']);
                    
                    $subSatuanData['sub_satuan_' . ($i + 1) . '_id'] = $subSatuanId;
                    $subSatuanData['sub_satuan_' . ($i + 1) . '_konversi'] = $subSatuan['konversi'];
                    $subSatuanData['sub_satuan_' . ($i + 1) . '_nilai'] = $subSatuan['harga_per_unit'];
                }
            }
            
            // Insert bahan baku
            $insertData = array_merge([
                'user_id' => $userId,
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
            ], $subSatuanData);
            
            DB::table('bahan_bakus')->insertGetId($insertData);
            $insertCount++;
        }
        
        return $insertCount;
    }
    
    /**
     * Initialize bahan pendukung default untuk user
     */
    private function initializeBahanPendukung($userId)
    {
        $bahanPendukungData = [
            [
                'kode_bahan' => 'AIR001',
                'nama_bahan' => 'Air',
                'kategori' => 'air',
                'satuan_utama' => 'Liter',
                'harga_satuan' => 1000,
                'stok_awal' => 0,
                'stok_minimum' => 5,
                'deskripsi' => 'Air Mineral',
                'coa_pembelian' => 'BOP-Air (531)',
                'coa_persediaan' => 'Pers. Bahan Pendukung Air (1150)',
                'coa_hpp' => 'BOP-Air (531)',
                'konversi' => [
                    ['satuan' => 'Kg', 'nilai' => 1],
                    ['satuan' => 'Ml', 'nilai' => 1000],
                    ['satuan' => 'Galon', 'nilai' => 0.067]
                ]
            ],
            [
                'kode_bahan' => 'TPG001',
                'nama_bahan' => 'Tepung Terigu',
                'kategori' => 'bumbu',
                'satuan_utama' => 'Bungkus',
                'harga_satuan' => 50000,
                'stok_awal' => 0,
                'stok_minimum' => 5,
                'deskripsi' => 'Perbumbuan',
                'coa_pembelian' => 'BOP-Tepung Terigu (534)',
                'coa_persediaan' => 'Pers. Bahan Pendukung Tepung Terigu (1153)',
                'coa_hpp' => 'BOP-Tepung Terigu (534)',
                'konversi' => [
                    ['satuan' => 'Gram', 'nilai' => 500],
                    ['satuan' => 'Sdt', 'nilai' => 12500],
                    ['satuan' => 'Sdm', 'nilai' => 4.7]
                ]
            ],
            [
                'kode_bahan' => 'LDA001',
                'nama_bahan' => 'Lada',
                'kategori' => 'bumbu',
                'satuan_utama' => 'Bungkus',
                'harga_satuan' => 15000,
                'stok_awal' => 0,
                'stok_minimum' => 5,
                'deskripsi' => 'Perbumbuan',
                'coa_pembelian' => 'BOP- Lada (536)',
                'coa_persediaan' => 'Pers. Bahan Pendukung Lada (1155)',
                'coa_hpp' => 'BOP- Lada (536)',
                'konversi' => [
                    ['satuan' => 'Sdt', 'nilai' => 5],
                    ['satuan' => 'Kg', 'nilai' => 0.01],
                    ['satuan' => 'Sdm', 'nilai' => 1.5]
                ]
            ]
        ];
        
        $insertCount = 0;
        
        foreach ($bahanPendukungData as $bahan) {
            // Cek apakah bahan sudah ada untuk user ini
            $exists = DB::table('bahan_pendukungs')
                ->where('user_id', $userId)
                ->where('nama_bahan', $bahan['nama_bahan'])
                ->exists();
                
            if ($exists) {
                continue; // Skip jika sudah ada
            }
            
            // Ambil satuan_id
            $satuanId = $this->getSatuanId($bahan['satuan_utama']);
            
            // Ambil COA IDs
            $coaPembelianId = $this->getOrCreateCoa($bahan['coa_pembelian']);
            $coaPersediaanId = $this->getOrCreateCoa($bahan['coa_persediaan']);
            $coaHppId = $this->getOrCreateCoa($bahan['coa_hpp']);
            
            // Siapkan data sub satuan
            $subSatuanData = [];
            for ($i = 0; $i < min(3, count($bahan['konversi'])); $i++) {
                if (isset($bahan['konversi'][$i])) {
                    $konversi = $bahan['konversi'][$i];
                    $subSatuanId = $this->getSatuanId($konversi['satuan']);
                    
                    // Calculate price per sub unit: harga_utama / nilai_konversi
                    $hargaPerUnit = $bahan['harga_satuan'] / $konversi['nilai'];
                    
                    $subSatuanData['sub_satuan_' . ($i + 1) . '_id'] = $subSatuanId;
                    $subSatuanData['sub_satuan_' . ($i + 1) . '_konversi'] = $konversi['nilai'];
                    $subSatuanData['sub_satuan_' . ($i + 1) . '_nilai'] = $hargaPerUnit;
                }
            }
            
            // Insert bahan pendukung
            $insertData = array_merge([
                'user_id' => $userId,
                'kode_bahan' => $bahan['kode_bahan'],
                'nama_bahan' => $bahan['nama_bahan'],
                'satuan_id' => $satuanId,
                'harga_satuan' => $bahan['harga_satuan'],
                'stok' => $bahan['stok_awal'],
                'stok_minimum' => $bahan['stok_minimum'],
                'deskripsi' => $bahan['deskripsi'],
                'kategori' => $bahan['kategori'],
                'coa_pembelian_id' => $coaPembelianId,
                'coa_persediaan_id' => $coaPersediaanId,
                'coa_hpp_id' => $coaHppId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ], $subSatuanData);

            $bahanId = DB::table('bahan_pendukungs')->insertGetId($insertData);

            // Insert konversi untuk bahan ini
            foreach ($bahan['konversi'] as $konversi) {
                $konversiSatuanId = $this->getSatuanId($konversi['satuan']);

                // Gunakan updateOrInsert untuk menghindari duplikasi
                DB::table('bahan_konversi')->updateOrInsert(
                    [
                        'bahan_id' => $bahanId,
                        'satuan_id' => $konversiSatuanId
                    ],
                    [
                        'nilai' => $konversi['nilai'],
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
            }
            
            $insertCount++;
        }
        
        return $insertCount;
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
        return DB::table('satuans')->insertGetId([
            'nama' => $namaSatuan,
            'kode' => strtoupper(substr($namaSatuan, 0, 3)),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Get or create COA account
     */
    private function getOrCreateCoa($coaName)
    {
        // Extract kode akun from name (e.g., "BOP-Air (531)" -> "531")
        preg_match('/\((\d+)\)/', $coaName, $matches);
        $kodeAkun = $matches[1] ?? null;
        
        if (!$kodeAkun) {
            return null;
        }

        // Check if COA exists
        $existingCoa = DB::table('coas')->where('kode_akun', $kodeAkun)->first();
        
        if ($existingCoa) {
            return $kodeAkun;
        }

        // Create new COA if not exists
        $namaAkun = trim(preg_replace('/\(\d+\)/', '', $coaName));
        
        // Determine tipe_akun based on kode_akun
        $tipeAkun = 'beban'; // default
        if ($kodeAkun >= 1000 && $kodeAkun < 2000) {
            $tipeAkun = 'aset';
        }
        
        DB::table('coas')->insert([
            'kode_akun' => $kodeAkun,
            'nama_akun' => $namaAkun,
            'tipe_akun' => $tipeAkun,
            'saldo_normal' => $tipeAkun === 'aset' ? 'debit' : 'debit',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $kodeAkun;
    }
}