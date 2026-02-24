<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get Ayam Kampung
$ayam = \App\Models\BahanBaku::where('nama_bahan', 'like', '%Ayam Kampung%')->first();

// Delete existing movements for clean test
\App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->delete();

echo "Creating test data to match user example...\n";

// Create initial stock on 01/02/2026 (30 Ekor = 180 Potong = Rp 1,350,000)
$initialStock = new \App\Models\StockMovement();
$initialStock->item_type = 'material';
$initialStock->item_id = $ayam->id;
$initialStock->tanggal = '2026-02-01';
$initialStock->direction = 'in';
$initialStock->qty = 30; // 30 Ekor
$initialStock->unit_cost = 45000; // Rp 45,000 per Ekor
$initialStock->total_cost = 1350000; // Rp 1,350,000 total
$initialStock->ref_type = 'adjustment';
$initialStock->ref_id = 1;
$initialStock->keterangan = 'Initial stock adjustment';
$initialStock->save();
echo "Created: Initial stock on 01/02/2026 - 30 Ekor (Rp 1,350,000)\n";

// Create purchase on 02/02/2026 (8 Ekor = 48 Potong = Rp 480,000)
$purchase1 = new \App\Models\StockMovement();
$purchase1->item_type = 'material';
$purchase1->item_id = $ayam->id;
$purchase1->tanggal = '2026-02-02';
$purchase1->direction = 'in';
$purchase1->qty = 8; // 8 Ekor
$purchase1->unit_cost = 60000; // Rp 60,000 per Ekor
$purchase1->total_cost = 480000; // Rp 480,000 total
$purchase1->ref_type = 'purchase';
$purchase1->ref_id = 1;
$purchase1->keterangan = 'Purchase from vendor';
$purchase1->save();
echo "Created: Purchase on 02/02/2026 - 8 Ekor (Rp 480,000)\n";

// Create purchase on 03/02/2026 (10 Ekor = 60 Potong = Rp 480,000)
$purchase2 = new \App\Models\StockMovement();
$purchase2->item_type = 'material';
$purchase2->item_id = $ayam->id;
$purchase2->tanggal = '2026-02-03';
$purchase2->direction = 'in';
$purchase2->qty = 10; // 10 Ekor
$purchase2->unit_cost = 48000; // Rp 48,000 per Ekor
$purchase2->total_cost = 480000; // Rp 480,000 total
$purchase2->ref_type = 'purchase';
$purchase2->ref_id = 2;
$purchase2->keterangan = 'Purchase from vendor';
$purchase2->save();
echo "Created: Purchase on 03/02/2026 - 10 Ekor (Rp 480,000)\n";

echo "\nTest data created successfully!\n";
echo "Expected results:\n";
echo "01/02/2026: 30 Ekor = 180 Potong = Rp 1,350,000 (Harga: Rp 7,500/Potong)\n";
echo "02/02/2026: +8 Ekor = +48 Potong = +Rp 480,000, Total: 38 Ekor = 228 Potong = Rp 1,830,000 (Harga: Rp 8,026.32/Potong)\n";
echo "03/02/2026: +10 Ekor = +60 Potong = +Rp 480,000, Total: 48 Ekor = 288 Potong = Rp 2,310,000 (Harga: Rp 8,020.83/Potong)\n";
