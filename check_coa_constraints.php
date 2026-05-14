<?php
/**
 * CHECK COA CONSTRAINTS
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECK COA CONSTRAINTS ===\n\n";

$indexes = DB::select("SHOW INDEX FROM coas");

echo "All indexes on 'coas' table:\n";
foreach ($indexes as $index) {
    if ($index->Key_name !== 'PRIMARY') {
        echo "  - {$index->Key_name} on column '{$index->Column_name}' (Non_unique: {$index->Non_unique})\n";
    }
}

echo "\n";

// Check specifically for unique constraints
$uniqueIndexes = DB::select("SHOW INDEX FROM coas WHERE Non_unique = 0 AND Key_name != 'PRIMARY'");

echo "Unique constraints:\n";
if (empty($uniqueIndexes)) {
    echo "  (No unique constraints found)\n";
} else {
    foreach ($uniqueIndexes as $index) {
        echo "  ✅ {$index->Key_name} on column '{$index->Column_name}'\n";
    }
}

echo "\n";

// Test: Try to insert duplicate kode_akun for different users
echo "=== TEST: Insert duplicate kode_akun for different users ===\n\n";

try {
    // User 1 already has COA with kode_akun = '11'
    $user1Coa = DB::table('coas')->where('user_id', 1)->where('kode_akun', '11')->first();
    
    if ($user1Coa) {
        echo "✅ User 1 has COA with kode_akun = '11'\n";
    } else {
        echo "⚠️  User 1 does NOT have COA with kode_akun = '11'\n";
    }
    
    // Try to insert for User 2 (should succeed with composite unique)
    echo "\nTrying to insert COA with kode_akun = '11' for User 2...\n";
    
    $user2Exists = DB::table('coas')->where('user_id', 2)->where('kode_akun', '11')->exists();
    
    if ($user2Exists) {
        echo "✅ User 2 already has COA with kode_akun = '11'\n";
    } else {
        // Try to insert
        DB::table('coas')->insert([
            'user_id' => 2,
            'kode_akun' => '11',
            'nama_akun' => 'Test Aset',
            'tipe_akun' => 'Aset',
            'kategori_akun' => 'Aset',
            'saldo_normal' => 'debit',
            'saldo_awal' => 0,
            'tanggal_saldo_awal' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "✅ SUCCESS: User 2 can have COA with kode_akun = '11' (different from User 1)\n";
        echo "   This is CORRECT for multi-tenant system!\n";
        
        // Clean up
        DB::table('coas')->where('user_id', 2)->where('kode_akun', '11')->delete();
        echo "   (Test data cleaned up)\n";
    }
    
} catch (\Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    echo "   This means the unique constraint is NOT correct for multi-tenant!\n";
}

echo "\n=== RESULT ===\n\n";

$compositeUnique = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_user_id_unique'");

if (!empty($compositeUnique)) {
    echo "✅ CORRECT: Composite unique constraint (kode_akun + user_id) exists\n";
    echo "   Each user can have their own COA with the same kode_akun\n";
    echo "   Multi-tenant isolation is WORKING!\n";
} else {
    echo "❌ WRONG: Composite unique constraint does NOT exist\n";
    echo "   Users cannot have COA with the same kode_akun\n";
    echo "   Multi-tenant will FAIL on registration!\n";
}
