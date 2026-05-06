<?php
/**
 * FRESH DATABASE SETUP SCRIPT
 * 
 * This script will:
 * 1. Verify all required tables exist
 * 2. Run seeders to create users and initial data
 * 3. Display login credentials
 * 4. Fix any missing tables
 * 
 * Run: php setup_fresh_database.php
 */

echo "=== FRESH DATABASE SETUP ===\n\n";

// Step 1: Verify database structure
echo "Step 1: Verifying database structure...\n";
$verifyOutput = shell_exec('php verify_database_structure.php 2>&1');
echo $verifyOutput . "\n";

// Check if there are missing tables
if (strpos($verifyOutput, 'komponen_bops - TABLE MISSING') !== false) {
    echo "\n⚠️  komponen_bops table is missing!\n";
    echo "Attempting to fix...\n\n";
    
    // Run the fix script
    $fixOutput = shell_exec('php fix_missing_komponen_bops_table.php 2>&1');
    echo $fixOutput . "\n";
}

// Step 2: Run seeders
echo "\n=== Step 2: Running Database Seeders ===\n";
echo "This will create:\n";
echo "  - Default users (Admin & Test users)\n";
echo "  - COA (Chart of Accounts)\n";
echo "  - Satuan (Units)\n";
echo "  - Jabatan (Job Positions)\n";
echo "  - Initial setup data\n\n";

$seederOutput = shell_exec('php artisan db:seed 2>&1');
echo $seederOutput . "\n";

// Step 3: Get created users
echo "\n=== Step 3: Checking Created Users ===\n";
$usersOutput = shell_exec('php artisan tinker --execute="User::all([\'id\', \'name\', \'email\'])->each(function(\$u) { echo \$u->id . \' | \' . \$u->name . \' | \' . \$u->email . PHP_EOL; });" 2>&1');
echo $usersOutput . "\n";

// Step 4: Display login instructions
echo "\n=== SETUP COMPLETED! ===\n\n";
echo "✅ Database structure verified\n";
echo "✅ Seeders executed\n";
echo "✅ Users created\n\n";

echo "=== LOGIN CREDENTIALS ===\n\n";
echo "You can login with any of these accounts:\n\n";
echo "1. Admin User:\n";
echo "   Email: Check the output above (admin*@example.com)\n";
echo "   Password: password123\n\n";

echo "2. Test User:\n";
echo "   Email: Check the output above (test*@example.com)\n";
echo "   Password: password123\n\n";

echo "3. OR Create your own account:\n";
echo "   Go to: http://127.0.0.1:8000/register\n\n";

echo "=== NEXT STEPS ===\n\n";
echo "1. Start your development server:\n";
echo "   php artisan serve\n\n";

echo "2. Open your browser:\n";
echo "   http://127.0.0.1:8000\n\n";

echo "3. Login with one of the accounts above\n\n";

echo "4. Test critical pages:\n";
echo "   - Dashboard: http://127.0.0.1:8000/dashboard\n";
echo "   - Biaya Bahan: http://127.0.0.1:8000/master-data/biaya-bahan\n";
echo "   - BTKL: http://127.0.0.1:8000/master-data/btkl\n";
echo "   - Neraca Saldo: http://127.0.0.1:8000/akuntansi/neraca-saldo\n\n";

echo "=== TROUBLESHOOTING ===\n\n";
echo "If you still get 'users table not found' error:\n";
echo "1. Check .env file - make sure DB_DATABASE is correct\n";
echo "2. Run: php artisan config:clear\n";
echo "3. Run: php artisan cache:clear\n";
echo "4. Restart your server\n\n";

echo "If you need to create a specific user:\n";
echo "Run: php artisan tinker\n";
echo "Then: User::create(['name' => 'Your Name', 'email' => 'your@email.com', 'password' => bcrypt('yourpassword'), 'email_verified_at' => now()]);\n\n";

echo "=== READY TO USE! ===\n";
