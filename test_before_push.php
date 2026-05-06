<?php
/**
 * TEST BEFORE PUSH
 * 
 * Script ini melakukan automated testing sebelum push ke GitHub
 * Run: php test_before_push.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING BEFORE PUSH TO GITHUB ===\n\n";

$allPassed = true;
$warnings = [];

// Test 1: Database Connection
echo "Test 1: Database Connection...\n";
try {
    \DB::connection()->getPdo();
    $dbName = \DB::connection()->getDatabaseName();
    echo "   ✅ PASS: Connected to database: $dbName\n\n";
} catch (\Exception $e) {
    echo "   ❌ FAIL: " . $e->getMessage() . "\n\n";
    $allPassed = false;
}

// Test 2: Critical Tables Exist
echo "Test 2: Critical Tables Exist...\n";
$criticalTables = ['users', 'coas', 'satuans', 'jabatans', 'produks', 'bahan_bakus', 
                   'jurnal_umum', 'biaya_bahan_baku', 'produksis', 'komponen_bops'];
$missingTables = [];
foreach ($criticalTables as $table) {
    if (!\Schema::hasTable($table)) {
        $missingTables[] = $table;
        echo "   ❌ FAIL: Table '$table' missing\n";
        $allPassed = false;
    }
}
if (empty($missingTables)) {
    echo "   ✅ PASS: All critical tables exist\n\n";
} else {
    echo "\n";
}

// Test 3: Required Data Seeded
echo "Test 3: Required Data Seeded...\n";
$coaCount = \App\Models\Coa::count();
$satuanCount = \App\Models\Satuan::count();
$jabatanCount = \App\Models\Jabatan::count();
$userCount = \App\Models\User::count();

$dataOK = true;
if ($coaCount < 100) {
    echo "   ❌ FAIL: COA count too low ($coaCount). Expected at least 100\n";
    $allPassed = false;
    $dataOK = false;
}
if ($satuanCount < 10) {
    echo "   ❌ FAIL: Satuan count too low ($satuanCount). Expected at least 10\n";
    $allPassed = false;
    $dataOK = false;
}
if ($jabatanCount < 5) {
    echo "   ❌ FAIL: Jabatan count too low ($jabatanCount). Expected at least 5\n";
    $allPassed = false;
    $dataOK = false;
}
if ($userCount < 1) {
    echo "   ❌ FAIL: No users found. Need at least 1 user\n";
    $allPassed = false;
    $dataOK = false;
}

if ($dataOK) {
    echo "   ✅ PASS: COA: $coaCount, Satuan: $satuanCount, Jabatan: $jabatanCount, Users: $userCount\n\n";
}

// Test 4: WIP Accounts Exist
echo "Test 4: WIP Accounts for Production...\n";
$wipAccounts = ['1171', '1172', '1173', '210', '211'];
$missingWIP = [];
foreach ($wipAccounts as $code) {
    $exists = \App\Models\Coa::where('kode_akun', $code)->exists();
    if (!$exists) {
        $missingWIP[] = $code;
        echo "   ❌ FAIL: COA $code missing\n";
        $allPassed = false;
    }
}
if (empty($missingWIP)) {
    echo "   ✅ PASS: All WIP accounts exist (1171, 1172, 1173, 210, 211)\n\n";
} else {
    echo "\n";
}

// Test 5: Multi-Tenant Implementation
echo "Test 5: Multi-Tenant Implementation...\n";
$controllers = [
    'App\Http\Controllers\BiayaBahanController',
    'App\Http\Controllers\DashboardController',
    'App\Http\Controllers\LaporanKasBankController',
    'App\Http\Controllers\AkuntansiController',
];

$multiTenantOK = true;
foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        $reflection = new \ReflectionClass($controller);
        $source = file_get_contents($reflection->getFileName());
        
        if (strpos($source, 'auth()->id()') !== false || strpos($source, 'user_id') !== false) {
            // OK
        } else {
            echo "   ⚠️  WARNING: $controller may not have multi-tenant filtering\n";
            $warnings[] = "$controller may not have multi-tenant filtering";
            $multiTenantOK = false;
        }
    }
}
if ($multiTenantOK) {
    echo "   ✅ PASS: Multi-tenant filtering implemented\n\n";
} else {
    echo "\n";
}

// Test 6: Migration Status
echo "Test 6: Migration Status...\n";
try {
    $migrations = \DB::table('migrations')->count();
    if ($migrations > 400) {
        echo "   ✅ PASS: $migrations migrations recorded\n\n";
    } else {
        echo "   ⚠️  WARNING: Only $migrations migrations. Expected 400+\n\n";
        $warnings[] = "Migration count lower than expected";
    }
} catch (\Exception $e) {
    echo "   ❌ FAIL: Cannot check migrations: " . $e->getMessage() . "\n\n";
    $allPassed = false;
}

// Test 7: .env.example Exists
echo "Test 7: .env.example File...\n";
if (file_exists('.env.example')) {
    echo "   ✅ PASS: .env.example exists\n\n";
} else {
    echo "   ❌ FAIL: .env.example missing\n\n";
    $allPassed = false;
}

// Test 8: composer.json and composer.lock
echo "Test 8: Composer Files...\n";
$composerOK = true;
if (!file_exists('composer.json')) {
    echo "   ❌ FAIL: composer.json missing\n";
    $allPassed = false;
    $composerOK = false;
}
if (!file_exists('composer.lock')) {
    echo "   ❌ FAIL: composer.lock missing\n";
    $allPassed = false;
    $composerOK = false;
}
if ($composerOK) {
    echo "   ✅ PASS: composer.json and composer.lock exist\n\n";
}

// Test 9: Storage Permissions
echo "Test 9: Storage Directory...\n";
if (is_writable('storage')) {
    echo "   ✅ PASS: storage/ is writable\n\n";
} else {
    echo "   ⚠️  WARNING: storage/ may not be writable\n\n";
    $warnings[] = "storage/ directory may not be writable";
}

// Test 10: Key Files Not in Git
echo "Test 10: Sensitive Files Check...\n";
$sensitiveFiles = ['.env'];
$foundSensitive = [];
foreach ($sensitiveFiles as $file) {
    if (file_exists($file)) {
        // Check if in .gitignore
        $gitignore = file_get_contents('.gitignore');
        if (strpos($gitignore, $file) === false) {
            $foundSensitive[] = $file;
            echo "   ⚠️  WARNING: $file exists but may not be in .gitignore\n";
            $warnings[] = "$file should be in .gitignore";
        }
    }
}
if (empty($foundSensitive)) {
    echo "   ✅ PASS: No sensitive files will be committed\n\n";
} else {
    echo "\n";
}

// Test 11: Required Seeders Exist
echo "Test 11: Required Seeders...\n";
$seeders = [
    'database/seeders/JasukeCoaSeeder.php',
    'database/seeders/SatuanSeeder.php',
    'database/seeders/JabatanSeeder.php',
    'database/seeders/RequiredProductionCoasSeeder.php',
];
$missingSeeders = [];
foreach ($seeders as $seeder) {
    if (!file_exists($seeder)) {
        $missingSeeders[] = $seeder;
        echo "   ❌ FAIL: $seeder missing\n";
        $allPassed = false;
    }
}
if (empty($missingSeeders)) {
    echo "   ✅ PASS: All required seeders exist\n\n";
} else {
    echo "\n";
}

// Test 12: Helper Scripts Exist
echo "Test 12: Helper Scripts...\n";
$scripts = [
    'verify_database_structure.php',
    'create_first_user.php',
    'final_verification.php',
];
$missingScripts = [];
foreach ($scripts as $script) {
    if (!file_exists($script)) {
        $missingScripts[] = $script;
        echo "   ⚠️  WARNING: $script missing\n";
        $warnings[] = "$script missing";
    }
}
if (empty($missingScripts)) {
    echo "   ✅ PASS: All helper scripts exist\n\n";
} else {
    echo "\n";
}

// SUMMARY
echo "=== TEST SUMMARY ===\n\n";

if ($allPassed && empty($warnings)) {
    echo "✅ ALL TESTS PASSED!\n\n";
    echo "Sistem Anda SIAP untuk di-push ke GitHub.\n\n";
    echo "=== LANGKAH SELANJUTNYA ===\n\n";
    echo "1. MANUAL TESTING (PENTING!):\n";
    echo "   - Jalankan: php artisan serve\n";
    echo "   - Buka browser: http://127.0.0.1:8000/login\n";
    echo "   - Login dengan: admin@umkm.test / password123\n";
    echo "   - Test halaman: Dashboard, Biaya Bahan, BTKL, Neraca Saldo\n\n";
    echo "2. JIKA MANUAL TEST OK, PUSH KE GITHUB:\n";
    echo "   git add .\n";
    echo "   git commit -m \"Fix: Complete database setup and multi-tenant fixes\"\n";
    echo "   git push origin main\n\n";
    echo "3. MONITOR JENKINS:\n";
    echo "   - Tunggu Jenkins selesai build\n";
    echo "   - Check logs jika ada error\n\n";
    echo "4. VERIFY DI VPS:\n";
    echo "   - Login ke web production\n";
    echo "   - Test halaman penting\n\n";
    echo "✅ READY TO DEPLOY!\n";
    exit(0);
} else {
    echo "⚠️  TESTS COMPLETED WITH ISSUES\n\n";
    
    if (!$allPassed) {
        echo "❌ CRITICAL ISSUES FOUND:\n";
        echo "   Beberapa test GAGAL. Review output di atas.\n";
        echo "   JANGAN push ke GitHub sampai semua test PASS.\n\n";
    }
    
    if (!empty($warnings)) {
        echo "⚠️  WARNINGS (" . count($warnings) . "):\n";
        foreach ($warnings as $warning) {
            echo "   - $warning\n";
        }
        echo "\n";
        echo "   Warnings tidak menghalangi deployment, tapi sebaiknya di-review.\n\n";
    }
    
    if (!$allPassed) {
        echo "=== RECOMMENDED ACTIONS ===\n\n";
        
        if (!empty($missingTables)) {
            echo "1. Run migrations:\n";
            echo "   php artisan migrate\n\n";
        }
        
        if ($coaCount < 100 || $satuanCount < 10 || $jabatanCount < 5) {
            echo "2. Run seeders:\n";
            echo "   php artisan db:seed --class=JasukeCoaSeeder\n";
            echo "   php artisan db:seed --class=SatuanSeeder\n";
            echo "   php artisan db:seed --class=JabatanSeeder\n\n";
        }
        
        if (!empty($missingWIP)) {
            echo "3. Add WIP accounts:\n";
            echo "   php artisan db:seed --class=RequiredProductionCoasSeeder\n\n";
        }
        
        if ($userCount < 1) {
            echo "4. Create user:\n";
            echo "   php create_first_user.php\n\n";
        }
        
        echo "5. Run this test again:\n";
        echo "   php test_before_push.php\n\n";
        
        exit(1);
    } else {
        echo "✅ Semua critical tests PASS, hanya ada warnings.\n";
        echo "   Anda bisa melanjutkan ke manual testing.\n\n";
        exit(0);
    }
}
