<?php
/**
 * FINAL VERIFICATION SCRIPT
 * 
 * This script verifies that the fresh database setup is complete and working
 * Run: php final_verification.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FINAL VERIFICATION ===\n\n";

$allGood = true;

try {
    // 1. Check database connection
    echo "1. Checking database connection...\n";
    try {
        \DB::connection()->getPdo();
        $dbName = \DB::connection()->getDatabaseName();
        echo "   ✅ Connected to database: $dbName\n\n";
    } catch (\Exception $e) {
        echo "   ❌ Database connection failed: " . $e->getMessage() . "\n\n";
        $allGood = false;
    }
    
    // 2. Check critical tables
    echo "2. Checking critical tables...\n";
    $tables = ['users', 'coas', 'satuans', 'jabatans', 'produks', 'bahan_bakus', 'jurnal_umum', 'biaya_bahan_baku'];
    foreach ($tables as $table) {
        $exists = \Schema::hasTable($table);
        if ($exists) {
            $count = \DB::table($table)->count();
            echo "   ✅ $table (records: $count)\n";
        } else {
            echo "   ❌ $table - TABLE MISSING!\n";
            $allGood = false;
        }
    }
    echo "\n";
    
    // 3. Check data counts
    echo "3. Checking seeded data...\n";
    $coaCount = \App\Models\Coa::count();
    $satuanCount = \App\Models\Satuan::count();
    $jabatanCount = \App\Models\Jabatan::count();
    $userCount = \App\Models\User::count();
    
    echo "   COA: $coaCount " . ($coaCount > 0 ? "✅" : "❌") . "\n";
    echo "   Satuan: $satuanCount " . ($satuanCount > 0 ? "✅" : "❌") . "\n";
    echo "   Jabatan: $jabatanCount " . ($jabatanCount > 0 ? "✅" : "❌") . "\n";
    echo "   Users: $userCount " . ($userCount > 0 ? "✅" : "❌") . "\n\n";
    
    if ($coaCount == 0 || $satuanCount == 0 || $jabatanCount == 0 || $userCount == 0) {
        $allGood = false;
    }
    
    // 4. Check user details
    echo "4. Checking user accounts...\n";
    $users = \App\Models\User::all(['id', 'name', 'email']);
    foreach ($users as $user) {
        echo "   ✅ User ID {$user->id}: {$user->name} ({$user->email})\n";
    }
    echo "\n";
    
    // 5. Check required COA accounts for production
    echo "5. Checking required COA accounts for production...\n";
    $requiredCoas = [
        '1171' => 'Pers. Barang Dalam Proses - BBB',
        '1172' => 'Pers. Barang Dalam Proses - BTKL',
        '1173' => 'Pers. Barang Dalam Proses - BOP',
        '211' => 'Hutang Gaji',
        '210' => 'Hutang Usaha'
    ];
    
    foreach ($requiredCoas as $code => $name) {
        $coa = \App\Models\Coa::where('kode_akun', $code)->first();
        if ($coa) {
            echo "   ✅ $code: {$coa->nama_akun}\n";
        } else {
            echo "   ❌ $code: $name - MISSING!\n";
            $allGood = false;
        }
    }
    echo "\n";
    
    // 6. Check BiayaBahanController
    echo "6. Checking BiayaBahanController...\n";
    if (class_exists('App\Http\Controllers\BiayaBahanController')) {
        echo "   ✅ BiayaBahanController exists\n";
        
        // Check if it has multi-tenant filtering
        $reflection = new \ReflectionClass('App\Http\Controllers\BiayaBahanController');
        $indexMethod = $reflection->getMethod('index');
        $source = file_get_contents($indexMethod->getFileName());
        
        if (strpos($source, 'auth()->id()') !== false) {
            echo "   ✅ Multi-tenant filtering implemented\n";
        } else {
            echo "   ⚠️  Multi-tenant filtering may not be implemented\n";
        }
    } else {
        echo "   ❌ BiayaBahanController not found\n";
        $allGood = false;
    }
    echo "\n";
    
    // 7. Final status
    echo "=== VERIFICATION RESULT ===\n\n";
    
    if ($allGood) {
        echo "✅ ALL CHECKS PASSED!\n\n";
        echo "Your database is ready to use.\n\n";
        echo "=== NEXT STEPS ===\n\n";
        echo "1. Start server: php artisan serve\n";
        echo "2. Open browser: http://127.0.0.1:8000/login\n";
        echo "3. Login with:\n";
        echo "   Email: admin@umkm.test\n";
        echo "   Password: password123\n\n";
        echo "4. Test pages:\n";
        echo "   - Dashboard: http://127.0.0.1:8000/dashboard\n";
        echo "   - Biaya Bahan: http://127.0.0.1:8000/master-data/biaya-bahan\n";
        echo "   - BTKL: http://127.0.0.1:8000/master-data/btkl\n";
        echo "   - Neraca Saldo: http://127.0.0.1:8000/akuntansi/neraca-saldo\n\n";
        echo "5. When ready, commit and push to GitHub:\n";
        echo "   git add .\n";
        echo "   git commit -m \"Fresh database setup completed\"\n";
        echo "   git push origin main\n\n";
        echo "✅ READY TO DEPLOY!\n";
    } else {
        echo "❌ SOME CHECKS FAILED!\n\n";
        echo "Please review the errors above and fix them.\n\n";
        echo "Common fixes:\n";
        echo "- Run migrations: php artisan migrate\n";
        echo "- Seed COA: php artisan db:seed --class=JasukeCoaSeeder\n";
        echo "- Seed Satuan: php artisan db:seed --class=SatuanSeeder\n";
        echo "- Seed Jabatan: php artisan db:seed --class=JabatanSeeder\n";
        echo "- Create user: php create_first_user.php\n\n";
    }
    
} catch (\Exception $e) {
    echo "❌ VERIFICATION ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
