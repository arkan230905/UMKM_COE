<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Membersihkan SEMUA data pelanggan...\n\n";

try {
    // Get all pelanggan data
    $pelanggans = \DB::table('users')->where('role', 'pelanggan')->get();
    $totalPelanggan = $pelanggans->count();
    
    echo "Found $totalPelanggan pelanggan records\n";
    
    if ($totalPelanggan > 0) {
        echo "\nData pelanggan yang akan dihapus:\n";
        echo str_repeat("=", 80) . "\n";
        
        foreach ($pelanggans as $pelanggan) {
            echo sprintf("ID: %d | Name: %-20s | Email: %-30s | Phone: %-15s | Password: %s\n", 
                $pelanggan->id, 
                $pelanggan->name ?? 'N/A', 
                $pelanggan->email ?? 'N/A', 
                $pelanggan->phone ?? 'N/A',
                $pelanggan->plain_password ?? 'N/A'
            );
        }
        
        echo str_repeat("=", 80) . "\n\n";
        
        // Delete all pelanggan
        $deleted = \DB::table('users')->where('role', 'pelanggan')->delete();
        
        if ($deleted > 0) {
            echo "✅ Berhasil menghapus $deleted data pelanggan\n";
        } else {
            echo "❌ Gagal menghapus data pelanggan\n";
        }
    } else {
        echo "ℹ️  Tabel pelanggan sudah kosong\n";
    }
    
    // Reset auto-increment
    echo "\nMereset auto-increment ID users...\n";
    \DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
    echo "✅ Auto-increment ID users direset ke 1\n";
    
    // Verify deletion
    $finalCount = \DB::table('users')->where('role', 'pelanggan')->count();
    echo "\nVerifikasi penghapusan:\n";
    echo "Jumlah pelanggan setelah penghapusan: $finalCount\n";
    
    if ($finalCount === 0) {
        echo "✅ Tabel pelanggan benar-benar kosong\n";
    } else {
        echo "⚠️  Masih ada $finalCount data pelanggan\n";
    }
    
    // Show remaining users (non-pelanggan)
    echo "\n=== SISA DATA USERS (NON-PELANGGAN) ===\n";
    $otherUsers = \DB::table('users')->where('role', '!=', 'pelanggan')->get();
    
    if ($otherUsers->count() > 0) {
        foreach ($otherUsers as $user) {
            echo sprintf("ID: %d | Name: %-20s | Role: %-15s | Email: %s\n", 
                $user->id, 
                $user->name ?? 'N/A', 
                $user->role ?? 'N/A',
                $user->email ?? 'N/A'
            );
        }
    } else {
        echo "Tidak ada data users lain\n";
    }
    
    echo "\n=== PEMBERSIHAN SELESAI ===\n";
    echo "Status: " . ($finalCount === 0 ? '✅ BERHASIL BERSIH' : '⚠️  MASIH ADA DATA') . "\n";
    echo "Total data dihapus: $deleted\n";
    echo "Tabel pelanggan: " . ($finalCount === 0 ? 'BERSIH TOTAL' : 'MASIH ADA DATA') . "\n";
    
    echo "\n🎯 READY FOR TESTING:\n";
    echo "1. Tambah pelanggan baru via web interface\n";
    echo "2. Test password input: 'test123456'\n";
    echo "3. Verify plain_password tersimpan dengan benar\n";
    echo "4. Test show/hide password functionality\n";
    echo "5. Test copy password functionality\n";
    echo "6. Test reset password functionality\n";
    
    echo "\n📝 EXPECTED BEHAVIOR:\n";
    echo "- Input password: 'test123456'\n";
    echo "- Plain password stored: 'test123456'\n";
    echo "- Hashed password for login: (bcrypt hash)\n";
    echo "- Display when clicked: 'test123456'\n";
    echo "- Copy result: 'test123456'\n";
    
} catch (\Exception $e) {
    echo "❌ Error membersihkan data pelanggan: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
