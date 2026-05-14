<?php

/**
 * SCRIPT OTOMATIS: UBAH SEMUA REFERENSI 'coas' MENJADI 'accounts'
 * 
 * Script ini akan:
 * 1. Scan semua file migrasi dan seeder
 * 2. Replace semua referensi 'coas' dengan 'accounts'
 * 3. Backup file sebelum diubah
 * 
 * CARA PAKAI:
 * php fix_coas_to_accounts.php
 */

echo "🔧 MEMULAI PROSES STANDARISASI: coas → accounts\n";
echo str_repeat("=", 60) . "\n\n";

// Direktori yang akan di-scan
$directories = [
    __DIR__ . '/database/migrations',
    __DIR__ . '/database/seeders',
];

// Pattern yang akan diganti
$replacements = [
    // Table references
    "table('coas')" => "table('accounts')",
    'table("coas")' => 'table("accounts")',
    
    // Foreign key constraints
    "->on('coas')" => "->on('accounts')",
    '->on("coas")' => '->on("accounts")',
    "constrained('coas')" => "constrained('accounts')",
    'constrained("coas")' => 'constrained("accounts")',
    
    // Schema checks
    "hasTable('coas')" => "hasTable('accounts')",
    'hasTable("coas")' => 'hasTable("accounts")',
    "hasColumn('coas'" => "hasColumn('accounts'",
    'hasColumn("coas"' => 'hasColumn("accounts"',
    
    // Comments and documentation
    "tabel 'coas'" => "tabel 'accounts'",
    'tabel "coas"' => 'tabel "accounts"',
    "tabel coas" => "tabel accounts",
    "Tabel coas" => "Tabel accounts",
    "TABEL COAS" => "TABEL ACCOUNTS",
];

$totalFiles = 0;
$totalChanges = 0;
$changedFiles = [];

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        echo "⚠️  Direktori tidak ditemukan: $directory\n";
        continue;
    }
    
    echo "📁 Scanning: $directory\n";
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );
    
    foreach ($files as $file) {
        if ($file->isDir() || $file->getExtension() !== 'php') {
            continue;
        }
        
        $filePath = $file->getPathname();
        $content = file_get_contents($filePath);
        $originalContent = $content;
        $fileChanges = 0;
        
        // Apply replacements
        foreach ($replacements as $search => $replace) {
            $newContent = str_replace($search, $replace, $content);
            if ($newContent !== $content) {
                $count = substr_count($content, $search);
                $fileChanges += $count;
                $content = $newContent;
            }
        }
        
        // If changes were made, backup and save
        if ($content !== $originalContent) {
            // Create backup
            $backupPath = $filePath . '.backup_' . date('Y-m-d_H-i-s');
            file_put_contents($backupPath, $originalContent);
            
            // Save modified file
            file_put_contents($filePath, $content);
            
            $totalChanges += $fileChanges;
            $changedFiles[] = [
                'file' => str_replace(__DIR__ . '/', '', $filePath),
                'changes' => $fileChanges,
                'backup' => str_replace(__DIR__ . '/', '', $backupPath),
            ];
            
            echo "  ✅ " . basename($filePath) . " ($fileChanges perubahan)\n";
        }
        
        $totalFiles++;
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RINGKASAN:\n";
echo "  • Total file di-scan: $totalFiles\n";
echo "  • File yang diubah: " . count($changedFiles) . "\n";
echo "  • Total perubahan: $totalChanges\n\n";

if (count($changedFiles) > 0) {
    echo "📝 DAFTAR FILE YANG DIUBAH:\n";
    foreach ($changedFiles as $item) {
        echo "  • {$item['file']} ({$item['changes']} perubahan)\n";
        echo "    Backup: {$item['backup']}\n";
    }
    
    echo "\n✅ PROSES SELESAI!\n";
    echo "⚠️  File backup telah dibuat. Jika ada masalah, restore dari file .backup\n";
} else {
    echo "ℹ️  Tidak ada perubahan yang diperlukan.\n";
}

echo "\n🔍 LANGKAH SELANJUTNYA:\n";
echo "1. Review perubahan yang dibuat\n";
echo "2. Hapus file: database/migrations/2025_10_28_161000_create_coas_table.php\n";
echo "3. Jalankan: php artisan migrate:fresh\n";
echo "4. Jalankan: php artisan db:seed --class=JasukeCoaSeeder\n";
echo "5. Verifikasi: SELECT * FROM accounts LIMIT 10;\n";
echo "\n";
