<?php
/**
 * SEED ESSENTIAL DATA
 * 
 * This script seeds only the essential data needed for the system to work:
 * - COA (Chart of Accounts) using JasukeCoaSeeder
 * - Satuan (Units)
 * - Jabatan (Job Positions)
 * 
 * Run: php seed_essential_data.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SEEDING ESSENTIAL DATA ===\n\n";

try {
    // 1. Seed COA using JasukeCoaSeeder
    echo "1. Seeding COA (Chart of Accounts)...\n";
    $jasukeSeeder = new \Database\Seeders\JasukeCoaSeeder();
    $jasukeSeeder->run();
    echo "✅ COA seeded successfully\n\n";
    
    // 2. Seed Satuan
    echo "2. Seeding Satuan (Units)...\n";
    $satuanSeeder = new \Database\Seeders\SatuanSeeder();
    $satuanSeeder->run();
    echo "✅ Satuan seeded successfully\n\n";
    
    // 3. Seed Jabatan
    echo "3. Seeding Jabatan (Job Positions)...\n";
    $jabatanSeeder = new \Database\Seeders\JabatanSeeder();
    $jabatanSeeder->run();
    echo "✅ Jabatan seeded successfully\n\n";
    
    // 4. Seed Pegawai (if needed)
    echo "4. Seeding Pegawai (Employees)...\n";
    try {
        $pegawaiSeeder = new \Database\Seeders\PegawaiSeeder();
        $pegawaiSeeder->run();
        echo "✅ Pegawai seeded successfully\n\n";
    } catch (\Exception $e) {
        echo "⚠️  Pegawai seeder skipped: " . $e->getMessage() . "\n\n";
    }
    
    // 5. Seed Initial Setup
    echo "5. Seeding Initial Setup Data...\n";
    try {
        $initialSeeder = new \Database\Seeders\InitialSetupSeeder();
        $initialSeeder->run();
        echo "✅ Initial setup seeded successfully\n\n";
    } catch (\Exception $e) {
        echo "⚠️  Initial setup seeder skipped: " . $e->getMessage() . "\n\n";
    }
    
    echo "=== SEEDING COMPLETED! ===\n\n";
    
    // Verify COA count
    $coaCount = \App\Models\Coa::count();
    echo "Total COA accounts: $coaCount\n";
    
    // Verify Satuan count
    $satuanCount = \App\Models\Satuan::count();
    echo "Total Satuan: $satuanCount\n";
    
    // Verify Jabatan count
    $jabatanCount = \App\Models\Jabatan::count();
    echo "Total Jabatan: $jabatanCount\n\n";
    
    echo "✅ All essential data seeded successfully!\n";
    echo "You can now use the system.\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
