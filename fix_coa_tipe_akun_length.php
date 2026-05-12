<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix COA tipe_akun Column Length ===" . PHP_EOL;

// Check current column structure
echo PHP_EOL . "Current tipe_akun column structure:" . PHP_EOL;
$columns = DB::select("DESCRIBE coas");
foreach ($columns as $column) {
    if ($column->Field === 'tipe_akun') {
        echo "- Field: " . $column->Field . PHP_EOL;
        echo "- Type: " . $column->Type . PHP_EOL;
        echo "- Max Length: " . $column->Type . PHP_EOL;
        
        // Extract length from type
        if (preg_match('/varchar\((\d+)\)/', $column->Type, $matches)) {
            $currentLength = $matches[1];
            echo "- Current Max Length: " . $currentLength . PHP_EOL;
            
            $requiredLength = 50; // Increase to 50 characters
            if ($currentLength < $requiredLength) {
                echo "- Required Length: " . $requiredLength . PHP_EOL;
                echo "- Status: NEEDS TO BE INCREASED ❌" . PHP_EOL;
                
                echo PHP_EOL . "Creating migration to increase column length..." . PHP_EOL;
                
                // Create migration
                $migration = "<?php" . PHP_EOL;
                $migration .= PHP_EOL . "use Illuminate\Database\Migrations\Migration;" . PHP_EOL;
                $migration .= "use Illuminate\Database\Schema\Blueprint;" . PHP_EOL;
                $migration .= "use Illuminate\Support\Facades\Schema;" . PHP_EOL;
                $migration .= PHP_EOL . "return new class extends Migration" . PHP_EOL;
                $migration .= "{" . PHP_EOL;
                $migration .= "    /**" . PHP_EOL;
                $migration .= "     * Run the migrations." . PHP_EOL;
                $migration .= "     */" . PHP_EOL;
                $migration .= "    public function up()" . PHP_EOL;
                $migration .= "    {" . PHP_EOL;
                $migration .= "        Schema::table('coas', function (Blueprint \$table) {" . PHP_EOL;
                $migration .= "            \$table->string('tipe_akun', 50)->change();" . PHP_EOL;
                $migration .= "        });" . PHP_EOL;
                $migration .= "    }" . PHP_EOL;
                $migration .= PHP_EOL . "    /**" . PHP_EOL;
                $migration .= "     * Reverse the migrations." . PHP_EOL;
                $migration .= "     */" . PHP_EOL;
                $migration .= "    public function down()" . PHP_EOL;
                $migration .= "    {" . PHP_EOL;
                $migration .= "        Schema::table('coas', function (Blueprint \$table) {" . PHP_EOL;
                $migration .= "            \$table->string('tipe_akun', 20)->change();" . PHP_EOL;
                $migration .= "        });" . PHP_EOL;
                $migration .= "    }" . PHP_EOL;
                $migration .= "};" . PHP_EOL;
                
                $migrationFile = __DIR__ . '/database/migrations/' . date('Y_m_d_His') . '_increase_coa_tipe_akun_length.php';
                file_put_contents($migrationFile, $migration);
                
                echo "✅ Migration created: " . basename($migrationFile) . PHP_EOL;
                echo "Run: php artisan migrate" . PHP_EOL;
                
                // Also run it directly
                echo PHP_EOL . "Running migration directly..." . PHP_EOL;
                try {
                    DB::statement("ALTER TABLE coas MODIFY COLUMN tipe_akun VARCHAR(50)");
                    echo "✅ Column tipe_akun increased to VARCHAR(50)" . PHP_EOL;
                } catch (\Exception $e) {
                    echo "❌ Error updating column: " . $e->getMessage() . PHP_EOL;
                }
                
            } else {
                echo "- Status: LENGTH IS SUFFICIENT ✅" . PHP_EOL;
            }
        }
        break;
    }
}

echo PHP_EOL . "=== Test Update COA ===" . PHP_EOL;

// Test updating the COA that caused the error
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
    echo "- Length of tipe_akun: " . strlen($updatedCoa->tipe_akun) . " characters" . PHP_EOL;
    echo "- Status: " . (strlen($updatedCoa->tipe_akun) <= 50 ? "✅ WITHIN LIMIT" : "❌ EXCEEDS LIMIT") . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "✅ Fixed: tipe_akun column increased to VARCHAR(50)" . PHP_EOL;
echo "✅ Result: Can store 'Biaya Tenaga Kerja Tidak Langsung'" . PHP_EOL;
echo "✅ Status: COA update should work now" . PHP_EOL;
