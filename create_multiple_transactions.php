<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get Ayam Kampung
$ayam = \App\Models\BahanBaku::find(2);

// Delete existing movements
\App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->delete();

echo "Creating multiple transactions on same dates...\n";

// Create initial stock on 31/01/2026
$initialStock = new \App\Models\StockMovement();
$initialStock->item_type = 'material';
$initialStock->item_id = $ayam->id;
$initialStock->tanggal = '2026-01-31';
$initialStock->direction = 'in';
$initialStock->qty = 30; // 30 Ekor
$initialStock->satuan = 'Ekor';
$initialStock->unit_cost = 45000;
$initialStock->total_cost = 1350000;
$initialStock->ref_type = 'adjustment';
$initialStock->ref_id = 1;
$initialStock->save();
echo "Created: Initial stock on 31/01/2026 - 30 Ekor\n";

// Create TWO purchases on 02/02/2026
$purchase1 = new \App\Models\StockMovement();
$purchase1->item_type = 'material';
$purchase1->item_id = $ayam->id;
$purchase1->tanggal = '2026-02-02';
$purchase1->direction = 'in';
$purchase1->qty = 8; // 8 Ekor
$purchase1->satuan = 'Ekor';
$purchase1->unit_cost = 60000;
$purchase1->total_cost = 480000;
$purchase1->ref_type = 'purchase';
$purchase1->ref_id = 1;
$purchase1->save();
echo "Created: Purchase #1 on 02/02/2026 - 8 Ekor\n";

$purchase2 = new \App\Models\StockMovement();
$purchase2->item_type = 'material';
$purchase2->item_id = $ayam->id;
$purchase2->tanggal = '2026-02-02';
$purchase2->direction = 'in';
$purchase2->qty = 10; // 10 Ekor
$purchase2->satuan = 'Ekor';
$purchase2->unit_cost = 48000;
$purchase2->total_cost = 480000;
$purchase2->ref_type = 'purchase';
$purchase2->ref_id = 2;
$purchase2->save();
echo "Created: Purchase #2 on 02/02/2026 - 10 Ekor\n";

// Create one purchase on 03/02/2026
$purchase3 = new \App\Models\StockMovement();
$purchase3->item_type = 'material';
$purchase3->item_id = $ayam->id;
$purchase3->tanggal = '2026-02-03';
$purchase3->direction = 'in';
$purchase3->qty = 5; // 5 Ekor
$purchase3->satuan = 'Ekor';
$purchase3->unit_cost = 50000;
$purchase3->total_cost = 250000;
$purchase3->ref_type = 'purchase';
$purchase3->ref_id = 3;
$purchase3->save();
echo "Created: Purchase #3 on 03/02/2026 - 5 Ekor\n";

echo "\nExpected result:\n";
echo "- 31/01: 1 baris (initial stock)\n";
echo "- 02/02: 2 baris (2 purchases terpisah)\n";
echo "- 03/02: 1 baris (1 purchase)\n";
echo "- Total: 4 baris transaksi individual\n";
