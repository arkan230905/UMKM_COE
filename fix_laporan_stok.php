<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PERBAIKI LAPORAN STOK JAGUNG ===\n";

try {
    DB::beginTransaction();
    
    // 1. Cek kartu stok saat ini
    echo "1. Kartu stok jagung saat ini:\n";
    $kartuStok = DB::table('kartu_stok')
        ->where('item_id', 1)
        ->where('item_type', 'bahan_baku')
        ->orderBy('tanggal')
        ->orderBy('id')
        ->get();
    
    $runningBalance = 0;
    foreach ($kartuStok as $kartu) {
        $masuk = $kartu->qty_masuk ?? 0;
        $keluar = $kartu->qty_keluar ?? 0;
        $runningBalance += ($masuk - $keluar);
        
        echo "   - {$kartu->tanggal} | {$kartu->ref_type} | Masuk: {$masuk} | Keluar: {$keluar} | Balance: {$runningBalance}\n";
    }
    
    // 2. Reset dan buat ulang kartu stok yang benar
    echo "\n2. Reset dan buat ulang kartu stok:\n";
    
    // Hapus semua kartu stok jagung
    DB::table('kartu_stok')
        ->where('item_id', 1)
        ->where('item_type', 'bahan_baku')
        ->delete();
    
    echo "   ✓ Kartu stok lama dihapus\n";
    
    // Buat entry yang benar sesuai urutan
    $entries = [
        [
            'tanggal' => '2026-04-23',
            'ref_type' => 'saldo_awal',
            'ref_id' => null,
            'qty_masuk' => 12,
            'qty_keluar' => null,
            'keterangan' => 'Saldo Awal'
        ],
        [
            'tanggal' => '2026-04-24',
            'ref_type' => 'pembelian',
            'ref_id' => 5,
            'qty_masuk' => 5,
            'qty_keluar' => null,
            'keterangan' => 'Pembelian PB-20260424-0005'
        ],
        [
            'tanggal' => '2026-04-24',
            'ref_type' => 'retur',
            'ref_id' => 1,
            'qty_masuk' => null,
            'qty_keluar' => 2,
            'keterangan' => 'Retur Pembelian PRTN-20260424-0001'
        ]
    ];
    
    $runningBalance = 0;
    foreach ($entries as $entry) {
        DB::table('kartu_stok')->insert([
            'tanggal' => $entry['tanggal'],
            'item_id' => 1,
            'item_type' => 'bahan_baku',
            'qty_masuk' => $entry['qty_masuk'],
            'qty_keluar' => $entry['qty_keluar'],
            'keterangan' => $entry['keterangan'],
            'ref_type' => $entry['ref_type'],
            'ref_id' => $entry['ref_id'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $masuk = $entry['qty_masuk'] ?? 0;
        $keluar = $entry['qty_keluar'] ?? 0;
        $runningBalance += ($masuk - $keluar);
        
        echo "   ✓ {$entry['tanggal']} | {$entry['keterangan']} | Balance: {$runningBalance} kg\n";
    }
    
    // 3. Update field stok di bahan_bakus
    echo "\n3. Update stok di tabel bahan_bakus:\n";
    
    $jagung = \App\Models\BahanBaku::find(1);
    $jagung->stok = $runningBalance; // 15 kg
    $jagung->save();
    
    echo "   ✓ Field stok diupdate menjadi: {$runningBalance} kg\n";
    
    // 4. Verifikasi hasil
    echo "\n4. Verifikasi hasil:\n";
    
    $newBalance = \App\Models\KartuStok::getStockBalance(1, 'bahan_baku');
    echo "   - Kartu stok balance: {$newBalance} kg\n";
    
    $jagung->refresh();
    echo "   - Field stok: {$jagung->stok} kg\n";
    echo "   - Stok real time: {$jagung->stok_real_time} kg\n";
    
    // 5. Cek konsistensi
    if ($newBalance == 15 && $jagung->stok == 15) {
        echo "\n✓ BERHASIL! Semua sistem stok sudah konsisten: 15 kg\n";
        echo "Laporan stok akan menampilkan:\n";
        echo "- Saldo Awal: 12 kg\n";
        echo "- Pembelian: +5 kg → Total: 17 kg\n";
        echo "- Retur Pembelian: -2 kg → Sisa: 15 kg\n";
    } else {
        echo "\n✗ Masih ada inkonsistensi\n";
    }
    
    DB::commit();
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ Error: " . $e->getMessage() . "\n";
}