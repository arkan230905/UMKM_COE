<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Updating laporan stok logic to use real-time stock data...\n\n";

// Read the current LaporanController
$controllerFile = 'app/Http/Controllers/LaporanController.php';
$backupFile = 'app/Http/Controllers/LaporanController_backup_' . date('YmdHis') . '.php';

if (!file_exists($controllerFile)) {
    echo "LaporanController not found!\n";
    exit;
}

// Create backup
copy($controllerFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Read current content
$content = file_get_contents($controllerFile);

// Find the section where stock is calculated from master data
$oldLogic = '// Jika tidak ada filter tanggal, gunakan stok dari master table
                if (!$from && !$to) {
                    if ($tipe == \'material\') {
                        foreach ($materials as $m) {
                            $saldoPerItem[$m->id] = (float)($m->stok ?? 0);
                        }
                    } elseif ($tipe == \'product\') {
                        foreach ($products as $p) {
                            $saldoPerItem[$p->id] = (float)($p->stok ?? 0);
                        }
                    } elseif ($tipe == \'bahan_pendukung\') {
                        foreach ($bahanPendukungs as $bp) {
                            $saldoPerItem[$bp->id] = (float)($bp->stok ?? 0);
                        }
                    }
                }';

$newLogic = '// Jika tidak ada filter tanggal, gunakan stok dari stock movements (real-time)
                if (!$from && !$to) {
                    // Calculate stock from movements for real-time accuracy
                    if ($tipe == \'material\') {
                        foreach ($materials as $m) {
                            $stockIn = StockMovement::where(\'item_type\', \'bahan_baku\')
                                ->where(\'item_id\', $m->id)
                                ->where(\'direction\', \'in\')
                                ->sum(\'qty\');
                            $stockOut = StockMovement::where(\'item_type\', \'bahan_baku\')
                                ->where(\'item_id\', $m->id)
                                ->where(\'direction\', \'out\')
                                ->sum(\'qty\');
                            $saldoPerItem[$m->id] = $stockIn - $stockOut;
                        }
                    } elseif ($tipe == \'product\') {
                        foreach ($products as $p) {
                            $stockIn = StockMovement::where(\'item_type\', \'product\')
                                ->where(\'item_id\', $p->id)
                                ->where(\'direction\', \'in\')
                                ->sum(\'qty\');
                            $stockOut = StockMovement::where(\'item_type\', \'product\')
                                ->where(\'item_id\', $p->id)
                                ->where(\'direction\', \'out\')
                                ->sum(\'qty\');
                            $saldoPerItem[$p->id] = $stockIn - $stockOut;
                            
                            // Also update the master data for consistency
                            $p->stok = $stockIn - $stockOut;
                            $p->save();
                        }
                    } elseif ($tipe == \'bahan_pendukung\') {
                        foreach ($bahanPendukungs as $bp) {
                            $stockIn = StockMovement::where(\'item_type\', \'bahan_pendukung\')
                                ->where(\'item_id\', $bp->id)
                                ->where(\'direction\', \'in\')
                                ->sum(\'qty\');
                            $stockOut = StockMovement::where(\'item_type\', \'bahan_pendukung\')
                                ->where(\'item_id\', $bp->id)
                                ->where(\'direction\', \'out\')
                                ->sum(\'qty\');
                            $saldoPerItem[$bp->id] = $stockIn - $stockOut;
                        }
                    }
                }';

// Replace the logic
if (strpos($content, $oldLogic) !== false) {
    $content = str_replace($oldLogic, $newLogic, $content);
    
    // Write back to file
    file_put_contents($controllerFile, $content);
    echo "✓ Updated laporan stok logic to use real-time stock data\n";
} else {
    echo "✗ Could not find the exact logic to replace\n";
    echo "Looking for alternative patterns...\n";
    
    // Try to find and replace the specific product section
    $productOldLogic = 'foreach ($products as $p) {
                            $saldoPerItem[$p->id] = (float)($p->stok ?? 0);
                        }';
    
    $productNewLogic = 'foreach ($products as $p) {
                            $stockIn = StockMovement::where(\'item_type\', \'product\')
                                ->where(\'item_id\', $p->id)
                                ->where(\'direction\', \'in\')
                                ->sum(\'qty\');
                            $stockOut = StockMovement::where(\'item_type\', \'product\')
                                ->where(\'item_id\', $p->id)
                                ->where(\'direction\', \'out\')
                                ->sum(\'qty\');
                            $saldoPerItem[$p->id] = $stockIn - $stockOut;
                            
                            // Also update the master data for consistency
                            $p->stok = $stockIn - $stockOut;
                            $p->save();
                        }';
    
    if (strpos($content, $productOldLogic) !== false) {
        $content = str_replace($productOldLogic, $productNewLogic, $content);
        file_put_contents($controllerFile, $content);
        echo "✓ Updated product stock logic in laporan stok\n";
    } else {
        echo "✗ Could not find product logic to replace\n";
    }
}

echo "\nDone.\n";
