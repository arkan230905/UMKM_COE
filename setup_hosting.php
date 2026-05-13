<?php

/**
 * Script Helper untuk Setup Hosting
 * Jalankan setelah migrate di server hosting
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🚀 Setup Hosting - UMKM COE\n";
echo str_repeat("=", 70) . "\n\n";

// 1. Check database connection
echo "1️⃣  Checking database connection...\n";
try {
    DB::connection()->getPdo();
    echo "   ✅ Database connected: " . DB::connection()->getDatabaseName() . "\n\n";
} catch (\Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Check if users exist
echo "2️⃣  Checking users...\n";
$userCount = DB::table('users')->count();
echo "   📊 Total users: {$userCount}\n";

if ($userCount == 0) {
    echo "   ⚠️  No users found. Creating admin user...\n";
    
    DB::table('users')->insert([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'owner',
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "   ✅ Admin user created\n";
    echo "      Email: admin@example.com\n";
    echo "      Password: password\n";
    echo "      Role: owner\n\n";
} else {
    echo "   ✅ Users already exist\n\n";
}

// 3. Setup COA for each user
echo "3️⃣  Setting up COA...\n";
$users = DB::table('users')->get();

foreach ($users as $user) {
    $coaCount = DB::table('coas')->where('user_id', $user->id)->count();
    
    if ($coaCount == 0) {
        echo "   🌱 Creating COA for user: {$user->name} (ID: {$user->id})\n";
        
        $seeder = new \Database\Seeders\DefaultCoaSeeder();
        $seeder->run($user->id);
        
        $newCoaCount = DB::table('coas')->where('user_id', $user->id)->count();
        echo "      ✅ Created {$newCoaCount} COA\n";
    } else {
        echo "   ✅ User {$user->name} already has {$coaCount} COA\n";
    }
}
echo "\n";

// 4. Setup Satuan for each user
echo "4️⃣  Setting up Satuan...\n";

$defaultSatuanData = [
    ['kode' => 'ONS', 'nama' => 'Ons'],
    ['kode' => 'KG', 'nama' => 'Kilogram'],
    ['kode' => 'ML', 'nama' => 'Mililiter'],
    ['kode' => 'G', 'nama' => 'Gram'],
    ['kode' => 'LTR', 'nama' => 'Liter'],
    ['kode' => 'PTG', 'nama' => 'Potong'],
    ['kode' => 'EKOR', 'nama' => 'Ekor'],
    ['kode' => 'SDT', 'nama' => 'Sendok Teh'],
    ['kode' => 'SDM', 'nama' => 'Sendok Makan'],
    ['kode' => 'PCS', 'nama' => 'Pieces'],
    ['kode' => 'BNGKS', 'nama' => 'Bungkus'],
    ['kode' => 'CUP', 'nama' => 'Cup'],
    ['kode' => 'GL', 'nama' => 'Galon'],
    ['kode' => 'TBG', 'nama' => 'Tabung'],
    ['kode' => 'SNG', 'nama' => 'Siung'],
    ['kode' => 'KLG', 'nama' => 'Kaleng'],
];

foreach ($users as $user) {
    $satuanCount = DB::table('satuans')->where('user_id', $user->id)->count();
    
    if ($satuanCount == 0) {
        echo "   🌱 Creating Satuan for user: {$user->name} (ID: {$user->id})\n";
        
        foreach ($defaultSatuanData as $satuan) {
            DB::table('satuans')->insert([
                'kode' => $satuan['kode'],
                'nama' => $satuan['nama'],
                'user_id' => $user->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $newSatuanCount = DB::table('satuans')->where('user_id', $user->id)->count();
        echo "      ✅ Created {$newSatuanCount} Satuan\n";
    } else {
        echo "   ✅ User {$user->name} already has {$satuanCount} Satuan\n";
    }
}
echo "\n";

// 5. Verify setup
echo "5️⃣  Verifying setup...\n";
$allGood = true;

foreach ($users as $user) {
    $coaCount = DB::table('coas')->where('user_id', $user->id)->count();
    $satuanCount = DB::table('satuans')->where('user_id', $user->id)->count();
    
    if ($coaCount < 51) {
        echo "   ⚠️  User {$user->name}: COA count is {$coaCount} (expected 51)\n";
        $allGood = false;
    }
    
    if ($satuanCount < 16) {
        echo "   ⚠️  User {$user->name}: Satuan count is {$satuanCount} (expected 16)\n";
        $allGood = false;
    }
}

if ($allGood) {
    echo "   ✅ All users have complete data\n\n";
} else {
    echo "   ⚠️  Some users have incomplete data\n\n";
}

// 6. Summary
echo str_repeat("=", 70) . "\n";
echo "📊 Setup Summary:\n";
echo "   Total Users: " . DB::table('users')->count() . "\n";
echo "   Total COA: " . DB::table('coas')->count() . "\n";
echo "   Total Satuan: " . DB::table('satuans')->count() . "\n";
echo "\n";

if ($allGood) {
    echo "✅ SETUP COMPLETE! Application is ready to use.\n";
    echo "\n";
    echo "🔐 Login Credentials:\n";
    echo "   Email: admin@example.com\n";
    echo "   Password: password\n";
    echo "   Role: owner\n";
} else {
    echo "⚠️  SETUP INCOMPLETE! Please check the warnings above.\n";
}

echo "\n";
echo "📝 Next Steps:\n";
echo "   1. Login to the application\n";
echo "   2. Change admin password\n";
echo "   3. Test all features\n";
echo "   4. Create additional users if needed\n";
