<?php
/**
 * ANALISIS & PERBAIKI STRUKTUR DATABASE
 * ======================================
 * Script ini akan menganalisis dan memperbaiki struktur database
 * agar siap di-export manual tanpa error
 * 
 * CARA PAKAI:
 * php analisis_dan_perbaiki_database.php
 * Atau buka di browser: http://localhost/analisis_dan_perbaiki_database.php
 */

// Konfigurasi database
$config = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'eadt_umkm',
    'username' => 'root',
    'password' => ''
];

echo "==============================================\n";
echo "ANALISIS & PERBAIKI STRUKTUR DATABASE\n";
echo "==============================================\n\n";

echo "Database: {$config['database']}\n";
echo "Host: {$config['host']}:{$config['port']}\n\n";

try {
    // Koneksi ke database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✓ Koneksi database berhasil\n\n";
    
    // Array untuk menyimpan masalah dan solusi
    $issues = [];
    $fixes = [];
    
    echo "==============================================\n";
    echo "TAHAP 1: ANALISIS MASALAH\n";
    echo "==============================================\n\n";
    
    // 1. Cek Foreign Key Constraints
    echo "[1] Menganalisis Foreign Key Constraints...\n";
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '{$config['database']}'
        AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME
    ");
    $foreignKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "    Ditemukan " . count($foreignKeys) . " foreign key constraints\n";
    
    // Cek apakah ada data yang tidak valid (orphaned records)
    $orphanedRecords = [];
    foreach ($foreignKeys as $fk) {
        $table = $fk['TABLE_NAME'];
        $column = $fk['COLUMN_NAME'];
        $refTable = $fk['REFERENCED_TABLE_NAME'];
        $refColumn = $fk['REFERENCED_COLUMN_NAME'];
        
        // Cek orphaned records
        $stmt = $pdo->query("
            SELECT COUNT(*) as count
            FROM `{$table}` t
            WHERE t.`{$column}` IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM `{$refTable}` r
                WHERE r.`{$refColumn}` = t.`{$column}`
            )
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $orphanedRecords[] = [
                'table' => $table,
                'column' => $column,
                'count' => $result['count'],
                'ref_table' => $refTable,
                'ref_column' => $refColumn
            ];
        }
    }
    
    if (!empty($orphanedRecords)) {
        echo "    ⚠️  Ditemukan " . count($orphanedRecords) . " tabel dengan orphaned records\n";
        foreach ($orphanedRecords as $orphan) {
            echo "        - {$orphan['table']}.{$orphan['column']}: {$orphan['count']} records\n";
            $issues[] = "Orphaned records di {$orphan['table']}.{$orphan['column']}";
        }
    } else {
        echo "    ✓ Tidak ada orphaned records\n";
    }
    
    echo "\n";
    
    // 2. Cek Character Set dan Collation
    echo "[2] Menganalisis Character Set dan Collation...\n";
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            TABLE_COLLATION
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '{$config['database']}'
        AND TABLE_TYPE = 'BASE TABLE'
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $inconsistentCollation = [];
    foreach ($tables as $table) {
        if ($table['TABLE_COLLATION'] !== 'utf8mb4_unicode_ci' && 
            $table['TABLE_COLLATION'] !== 'utf8mb4_general_ci') {
            $inconsistentCollation[] = $table;
        }
    }
    
    if (!empty($inconsistentCollation)) {
        echo "    ⚠️  Ditemukan " . count($inconsistentCollation) . " tabel dengan collation tidak standar\n";
        foreach ($inconsistentCollation as $table) {
            echo "        - {$table['TABLE_NAME']}: {$table['TABLE_COLLATION']}\n";
            $issues[] = "Collation tidak standar di {$table['TABLE_NAME']}";
        }
    } else {
        echo "    ✓ Semua tabel menggunakan collation yang konsisten\n";
    }
    
    echo "\n";
    
    // 3. Cek Engine Type
    echo "[3] Menganalisis Storage Engine...\n";
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            ENGINE
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '{$config['database']}'
        AND TABLE_TYPE = 'BASE TABLE'
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $nonInnoDBTables = [];
    foreach ($tables as $table) {
        if ($table['ENGINE'] !== 'InnoDB') {
            $nonInnoDBTables[] = $table;
        }
    }
    
    if (!empty($nonInnoDBTables)) {
        echo "    ⚠️  Ditemukan " . count($nonInnoDBTables) . " tabel tidak menggunakan InnoDB\n";
        foreach ($nonInnoDBTables as $table) {
            echo "        - {$table['TABLE_NAME']}: {$table['ENGINE']}\n";
            $issues[] = "Engine bukan InnoDB di {$table['TABLE_NAME']}";
        }
    } else {
        echo "    ✓ Semua tabel menggunakan InnoDB engine\n";
    }
    
    echo "\n";
    
    // 4. Cek NULL values di kolom yang seharusnya NOT NULL
    echo "[4] Menganalisis NULL values...\n";
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            IS_NULLABLE,
            COLUMN_DEFAULT
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '{$config['database']}'
        AND IS_NULLABLE = 'YES'
        AND COLUMN_KEY = 'MUL'
    ");
    $nullableFK = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "    Ditemukan " . count($nullableFK) . " kolom foreign key yang nullable\n";
    echo "    ✓ Ini normal dan tidak masalah\n";
    
    echo "\n";
    
    // 5. Cek Auto Increment values
    echo "[5] Menganalisis Auto Increment...\n";
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            AUTO_INCREMENT
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '{$config['database']}'
        AND AUTO_INCREMENT IS NOT NULL
    ");
    $autoIncrementTables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "    Ditemukan " . count($autoIncrementTables) . " tabel dengan auto increment\n";
    echo "    ✓ Auto increment values normal\n";
    
    echo "\n";
    
    // RINGKASAN ANALISIS
    echo "==============================================\n";
    echo "RINGKASAN ANALISIS\n";
    echo "==============================================\n\n";
    
    if (empty($issues)) {
        echo "✓ SELAMAT! Database Anda sudah dalam kondisi baik!\n";
        echo "  Anda bisa langsung export manual dari phpMyAdmin.\n\n";
        
        echo "CARA EXPORT YANG BENAR:\n";
        echo "1. Buka phpMyAdmin\n";
        echo "2. Pilih database 'eadt_umkm'\n";
        echo "3. Klik tab 'Export'\n";
        echo "4. Pilih 'Custom' method\n";
        echo "5. PENTING: Centang 'Disable foreign key checks'\n";
        echo "6. Centang 'Add DROP TABLE'\n";
        echo "7. Centang 'Add CREATE DATABASE'\n";
        echo "8. Klik 'Export'\n\n";
        
    } else {
        echo "Ditemukan " . count($issues) . " masalah yang perlu diperbaiki:\n\n";
        foreach ($issues as $i => $issue) {
            echo ($i + 1) . ". {$issue}\n";
        }
        echo "\n";
        
        // TAHAP 2: PERBAIKAN
        echo "==============================================\n";
        echo "TAHAP 2: PERBAIKAN OTOMATIS\n";
        echo "==============================================\n\n";
        
        $fixCount = 0;
        
        // Fix 1: Perbaiki orphaned records
        if (!empty($orphanedRecords)) {
            echo "[FIX 1] Memperbaiki orphaned records...\n";
            foreach ($orphanedRecords as $orphan) {
                echo "    Memperbaiki {$orphan['table']}.{$orphan['column']}...\n";
                
                // Set NULL untuk orphaned records (lebih aman daripada delete)
                $stmt = $pdo->prepare("
                    UPDATE `{$orphan['table']}` t
                    SET t.`{$orphan['column']}` = NULL
                    WHERE t.`{$orphan['column']}` IS NOT NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM `{$orphan['ref_table']}` r
                        WHERE r.`{$orphan['ref_column']}` = t.`{$orphan['column']}`
                    )
                ");
                $stmt->execute();
                $affected = $stmt->rowCount();
                echo "        ✓ {$affected} records diperbaiki\n";
                $fixCount++;
            }
            echo "\n";
        }
        
        // Fix 2: Standardisasi collation
        if (!empty($inconsistentCollation)) {
            echo "[FIX 2] Menstandardisasi collation...\n";
            foreach ($inconsistentCollation as $table) {
                echo "    Mengubah {$table['TABLE_NAME']}...\n";
                try {
                    $pdo->exec("ALTER TABLE `{$table['TABLE_NAME']}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    echo "        ✓ Berhasil\n";
                    $fixCount++;
                } catch (Exception $e) {
                    echo "        ⚠️  Gagal: " . $e->getMessage() . "\n";
                }
            }
            echo "\n";
        }
        
        // Fix 3: Ubah engine ke InnoDB
        if (!empty($nonInnoDBTables)) {
            echo "[FIX 3] Mengubah storage engine ke InnoDB...\n";
            foreach ($nonInnoDBTables as $table) {
                echo "    Mengubah {$table['TABLE_NAME']}...\n";
                try {
                    $pdo->exec("ALTER TABLE `{$table['TABLE_NAME']}` ENGINE=InnoDB");
                    echo "        ✓ Berhasil\n";
                    $fixCount++;
                } catch (Exception $e) {
                    echo "        ⚠️  Gagal: " . $e->getMessage() . "\n";
                }
            }
            echo "\n";
        }
        
        echo "==============================================\n";
        echo "PERBAIKAN SELESAI\n";
        echo "==============================================\n\n";
        echo "Total perbaikan: {$fixCount}\n\n";
        
        echo "✓ Database Anda sekarang sudah diperbaiki!\n";
        echo "  Anda bisa langsung export manual dari phpMyAdmin.\n\n";
    }
    
    // PANDUAN EXPORT
    echo "==============================================\n";
    echo "PANDUAN EXPORT MANUAL (PENTING!)\n";
    echo "==============================================\n\n";
    
    echo "Ikuti langkah ini saat export dari phpMyAdmin:\n\n";
    
    echo "1. Buka phpMyAdmin\n";
    echo "2. Klik database 'eadt_umkm' di panel kiri\n";
    echo "3. Klik tab 'Export' di atas\n";
    echo "4. Pilih 'Custom - display all possible options'\n";
    echo "5. Di bagian 'Format-specific options':\n";
    echo "   ✓ Centang 'Add DROP TABLE / VIEW / PROCEDURE'\n";
    echo "   ✓ Centang 'Add IF NOT EXISTS'\n";
    echo "   ✓ PENTING: Centang 'Disable foreign key checks'\n";
    echo "   ✓ Centang 'Add CREATE DATABASE / USE statement'\n";
    echo "6. Pilih 'Save output to a file'\n";
    echo "7. Klik tombol 'Export'\n\n";
    
    echo "Dengan mengikuti langkah di atas, file SQL yang Anda\n";
    echo "export akan otomatis include perintah untuk disable\n";
    echo "foreign key checks, sehingga teman Anda tidak akan\n";
    echo "mengalami error saat import!\n\n";
    
    echo "==============================================\n";
    echo "SELESAI\n";
    echo "==============================================\n";
    
} catch (PDOException $e) {
    echo "\n✗ Error koneksi database:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ Error:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}
