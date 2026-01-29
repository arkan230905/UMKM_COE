<?php
// Test login controller
// Run: php artisan tinker
// Then: include 'test_login_controller.php';

echo "=== TEST LOGIN CONTROLLER ===\n\n";

// Test data
$testCases = [
    [
        'name' => 'Admin Login',
        'data' => [
            'login_role' => 'admin',
            'email' => 'admin@umkm.com',
            'kode_perusahaan' => 'UMKM-COE12'
        ]
    ],
    [
        'name' => 'Owner Login',
        'data' => [
            'login_role' => 'owner',
            'email' => 'owner@umkm.com',
            'password' => 'password',
            'kode_perusahaan' => 'UMKM-COE12'
        ]
    ],
    [
        'name' => 'Pelanggan Login',
        'data' => [
            'login_role' => 'pelanggan',
            'email' => 'pelanggan@test.com',
            'password' => 'password'
        ]
    ]
];

// Check users in database
echo "1. Checking users in database:\n";
$users = \App\Models\User::select('id', 'name', 'email', 'role', 'perusahaan_id')->get();
foreach ($users as $user) {
    echo "   - {$user->name} ({$user->email}) - Role: {$user->role}\n";
}

echo "\n2. Checking perusahaan:\n";
$perusahaan = \App\Models\Perusahaan::select('id', 'nama', 'kode')->get();
foreach ($perusahaan as $p) {
    echo "   - {$p->nama} (Kode: {$p->kode})\n";
}

echo "\n=== END TEST ===\n";
?>