<?php

echo "=== SETUP DATABASE UNTUK PENDAFTARAN BARU ===\n\n";

// 1. Buat database baru
echo "1. Membuat database baru...\n";
$command = "mysql -u root -e \"CREATE DATABASE IF NOT EXISTS umkm_coe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\"";
exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Database umkm_coe berhasil dibuat\n";
} else {
    echo "❌ Gagal membuat database\n";
    echo "Output: $output\n";
    exit(1);
}

// 2. Update .env file
echo "\n2. Mengupdate konfigurasi .env...\n";
$envContent = file_get_contents('.env');
$envContent = preg_replace('/DB_DATABASE=.*$/m', 'DB_DATABASE=umkm_coe', $envContent);
file_put_contents('.env', $envContent);
echo "✅ Konfigurasi database diupdate\n";

// 3. Clear cache
echo "\n3. Membersihkan cache...\n";
exec('php artisan config:clear', $output, $returnCode);
echo "✅ Cache dibersihkan\n";

// 4. Run migrate
echo "\n4. Menjalankan migrate...\n";
exec('php artisan migrate --force', $output, $returnCode);
echo "✅ Migrate selesai\n";

// 5. Buat user admin
echo "\n5. Membuat user admin...\n";
$createCommand = "php artisan tinker --execute=\"App\Models\User::create(['name' => 'Administrator', 'email' => 'admin@umkm.com', 'password' => Hash::make('admin123'), 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()]); echo 'Admin user created';\"";
exec($createCommand, $output, $returnCode);
echo "✅ User admin dibuat\n";

// 6. Buat data awal
echo "\n6. Membuat data awal...\n";
exec('php create_initial_data.php', $output, $returnCode);
echo "✅ Data awal dibuat\n";

echo "\n=== SETUP SELESAI ===\n";
echo "🚀 Database siap untuk pendaftaran baru!\n";
echo "🌐 Server akan berjalan di http://127.0.0.1:8000\n";
echo "👤 Login dengan: admin@umkm.com / admin123\n";
