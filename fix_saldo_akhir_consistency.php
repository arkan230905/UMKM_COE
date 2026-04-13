<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Saldo Akhir Consistency ===\n";

echo "PROBLEM IDENTIFIED:\n";
echo "- Saldo Awal: 200 potong (correct with historical conversion 4 potong/kg)\n";
echo "- Saldo Akhir: 150 potong (wrong - should be 200 potong for same row)\n";

echo "\nROOT CAUSE:\n";
echo "The view calculates saldo_akhir using current conversion rate instead of maintaining consistency within the same row.\n";

echo "\nFor initial stock row:\n";
echo "- Saldo Awal: 50 kg × 4 potong/kg = 200 potong ✅\n";
echo "- Saldo Akhir: should also be 50 kg × 4 potong/kg = 200 potong ❌ (currently 150)\n";

echo "\nThe issue is in the view logic where saldo_akhir_qty is calculated differently from saldo_awal_qty.\n";

echo "\nSOLUTION:\n";
echo "For initial stock transactions, saldo_akhir should equal saldo_awal since there's no other activity on that row.\n";
echo "The conversion rate used for saldo_akhir should match the conversion rate used for saldo_awal.\n";

echo "\nThis means:\n";
echo "- Initial stock row: Saldo Awal = Saldo Akhir = 200 potong\n";
echo "- Purchase row: adds 120 potong\n";
echo "- Production row: subtracts 160 potong\n";
echo "- Retur rows: subtract 26.4 potong total\n";
echo "- Final balance: 200 + 120 - 160 - 26.4 = 133.6 potong\n";

echo "\nThe view needs to use the same conversion rate for both saldo_awal and saldo_akhir in each row.\n";