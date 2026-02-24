<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Membuat Test Data Bahan Pendukung ===\n";

// Get Air (ID: 14)
$air = \App\Models\BahanPendukung::find(14);
echo "Air - ID: {$air->id}, Nama: {$air->nama_bahan}\n";
echo "Satuan Utama: " . ($air->satuanRelation->nama_satuan ?? 'N/A') . "\n";
echo "Sub Satuan 1: " . ($air->sub_satuan_1->nama_satuan ?? 'N/A') . " (Konversi: " . ($air->sub_satuan_1_nilai ?? 'N/A') . ")\n";

// Delete existing stock movements for Air
\App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $air->id)
    ->delete();

echo "Menghapus stock movements lama untuk Air...\n";

// Create initial stock adjustment on 31/01/2026
$initialStock = new \App\Models\StockMovement();
$initialStock->item_type = 'support'; // Use 'support' for Bahan Pendukung
$initialStock->item_id = $air->id;
$initialStock->tanggal = '2026-01-31';
$initialStock->direction = 'in';
$initialStock->qty = 100; // 100 Liter
$initialStock->satuan = 'Liter';
$initialStock->unit_cost = 1000; // Rp 1,000 per Liter
$initialStock->total_cost = 100000; // Rp 100,000 total
$initialStock->ref_type = 'adjustment';
$initialStock->ref_id = 1;
$initialStock->save();
echo "Created: Initial stock on 31/01/2026 - 100 Liter (Rp 100,000)\n";

// Create purchase on 02/02/2026
$purchase1 = new \App\Models\StockMovement();
$purchase1->item_type = 'support'; // Use 'support' for Bahan Pendukung
$purchase1->item_id = $air->id;
$purchase1->tanggal = '2026-02-02';
$purchase1->direction = 'in';
$purchase1->qty = 50; // 50 Liter
$purchase1->satuan = 'Liter';
$purchase1->unit_cost = 1200; // Rp 1,200 per Liter
$purchase1->total_cost = 60000; // Rp 60,000 total
$purchase1->ref_type = 'purchase';
$purchase1->ref_id = 1;
$purchase1->save();
echo "Created: Purchase on 02/02/2026 - 50 Liter (Rp 60,000)\n";

// Create purchase on 03/02/2026
$purchase2 = new \App\Models\StockMovement();
$purchase2->item_type = 'support'; // Use 'support' for Bahan Pendukung
$purchase2->item_id = $air->id;
$purchase2->tanggal = '2026-02-03';
$purchase2->direction = 'in';
$purchase2->qty = 30; // 30 Liter
$purchase2->satuan = 'Liter';
$purchase2->unit_cost = 1500; // Rp 1,500 per Liter
$purchase2->total_cost = 45000; // Rp 45,000 total
$purchase2->ref_type = 'purchase';
$purchase2->ref_id = 2;
$purchase2->save();
echo "Created: Purchase on 03/02/2026 - 30 Liter (Rp 45,000)\n";

echo "\n=== Test Data untuk Air ===\n";
echo "1. 31/01/2026: Initial stock 100 Liter @ Rp 1,000 = Rp 100,000\n";
echo "2. 02/02/2026: Purchase 50 Liter @ Rp 1,200 = Rp 60,000\n";
echo "3. 03/02/2026: Purchase 30 Liter @ Rp 1,500 = Rp 45,000\n";

echo "\nTotal: 180 Liter = Rp 205,000\n";
echo "\nSekarang Anda bisa test filter Bahan Pendukung > Air\n";
