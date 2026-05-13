<?php
/**
 * COMPLETE SETUP SCRIPT
 * 
 * This script completes the database setup by adding missing data
 * Run: php complete_setup.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPLETING DATABASE SETUP ===\n\n";

try {
    // Check current state
    echo "Current database state:\n";
    $coaCount = \App\Models\Coa::count();
    $satuanCount = \App\Models\Satuan::count();
    $jabatanCount = \App\Models\Jabatan::count();
    $userCount = \App\Models\User::count();
    
    echo "  COA: $coaCount\n";
    echo "  Satuan: $satuanCount\n";
    echo "  Jabatan: $jabatanCount\n";
    echo "  Users: $userCount\n\n";
    
    // Seed Satuan if needed
    if ($satuanCount == 0) {
        echo "Seeding Satuan (Units)...\n";
        $satuanSeeder = new \Database\Seeders\SatuanSeeder();
        $satuanSeeder->run();
        $satuanCount = \App\Models\Satuan::count();
        echo "✅ Satuan seeded: $satuanCount units\n\n";
    } else {
        echo "✅ Satuan already exists\n\n";
    }
    
    // Seed Jabatan if needed
    if ($jabatanCount == 0) {
        echo "Seeding Jabatan (Job Positions)...\n";
        $jabatanSeeder = new \Database\Seeders\JabatanSeeder();
        $jabatanSeeder->run();
        $jabatanCount = \App\Models\Jabatan::count();
        echo "✅ Jabatan seeded: $jabatanCount positions\n\n";
    } else {
        echo "✅ Jabatan already exists\n\n";
    }
    
    // Seed Pegawai if needed
    $pegawaiCount = \App\Models\Pegawai::count();
    if ($pegawaiCount == 0) {
        echo "Seeding Pegawai (Employees)...\n";
        try {
            $pegawaiSeeder = new \Database\Seeders\PegawaiSeeder();
            $pegawaiSeeder->run();
            $pegawaiCount = \App\Models\Pegawai::count();
            echo "✅ Pegawai seeded: $pegawaiCount employees\n\n";
        } catch (\Exception $e) {
            echo "⚠️  Pegawai seeder skipped: " . $e->getMessage() . "\n\n";
        }
    } else {
        echo "✅ Pegawai already exists: $pegawaiCount employees\n\n";
    }
    
    echo "=== SETUP COMPLETED! ===\n\n";
    
    // Final state
    echo "Final database state:\n";
    echo "  COA: " . \App\Models\Coa::count() . "\n";
    echo "  Satuan: " . \App\Models\Satuan::count() . "\n";
    echo "  Jabatan: " . \App\Models\Jabatan::count() . "\n";
    echo "  Pegawai: " . \App\Models\Pegawai::count() . "\n";
    echo "  Users: " . \App\Models\User::count() . "\n\n";
    
    // Show login credentials
    echo "=== LOGIN CREDENTIALS ===\n\n";
    $users = \App\Models\User::all(['id', 'name', 'email']);
    foreach ($users as $user) {
        echo "User ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Email: {$user->email}\n";
        echo "Password: password123\n\n";
    }
    
    echo "=== NEXT STEPS ===\n\n";
    echo "1. Start your server:\n";
    echo "   php artisan serve\n\n";
    echo "2. Open browser:\n";
    echo "   http://127.0.0.1:8000/login\n\n";
    echo "3. Login with the credentials above\n\n";
    echo "4. Test these pages:\n";
    echo "   - Dashboard: http://127.0.0.1:8000/dashboard\n";
    echo "   - Biaya Bahan: http://127.0.0.1:8000/master-data/biaya-bahan\n";
    echo "   - BTKL: http://127.0.0.1:8000/master-data/btkl\n";
    echo "   - Neraca Saldo: http://127.0.0.1:8000/akuntansi/neraca-saldo\n\n";
    
    echo "✅ READY TO USE!\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
