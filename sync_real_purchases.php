<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Sinkronisasi Stock Movements dengan Data Pembelian Riil ===\n";

// Get Ayam Kampung
$ayam = \App\Models\BahanBaku::find(2);

// Delete all existing stock movements
\App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->delete();

echo "Menghapus semua stock movements lama...\n";

// Create initial stock adjustment on 01/02/2026 (saldo awal bulan Februari)
$initialStock = new \App\Models\StockMovement();
$initialStock->item_type = 'material';
$initialStock->item_id = $ayam->id;
$initialStock->tanggal = '2026-02-01';
$initialStock->direction = 'in';
$initialStock->qty = 0; // 0 Ekor - mulai dari nol
$initialStock->satuan = 'Ekor';
$initialStock->unit_cost = 0;
$initialStock->total_cost = 0;
$initialStock->ref_type = 'adjustment';
$initialStock->ref_id = 1;
$initialStock->save();
echo "Created: Initial stock on 01/02/2026 - 0 Ekor (mulai dari nol)\n";

// Create purchase on 02/02/2026 - 8 Ekor (48 Potong) - PB-20260202-0001
$purchase1 = new \App\Models\StockMovement();
$purchase1->item_type = 'material';
$purchase1->item_id = $ayam->id;
$purchase1->tanggal = '2026-02-02';
$purchase1->direction = 'in';
$purchase1->qty = 8; // 8 Ekor = 48 Potong
$purchase1->satuan = 'Ekor';
$purchase1->unit_cost = 60000; // Rp 60,000 per Ekor
$purchase1->total_cost = 480000; // Rp 480,000 total
$purchase1->ref_type = 'purchase';
$purchase1->ref_id = 1; // PB-20260202-0001
$purchase1->save();
echo "Created: Purchase PB-20260202-0001 on 02/02/2026 - 8 Ekor = 48 Potong (Rp 480,000)\n";

// Create purchase on 03/02/2026 - 10 Ekor (60 Potong) - PB-20260203-0001
$purchase2 = new \App\Models\StockMovement();
$purchase2->item_type = 'material';
$purchase2->item_id = $ayam->id;
$purchase2->tanggal = '2026-02-03';
$purchase2->direction = 'in';
$purchase2->qty = 10; // 10 Ekor = 60 Potong
$purchase2->satuan = 'Ekor';
$purchase2->unit_cost = 48000; // Rp 48,000 per Ekor
$purchase2->total_cost = 480000; // Rp 480,000 total
$purchase2->ref_type = 'purchase';
$purchase2->ref_id = 2; // PB-20260203-0001
$purchase2->save();
echo "Created: Purchase PB-20260203-0001 on 03/02/2026 - 10 Ekor = 60 Potong (Rp 480,000)\n";

echo "\n=== Data yang Disinkronisasi ===\n";
echo "1. 01/02/2026: Initial stock 0 Ekor (mulai dari nol)\n";
echo "2. 02/02/2026: Purchase PB-20260202-0001 - 8 Ekor (48 Potong) @ Rp 60,000 = Rp 480,000\n";
echo "3. 03/02/2026: Purchase PB-20260203-0001 - 10 Ekor (60 Potong) @ Rp 48,000 = Rp 480,000\n";

echo "\nTotal: 18 Ekor (108 Potong) = Rp 960.000\n";

echo "\n=== Hasil yang Diharapkan di Kartu Stok ===\n";
echo "Tanggal: 01/02/2026 [SALDO AWAL BULAN]\n";
echo "Stok Awal: 0 Potong @ Rp 0 = Rp 0\n";
echo "Total: 0 Potong @ Rp 0 = Rp 0\n";
echo "\nTanggal: 02/02/2026 [Purchase #1]\n";
echo "Stok Awal: 0 Potong @ Rp 0 = Rp 0\n";
echo "Pembelian: 48 Potong @ Rp 10,000 = Rp 480,000\n";
echo "Total: 48 Potong @ Rp 10,000 = Rp 480,000\n";
echo "\nTanggal: 03/02/2026 [Purchase #2]\n";
echo "Stok Awal: 0 Potong @ Rp 0 = Rp 0\n";
echo "Pembelian: 60 Potong @ Rp 8,000 = Rp 480,000\n";
echo "Total: 108 Potong @ Rp 8,888.89 = Rp 960,000\n";
