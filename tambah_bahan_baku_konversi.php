<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TAMBAH BAHAN BAKU DENGAN KONVERSI SATUAN ===" . PHP_EOL;

try {
    // 1. Cek satuan yang tersedia
    echo "CEK SATUAN YANG TERSEDIA:" . PHP_EOL;
    $satuans = \App\Models\Satuan::all();
    $satuanMap = [];
    
    foreach ($satuans as $satuan) {
        $satuanMap[strtolower($satuan->nama)] = $satuan;
        echo "- ID: {$satuan->id}, Nama: {$satuan->nama}" . PHP_EOL;
    }
    
    // 2. Siapkan data bahan baku
    echo PHP_EOL . "MENAMBAHKAN DATA BAHAN BAKU:" . PHP_EOL;
    
    $dataBahan = [
        [
            'nama' => 'Ayam Potong',
            'satuan_utama' => 'Kilogram',
            'stok_awal' => 50,
            'harga_satuan' => 32000,
            'konversi' => [
                'sub_satuan_1' => [
                    'satuan' => 'Gram',
                    'harga' => 32,
                    'konversi' => '1 Kilogram = 1.000 Gram'
                ],
                'sub_satuan_2' => [
                    'satuan' => 'Potong',
                    'harga' => 8000,
                    'konversi' => '1 Kilogram = 4 Potong'
                ],
                'sub_satuan_3' => [
                    'satuan' => 'Ons',
                    'harga' => 3200,
                    'konversi' => '1 Kilogram = 10 Ons'
                ]
            ]
        ],
        [
            'nama' => 'Ayam Kampung',
            'satuan_utama' => 'Ekor',
            'stok_awal' => 30,
            'harga_satuan' => 45000,
            'konversi' => [
                'sub_satuan_1' => [
                    'satuan' => 'Potong',
                    'harga' => 7500,
                    'konversi' => '1 Ekor = 6 Potong'
                ],
                'sub_satuan_2' => [
                    'satuan' => 'Kilogram',
                    'harga' => 30000,
                    'konversi' => '1 Ekor = 1,5 Kilogram'
                ],
                'sub_satuan_3' => [
                    'satuan' => 'Gram',
                    'harga' => 30,
                    'konversi' => '1 Ekor = 1.500 Gram'
                ]
            ]
        ]
    ];
    
    foreach ($dataBahan as $index => $bahan) {
        echo PHP_EOL . ($index + 1) . ". Menambahkan: {$bahan['nama']}" . PHP_EOL;
        
        // Cek apakah sudah ada
        $existingBahan = \App\Models\BahanBaku::where('nama_bahan', $bahan['nama'])->first();
        if ($existingBahan) {
            echo "   ⚠️  Sudah ada, melewati..." . PHP_EOL;
            continue;
        }
        
        // Cari satuan utama
        $satuanUtamaKey = strtolower($bahan['satuan_utama']);
        $satuanUtama = $satuanMap[$satuanUtamaKey] ?? null;
        
        if (!$satuanUtama) {
            echo "   ❌ Satuan utama '{$bahan['satuan_utama']}' tidak ditemukan!" . PHP_EOL;
            continue;
        }
        
        echo "   Satuan utama: {$satuanUtama->nama} (ID: {$satuanUtama->id})" . PHP_EOL;
        
        // Buat bahan baku
        $newBahan = \App\Models\BahanBaku::create([
            'nama_bahan' => $bahan['nama'],
            'kode_bahan' => 'BB-' . str_pad(($index + 1), 3, '0', STR_PAD_LEFT),
            'satuan_id' => $satuanUtama->id,
            'satuan_dasar' => strtolower($bahan['satuan_utama']),
            'harga_satuan' => $bahan['harga_satuan'],
            'harga_per_satuan_dasar' => $bahan['harga_satuan'],
            'harga_rata_rata' => $bahan['harga_satuan'],
            'stok' => $bahan['stok_awal'],
            'stok_minimum' => 10,
            'deskripsi' => "Stok awal {$bahan['stok_awal']} {$bahan['satuan_utama']}"
        ]);
        
        echo "   ✅ Bahan baku dibuat: ID {$newBahan->id}" . PHP_EOL;
        echo "   💰 Harga: Rp " . number_format($bahan['harga_satuan'], 2, ',', '.') . "/{$bahan['satuan_utama']}" . PHP_EOL;
        echo "   📦 Stok: {$bahan['stok_awal']} {$bahan['satuan_utama']}" . PHP_EOL;
        
        // Tambahkan konversi satuan
        echo "   🔄 Menambahkan konversi satuan:" . PHP_EOL;
        
        foreach ($bahan['konversi'] as $key => $konversi) {
            $fieldMap = [
                'sub_satuan_1' => 'sub_satuan_1_id',
                'sub_satuan_2' => 'sub_satuan_2_id', 
                'sub_satuan_3' => 'sub_satuan_3_id'
            ];
            
            $valueMap = [
                'sub_satuan_1' => 'sub_satuan_1_nilai',
                'sub_satuan_2' => 'sub_satuan_2_nilai',
                'sub_satuan_3' => 'sub_satuan_3_nilai'
            ];
            
            $konversiKey = strtolower($konversi['satuan']);
            $satuanKonversi = $satuanMap[$konversiKey] ?? null;
            
            if ($satuanKonversi) {
                $updateData = [
                    $fieldMap[$key] => $satuanKonversi->id,
                    $valueMap[$key] => $konversi['harga']
                ];
                
                $newBahan->update($updateData);
                
                echo "     - {$konversi['satuan']}: Rp " . 
                     number_format($konversi['harga'], 2, ',', '.') . 
                     " ({$konversi['konversi']})" . PHP_EOL;
            } else {
                echo "     ❌ Satuan '{$konversi['satuan']}' tidak ditemukan" . PHP_EOL;
            }
        }
        
        echo "   ✅ Selesai!" . PHP_EOL;
    }
    
    // 3. Verifikasi hasil
    echo PHP_EOL . "VERIFIKASI HASIL:" . PHP_EOL;
    
    $finalBahan = \App\Models\BahanBaku::whereIn('nama_bahan', ['Ayam Potong', 'Ayam Kampung'])->get();
    
    foreach ($finalBahan as $bahan) {
        echo PHP_EOL . "📋 {$bahan->nama_bahan}:" . PHP_EOL;
        echo "   ID: {$bahan->id}" . PHP_EOL;
        echo "   Kode: {$bahan->kode_bahan}" . PHP_EOL;
        echo "   Satuan: {$bahan->satuan}" . PHP_EOL;
        echo "   Harga: Rp " . number_format($bahan->harga_satuan, 2, ',', '.') . PHP_EOL;
        echo "   Stok: {$bahan->stok} {$bahan->satuan}" . PHP_EOL;
        
        if ($bahan->sub_satuan_1_id) {
            $satuan1 = \App\Models\Satuan::find($bahan->sub_satuan_1_id);
            echo "   Sub Satuan 1: {$satuan1->nama} - Rp " . 
                 number_format($bahan->sub_satuan_1_nilai, 2, ',', '.') . PHP_EOL;
        }
        
        if ($bahan->sub_satuan_2_id) {
            $satuan2 = \App\Models\Satuan::find($bahan->sub_satuan_2_id);
            echo "   Sub Satuan 2: {$satuan2->nama} - Rp " . 
                 number_format($bahan->sub_satuan_2_nilai, 2, ',', '.') . PHP_EOL;
        }
        
        if ($bahan->sub_satuan_3_id) {
            $satuan3 = \App\Models\Satuan::find($bahan->sub_satuan_3_id);
            echo "   Sub Satuan 3: {$satuan3->nama} - Rp " . 
                 number_format($bahan->sub_satuan_3_nilai, 2, ',', '.') . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "✅ Semua data bahan baku telah ditambahkan!" . PHP_EOL;
    echo "📝 Sekarang Anda bisa melihatnya di halaman master-data/bahan-baku" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
