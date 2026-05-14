<?php

// Script untuk memperbaiki semua migration yang duplikat kolom

$migrations = [
    'database/migrations/2026_04_30_141141_add_missing_columns_to_pembelians_table.php',
    'database/migrations/2026_05_06_053330_add_bukti_faktur_to_pembelians_table.php',
];

foreach ($migrations as $file) {
    if (!file_exists($file)) {
        echo "Skip: $file (not found)\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Pattern untuk menambah kolom tanpa check
    $patterns = [
        // Pattern 1: $table->string('bukti_faktur')
        [
            'old' => '/\$table->string\(\'bukti_faktur\'\)(.*?);/',
            'new' => 'if (!Schema::hasColumn(\'pembelians\', \'bukti_faktur\')) {
                $table->string(\'bukti_faktur\')$1;
            }'
        ],
        // Pattern 2: $table->text('bukti_faktur')
        [
            'old' => '/\$table->text\(\'bukti_faktur\'\)(.*?);/',
            'new' => 'if (!Schema::hasColumn(\'pembelians\', \'bukti_faktur\')) {
                $table->text(\'bukti_faktur\')$1;
            }'
        ],
    ];
    
    $modified = false;
    foreach ($patterns as $pattern) {
        if (preg_match($pattern['old'], $content)) {
            $content = preg_replace($pattern['old'], $pattern['new'], $content);
            $modified = true;
        }
    }
    
    if ($modified) {
        file_put_contents($file, $content);
        echo "✓ Fixed: $file\n";
    } else {
        echo "- Skip: $file (no changes needed)\n";
    }
}

echo "\n✓ Done!\n";
