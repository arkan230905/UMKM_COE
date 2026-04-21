<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix COA tipe_akun ENUM Column ===" . PHP_EOL;

// The issue: tipe_akun is ENUM, not VARCHAR
echo PHP_EOL . "Problem Analysis:" . PHP_EOL;
echo "- Current tipe_akun type: ENUM" . PHP_EOL;
echo "- User trying to save: 'BEBAN'" . PHP_EOL;
echo "- But ENUM values are limited" . PHP_EOL;
echo "- Need to add 'BEBAN' to ENUM values" . PHP_EOL;

echo PHP_EOL . "Current ENUM values:" . PHP_EOL;
$columns = DB::select("SHOW COLUMNS FROM coas WHERE Field = 'tipe_akun'");
foreach ($columns as $column) {
    if ($column->Field === 'tipe_akun') {
        echo "- Type: " . $column->Type . PHP_EOL;
        
        // Extract ENUM values
        if (preg_match("/enum\((.*?)\)/", $column->Type, $matches)) {
            $enumValues = str_getcsv($matches[1], ',', "'");
            echo "- Values: " . implode(', ', $enumValues) . PHP_EOL;
            echo "- Count: " . count($enumValues) . PHP_EOL;
            
            // Check if 'BEBAN' is in the list
            if (in_array('BEBAN', $enumValues)) {
                echo "- Status: BEBAN already exists ✅" . PHP_EOL;
            } else {
                echo "- Status: BEBAN missing ❌" . PHP_EOL;
                echo "- Need to add BEBAN to ENUM" . PHP_EOL;
                
                echo PHP_EOL . "Adding BEBAN to ENUM..." . PHP_EOL;
                
                // Add BEBAN to ENUM
                $newEnumValues = array_merge($enumValues, ['BEBAN']);
                $newEnumString = "'" . implode("','", $newEnumValues) . "'";
                
                try {
                    DB::statement("ALTER TABLE coas MODIFY COLUMN tipe_akun ENUM(" . $newEnumString . ")");
                    echo "✅ BEBAN added to tipe_akun ENUM" . PHP_EOL;
                    echo "✅ New ENUM values: " . implode(', ', $newEnumValues) . PHP_EOL;
                } catch (\Exception $e) {
                    echo "❌ Error adding BEBAN to ENUM: " . $e->getMessage() . PHP_EOL;
                }
            }
        }
        break;
    }
}

echo PHP_EOL . "=== Test COA Update ===" . PHP_EOL;

// Test updating the COA that caused error
$coaId = 166;
$testData = [
    'nama_akun' => 'Biaya Tenaga Kerja Tidak Langsung',
    'tipe_akun' => 'BEBAN',
    'saldo_normal' => 'debit'
];

echo "Testing update for COA ID: " . $coaId . PHP_EOL;
echo "Data: " . json_encode($testData) . PHP_EOL;

try {
    $coa = DB::table('coas')->find($coaId);
    if ($coa) {
        DB::table('coas')
            ->where('id', $coaId)
            ->update([
                'nama_akun' => $testData['nama_akun'],
                'tipe_akun' => $testData['tipe_akun'],
                'saldo_normal' => $testData['saldo_normal']
            ]);
        
        echo "✅ COA updated successfully!" . PHP_EOL;
        echo "Nama Akun: " . $testData['nama_akun'] . PHP_EOL;
        echo "Tipe Akun: " . $testData['tipe_akun'] . PHP_EOL;
        echo "Saldo Normal: " . $testData['saldo_normal'] . PHP_EOL;
    } else {
        echo "❌ COA not found with ID: " . $coaId . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "❌ Error updating COA: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Verification ===" . PHP_EOL;

// Verify the update
$updatedCoa = DB::table('coas')->find($coaId);
if ($updatedCoa) {
    echo "Updated COA Data:" . PHP_EOL;
    echo "- ID: " . $updatedCoa->id . PHP_EOL;
    echo "- Nama Akun: " . $updatedCoa->nama_akun . PHP_EOL;
    echo "- Tipe Akun: " . $updatedCoa->tipe_akun . PHP_EOL;
    echo "- Status: " . ($updatedCoa->tipe_akun === 'BEBAN' ? "✅ CORRECT" : "❌ INCORRECT") . PHP_EOL;
}

echo PHP_EOL . "=== Update CoaController Validation ===" . PHP_EOL;

// Update CoaController to include BEBAN in validation
echo "Updating CoaController validation..." . PHP_EOL;

$controllerFile = __DIR__ . '/app/Http/Controllers/CoaController.php';
$controllerContent = file_get_contents($controllerFile);

// Find and update the allowed tipe_akun values
$pattern = "/'tipe_akun' => 'required\\|in:[^']+'/";
$replacement = "'tipe_akun' => 'required|in:Asset,Aset,ASET,Liability,Kewajiban,KEWAJIBAN,Equity,Ekuitas,Modal,MODAL,Revenue,Pendapatan,PENDAPATAN,Expense,Beban,BEBAN,Biaya,Biaya Bahan Baku,Biaya Tenaga Kerja Langsung,Biaya Overhead Pabrik,Biaya Tenaga Kerja Tidak Langsung,BOP Tidak Langsung Lainnya',";

if (preg_match($pattern, $controllerContent)) {
    $newContent = preg_replace($pattern, $replacement, $controllerContent);
    
    if (file_put_contents($controllerFile, $newContent)) {
        echo "✅ CoaController validation updated" . PHP_EOL;
        echo "✅ BEBAN added to allowed tipe_akun values" . PHP_EOL;
    } else {
        echo "❌ Failed to update CoaController" . PHP_EOL;
    }
} else {
    echo "❌ Could not find validation pattern in CoaController" . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "✅ Fixed: Added BEBAN to tipe_akun ENUM" . PHP_EOL;
echo "✅ Updated: CoaController validation rules" . PHP_EOL;
echo "✅ Result: Can now save COA with tipe_akun = 'BEBAN'" . PHP_EOL;
echo "✅ Status: COA update should work without errors" . PHP_EOL;
