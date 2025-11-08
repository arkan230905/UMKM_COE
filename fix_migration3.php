<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Nonaktifkan foreign key check
DB::statement('SET FOREIGN_KEY_CHECKS=0');

echo "Memperbaiki struktur tabel...\n";

// 1. Hapus foreign key constraint yang bermasalah
try {
    echo "Menghapus foreign key constraint yang bermasalah...\n";
    
    // Dapatkan daftar foreign key di tabel bops
    $foreignKeys = DB::select(
        "SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_NAME = 'bops' 
        AND CONSTRAINT_TYPE = 'FOREIGN_KEY'"
    );
    
    // Hapus setiap foreign key
    foreach ($foreignKeys as $fk) {
        $constraintName = $fk->CONSTRAINT_NAME;
        DB::statement("ALTER TABLE bops DROP FOREIGN KEY {$constraintName}");
        echo "Dihapus foreign key: {$constraintName}\n";
    }
} catch (\Exception $e) {
    echo "Gagal menghapus foreign key: " . $e->getMessage() . "\n";
}

// 2. Update tipe data kolom kode_akun di tabel coas
try {
    echo "Memperbarui tipe data kode_akun di tabel coas...\n";
    
    // Cek apakah kolom ada dan perbarui tipenya
    $columnExists = DB::select("SHOW COLUMNS FROM coas LIKE 'kode_akun'");
    
    if (!empty($columnExists)) {
        DB::statement('ALTER TABLE coas MODIFY kode_akun VARCHAR(20) NOT NULL');
        echo "Berhasil memperbarui tipe data kode_akun di tabel coas\n";
    } else {
        echo "Kolom kode_akun tidak ditemukan di tabel coas\n";
    }
} catch (\Exception $e) {
    echo "Gagal memperbarui tipe data kode_akun: " . $e->getMessage() . "\n";
}

// 3. Update tipe data kolom kode_akun di tabel bops
try {
    echo "Memperbarui tipe data kode_akun di tabel bops...\n";
    
    // Cek apakah tabel dan kolom ada
    $tableExists = DB::select("SHOW TABLES LIKE 'bops'");
    
    if (!empty($tableExists)) {
        $columnExists = DB::select("SHOW COLUMNS FROM bops LIKE 'kode_akun'");
        
        if (!empty($columnExists)) {
            DB::statement('ALTER TABLE bops MODIFY kode_akun VARCHAR(20) NOT NULL');
            echo "Berhasil memperbarui tipe data kode_akun di tabel bops\n";
        } else {
            echo "Kolom kode_akun tidak ditemukan di tabel bops\n";
        }
    } else {
        echo "Tabel bops tidak ditemukan\n";
    }
} catch (\Exception $e) {
    echo "Gagal memperbarui tipe data kode_akun di bops: " . $e->getMessage() . "\n";
}

// 4. Tambahkan kembali foreign key
try {
    echo "Menambahkan kembali foreign key...\n";
    
    // Cek apakah tabel coas dan bops ada
    $coasExists = DB::select("SHOW TABLES LIKE 'coas'");
    $bopsExists = DB::select("SHOW TABLES LIKE 'bops'");
    
    if (!empty($coasExists) && !empty($bopsExists)) {
        // Cek apakah kolom kode_akun ada di kedua tabel
        $coasColumn = DB::select("SHOW COLUMNS FROM coas LIKE 'kode_akun'");
        $bopsColumn = DB::select("SHOW COLUMNS FROM bops LIKE 'kode_akun'");
        
        if (!empty($coasColumn) && !empty($bopsColumn)) {
            // Tambahkan foreign key
            DB::statement('ALTER TABLE bops ADD CONSTRAINT bops_kode_akun_foreign FOREIGN KEY (kode_akun) REFERENCES coas(kode_akun)');
            echo "Berhasil menambahkan foreign key\n";
        } else {
            echo "Kolom kode_akun tidak ditemukan di salah satu tabel\n";
        }
    } else {
        echo "Tabel coas atau bops tidak ditemukan\n";
    }
} catch (\Exception $e) {
    echo "Gagal menambahkan foreign key: " . $e->getMessage() . "\n";
}

// Aktifkan kembali foreign key check
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\nPerbaikan selesai. Sekarang coba jalankan:\n";
echo "php artisan migrate --force\n\n";
