<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "All COA Records in Database:\n";
echo "============================\n";

$allCoas = App\Models\Coa::withoutGlobalScopes()->get();
echo "Total COA records: " . count($allCoas) . "\n\n";

// Group by company_id
$grouped = $allCoas->groupBy('company_id');
foreach($grouped as $companyId => $coas) {
    echo "Company ID: " . ($companyId ?? 'NULL') . " - Count: " . count($coas) . "\n";
    if (count($coas) <= 5) {
        foreach($coas as $coa) {
            echo "  " . $coa->kode_akun . " - " . $coa->nama_akun . "\n";
        }
    } else {
        echo "  First 5 accounts:\n";
        foreach($coas->take(5) as $coa) {
            echo "    " . $coa->kode_akun . " - " . $coa->nama_akun . "\n";
        }
        echo "    ... and " . (count($coas) - 5) . " more\n";
    }
    echo "\n";
}
