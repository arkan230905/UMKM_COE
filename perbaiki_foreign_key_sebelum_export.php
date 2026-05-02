<?php
/**
 * PERBAIKI FOREIGN KEY SEBELUM EXPORT
 * ====================================
 * Script ini akan memperbaiki semua masalah foreign key
 * sehingga database bisa di-export dan di-import tanpa error
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==============================================\n";
echo "PERBAIKI FOREIGN KEY SEBELUM EXPORT\n";
echo "==============================================\n\n";

$pdo = DB::connection()->getPdo();
$database = env('DB_DATABASE');

echo "Database: {$database}\n\n";

// Daftar tabel dan foreign key yang perlu dicek
$checks = [
    [
        'table' => 'bahan_bakus',
        'columns' => [
            'sub_satuan_1_id' => 'satuans',
            'sub_satuan_2_id' => 'satuans',
            'sub_satuan_3_id' => 'satuans',
            'satuan_id' => 'satuans',
        ]
    ],
    [
        'table' => 'bahan_pendukungs',
        'columns' => [
            'sub_satuan_1_id' => 'satuans',
            'sub_satuan_2_id' => 'satuans',
            'sub_satuan_3_id' => 'satuans',
            'satuan_id' => 'satuans',
        ]
    ],
    [
        'table' => 'produks',
        'columns' => [
            'satuan_id' => 'satuans',
        ]
    ],
    [
        'table' => 'pegawais',
        'columns' => [
            'jabatan_id' => 'jabatans',
        ]
    ],
    [
        'table' => 'penjualans',
        'columns' => [
            'pelanggan_id' => 'pelanggans',
        ]
    ],
    [
        'table' => 'pembelians',
        'columns' => [
            'vendor_id' => 'vendors',
        ]
    ],
];

$totalFixed = 0;
$totalOrphaned = 0;

echo "==============================================\n";
echo "MEMERIKSA DAN MEMPERBAIKI FOREIGN KEY\n";
echo "==============================================\n\n";

foreach ($checks as $check) {
    $table = $check['table'];
    
    // Cek apakah tabel ada
    $tableExists = $pdo->query("SHOW TABLES LIKE '{$table}'")->rowCount() > 0;
    if (!$tableExists) {
        echo "⚪ {$table}: Tabel tidak ada, skip\n";
        continue;
    }
    
    echo "Memeriksa tabel: {$table}\n";
    
    foreach ($check['columns'] as $column => $refTable) {
        // Cek apakah kolom ada
        $columnExists = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->rowCount() > 0;
        if (!$columnExists) {
            echo "  ⚪ Kolom {$column} tidak ada, skip\n";
            continue;
        }
        
        // Cari orphaned records (foreign key yang tidak valid)
        $query = "
            SELECT COUNT(*) as count
            FROM `{$table}` t
            WHERE t.`{$column}` IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM `{$refTable}` r
                WHERE r.id = t.`{$column}`
            )
        ";
        
        $result = $pdo->query($query)->fetch(PDO::FETCH_ASSOC);
        $orphanedCount = $result['count'];
        
        if ($orphanedCount > 0) {
            echo "  ❌ {$column} → {$refTable}: {$orphanedCount} orphaned records\n";
            $totalOrphaned += $orphanedCount;
            
            // Cek apakah kolom nullable
            $columnInfo = $pdo->query("SHOW COLUMNS FROM `{$table}` WHERE Field = '{$column}'")->fetch(PDO::FETCH_ASSOC);
            $isNullable = ($columnInfo['Null'] === 'YES');
            
            if ($isNullable) {
                // Perbaiki dengan set NULL
                $updateQuery = "
                    UPDATE `{$table}` t
                    SET t.`{$column}` = NULL
                    WHERE t.`{$column}` IS NOT NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM `{$refTable}` r
                        WHERE r.id = t.`{$column}`
                    )
                ";
                
                $updated = $pdo->exec($updateQuery);
                echo "  ✅ Diperbaiki: {$updated} records di-set NULL\n";
                $totalFixed += $updated;
            } else {
                // Kolom NOT NULL, cari ID valid pertama dari tabel referensi
                $firstValidId = $pdo->query("SELECT id FROM `{$refTable}` ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                
                if ($firstValidId) {
                    $defaultId = $firstValidId['id'];
                    $updateQuery = "
                        UPDATE `{$table}` t
                        SET t.`{$column}` = {$defaultId}
                        WHERE NOT EXISTS (
                            SELECT 1 FROM `{$refTable}` r
                            WHERE r.id = t.`{$column}`
                        )
                    ";
                    
                    $updated = $pdo->exec($updateQuery);
                    echo "  ✅ Diperbaiki: {$updated} records di-set ke ID default ({$defaultId})\n";
                    $totalFixed += $updated;
                } else {
                    echo "  ⚠️  Tidak bisa perbaiki: Tabel {$refTable} kosong!\n";
                }
            }
        } else {
            echo "  ✅ {$column} → {$refTable}: OK\n";
        }
    }
    
    echo "\n";
}

echo "==============================================\n";
echo "RINGKASAN\n";
echo "==============================================\n\n";
echo "Total orphaned records ditemukan: {$totalOrphaned}\n";
echo "Total records diperbaiki: {$totalFixed}\n\n";

if ($totalFixed > 0) {
    echo "✅ Database berhasil diperbaiki!\n\n";
} else {
    echo "✅ Database sudah bersih, tidak ada masalah!\n\n";
}

echo "==============================================\n";
echo "LANGKAH SELANJUTNYA\n";
echo "==============================================\n\n";
echo "1. Database Anda sekarang sudah bersih\n";
echo "2. Export database dari phpMyAdmin dengan cara:\n\n";
echo "   a. Buka phpMyAdmin\n";
echo "   b. Pilih database '{$database}'\n";
echo "   c. Klik tab 'Export'\n";
echo "   d. Pilih 'Custom - display all possible options'\n";
echo "   e. Di bagian 'Format-specific options':\n";
echo "      ✓ Centang 'Add DROP TABLE / VIEW / PROCEDURE'\n";
echo "      ✓ Centang 'Add IF NOT EXISTS'\n";
echo "      ✓ PENTING: Centang 'Disable foreign key checks'\n";
echo "      ✓ Centang 'Add CREATE DATABASE / USE statement'\n";
echo "   f. Klik 'Export'\n\n";
echo "3. Kirim file SQL ke teman Anda\n";
echo "4. Teman Anda bisa import tanpa error!\n\n";

echo "==============================================\n";
echo "SELESAI\n";
echo "==============================================\n";
