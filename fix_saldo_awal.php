<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Memperbaiki Saldo Awal ===\n";

// Get Ayam Kampung
$ayam = \App\Models\BahanBaku::find(2);

// Delete all existing stock movements
\App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->delete();

echo "Menghapus semua stock movements lama...\n";

// Create initial stock adjustment on 31/01/2026 (saldo awal yang dibawa ke Februari)
$initialStock = new \App\Models\StockMovement();
$initialStock->item_type = 'material';
$initialStock->item_id = $ayam->id;
$initialStock->tanggal = '2026-01-31';
$initialStock->direction = 'in';
$initialStock->qty = 20; // 20 Ekor = 120 Potong - ini adalah saldo awal real
$initialStock->satuan = 'Ekor';
$initialStock->unit_cost = 45000; // Rp 45,000 per Ekor
$initialStock->total_cost = 900000; // Rp 900,000 total
$initialStock->ref_type = 'adjustment';
$initialStock->ref_id = 1;
$initialStock->save();
echo "Created: Initial stock on 31/01/2026 - 20 Ekor (120 Potong) @ Rp 45,000 = Rp 900,000\n";

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

echo "\n=== Data yang Dibuat ===\n";
echo "1. 31/01/2026: Initial stock 20 Ekor (120 Potong) @ Rp 45,000 = Rp 900,000\n";
echo "2. 02/02/2026: Purchase PB-20260202-0001 - 8 Ekor (48 Potong) @ Rp 60,000 = Rp 480,000\n";
echo "3. 03/02/2026: Purchase PB-20260203-0001 - 10 Ekor (60 Potong) @ Rp 48,000 = Rp 480,000\n";

echo "\nTotal: 38 Ekor (228 Potong) = Rp 1.860.000\n";

echo "\n=== Hasil yang Diharapkan di Kartu Stok ===\n";
echo "Tanggal: 01/02/2026 [SALDO AWAL BULAN]\n";
echo "Stok Awal: 120 Potong @ Rp 7,500 = Rp 900,000\n";
echo "Total: 120 Potong @ Rp 7,500 = Rp 900,000\n";
echo "\nTanggal: 02/02/2026 [Purchase #1]\n";
echo "Stok Awal: 0 Potong @ Rp 0 = Rp 0\n";
echo "Pembelian: 48 Potong @ Rp 10,000 = Rp 480,000\n";
echo "Total: 168 Potong @ Rp 8,214.29 = Rp 1,380,000\n";
echo "\nTanggal: 03/02/2026 [Purchase #2]\n";
echo "Stok Awal: 0 Potong @ Rp 0 = Rp 0\n";
echo "Pembelian: 60 Potong @ Rp 8,000 = Rp 480,000\n";
echo "Total: 228 Potong @ Rp 8,157.89 = Rp 1,860,000\n";

echo "\n=== Catatan ===\n";
echo "Anda bisa mengubah nilai saldo awal (20 Ekor = 120 Potong = Rp 900,000) sesuai dengan saldo awal real yang Anda miliki.\n";
echo "Silakan sesuaikan qty dan harga di adjustment jika perlu.\n";
