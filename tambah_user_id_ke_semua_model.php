<?php
/**
 * TAMBAH KOLOM user_id KE SEMUA MODEL
 * ====================================
 * Script ini akan:
 * 1. Cek model yang belum punya user_id
 * 2. Generate migration untuk tambah kolom user_id
 * 3. Tambahkan global scope ke model
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$modelsWithoutUserId = [
    'KategoriProduk',
    'KategoriBahanPendukung',
    'JournalEntry',
    'ApSettlement',
    'SalesReturn',
    'BomJobCosting',
    'BomJobBahanPendukung',
    'BomJobBOP',
    'BomJobBTKL',
    'BomProses',
    'Bop',
    'BopLainnya',
    'KomponenBop',
];

echo "==============================================\n";
echo "CEK MODEL YANG BELUM PUNYA user_id\n";
echo "==============================================\n\n";

$pdo = DB::connection()->getPdo();
$needsMigration = [];
$alreadyHas = [];

foreach ($modelsWithoutUserId as $modelName) {
    $className = "App\\Models\\{$modelName}";
    
    if (!class_exists($className)) {
        echo "⚠️  {$modelName}: Class tidak ditemukan\n";
        continue;
    }
    
    try {
        $model = new $className;
        $table = $model->getTable();
        
        // Cek apakah tabel punya kolom user_id
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE 'user_id'");
        $hasUserId = $stmt->rowCount() > 0;
        
        if ($hasUserId) {
            echo "✅ {$modelName} ({$table}): Sudah punya user_id\n";
            $alreadyHas[] = ['model' => $modelName, 'table' => $table];
        } else {
            echo "❌ {$modelName} ({$table}): Belum punya user_id\n";
            $needsMigration[] = ['model' => $modelName, 'table' => $table];
        }
        
    } catch (Exception $e) {
        echo "⚠️  {$modelName}: Error - " . $e->getMessage() . "\n";
    }
}

echo "\n";
echo "==============================================\n";
echo "RINGKASAN\n";
echo "==============================================\n\n";
echo "✅ Sudah punya user_id: " . count($alreadyHas) . "\n";
echo "❌ Perlu tambah user_id: " . count($needsMigration) . "\n\n";

if (count($needsMigration) > 0) {
    echo "==============================================\n";
    echo "GENERATE MIGRATION\n";
    echo "==============================================\n\n";
    
    $timestamp = date('Y_m_d_His');
    $migrationContent = "<?php\n\n";
    $migrationContent .= "use Illuminate\\Database\\Migrations\\Migration;\n";
    $migrationContent .= "use Illuminate\\Database\\Schema\\Blueprint;\n";
    $migrationContent .= "use Illuminate\\Support\\Facades\\Schema;\n\n";
    $migrationContent .= "return new class extends Migration\n";
    $migrationContent .= "{\n";
    $migrationContent .= "    /**\n";
    $migrationContent .= "     * Run the migrations.\n";
    $migrationContent .= "     */\n";
    $migrationContent .= "    public function up(): void\n";
    $migrationContent .= "    {\n";
    
    foreach ($needsMigration as $item) {
        $migrationContent .= "        // Tambah user_id ke {$item['table']}\n";
        $migrationContent .= "        if (!Schema::hasColumn('{$item['table']}', 'user_id')) {\n";
        $migrationContent .= "            Schema::table('{$item['table']}', function (Blueprint \$table) {\n";
        $migrationContent .= "                \$table->unsignedBigInteger('user_id')->nullable()->after('id');\n";
        $migrationContent .= "                \$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');\n";
        $migrationContent .= "                \$table->index('user_id');\n";
        $migrationContent .= "            });\n";
        $migrationContent .= "        }\n\n";
    }
    
    $migrationContent .= "    }\n\n";
    $migrationContent .= "    /**\n";
    $migrationContent .= "     * Reverse the migrations.\n";
    $migrationContent .= "     */\n";
    $migrationContent .= "    public function down(): void\n";
    $migrationContent .= "    {\n";
    
    foreach ($needsMigration as $item) {
        $migrationContent .= "        if (Schema::hasColumn('{$item['table']}', 'user_id')) {\n";
        $migrationContent .= "            Schema::table('{$item['table']}', function (Blueprint \$table) {\n";
        $migrationContent .= "                \$table->dropForeign(['{$item['table']}_user_id_foreign']);\n";
        $migrationContent .= "                \$table->dropColumn('user_id');\n";
        $migrationContent .= "            });\n";
        $migrationContent .= "        }\n\n";
    }
    
    $migrationContent .= "    }\n";
    $migrationContent .= "};\n";
    
    $migrationFile = "database/migrations/{$timestamp}_add_user_id_to_all_tables.php";
    file_put_contents($migrationFile, $migrationContent);
    
    echo "✅ Migration file created: {$migrationFile}\n\n";
    echo "Jalankan migration dengan:\n";
    echo "  php artisan migrate\n\n";
}

// Generate script untuk tambah global scope
echo "==============================================\n";
echo "TAMBAH GLOBAL SCOPE KE SEMUA MODEL\n";
echo "==============================================\n\n";

$allModels = array_merge(
    array_column($alreadyHas, 'model'),
    array_column($needsMigration, 'model')
);

echo "Model yang perlu ditambahkan global scope:\n";
foreach ($allModels as $model) {
    echo "  - {$model}\n";
}

echo "\n";
echo "Setelah migration selesai, jalankan:\n";
echo "  php tambah_global_scope_ke_model_tersisa.php\n\n";

// Buat script untuk tambah global scope
$scriptContent = "<?php\n\n";
$scriptContent .= "\$modelsToFix = [\n";
foreach ($allModels as $model) {
    $scriptContent .= "    '{$model}',\n";
}
$scriptContent .= "];\n\n";
$scriptContent .= "// Jalankan script tambah_global_scope_otomatis.php dengan list ini\n";
$scriptContent .= "include 'tambah_global_scope_otomatis.php';\n";

file_put_contents('tambah_global_scope_ke_model_tersisa.php', $scriptContent);

echo "✅ Script helper created: tambah_global_scope_ke_model_tersisa.php\n\n";

echo "==============================================\n";
echo "LANGKAH SELANJUTNYA\n";
echo "==============================================\n\n";
echo "1. Jalankan migration:\n";
echo "   php artisan migrate\n\n";
echo "2. Tambah global scope ke model:\n";
echo "   php tambah_global_scope_otomatis.php\n\n";
echo "3. Test dengan:\n";
echo "   php audit_dan_perbaiki_isolasi_data.php\n\n";
