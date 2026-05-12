<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== FIX EMAIL UNIQUE CONSTRAINT ===\n\n";

// Check current indexes on pegawais table
$indexes = DB::select("SHOW INDEX FROM pegawais WHERE Column_name = 'email'");
echo "Current email indexes:\n";
foreach ($indexes as $idx) {
    echo "  Key: {$idx->Key_name} | Unique: " . ($idx->Non_unique == 0 ? 'YES' : 'NO') . "\n";
}
echo "\n";

// Drop global unique constraint if exists
try {
    DB::statement('ALTER TABLE pegawais DROP INDEX pegawais_email_unique');
    echo "✅ Dropped: pegawais_email_unique\n";
} catch (\Exception $e) {
    echo "ℹ️  pegawais_email_unique not found (already dropped or different name)\n";
}

// Try other possible constraint names
$possibleNames = ['email', 'pegawais_email_unique', 'email_unique'];
foreach ($possibleNames as $name) {
    try {
        DB::statement("ALTER TABLE pegawais DROP INDEX `{$name}`");
        echo "✅ Dropped index: {$name}\n";
    } catch (\Exception $e) {
        // ignore
    }
}

// Check if composite unique already exists
$compositeExists = false;
$allIndexes = DB::select("SHOW INDEX FROM pegawais");
foreach ($allIndexes as $idx) {
    if ($idx->Key_name === 'pegawais_email_user_id_unique') {
        $compositeExists = true;
        break;
    }
}

if (!$compositeExists) {
    // Add composite unique: email + user_id
    try {
        DB::statement('ALTER TABLE pegawais ADD UNIQUE KEY pegawais_email_user_id_unique (email, user_id)');
        echo "✅ Added composite unique: (email, user_id)\n";
    } catch (\Exception $e) {
        echo "⚠️  Could not add composite unique: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ Composite unique (email, user_id) already exists\n";
}

// Verify final state
echo "\nFinal email indexes:\n";
$finalIndexes = DB::select("SHOW INDEX FROM pegawais WHERE Column_name = 'email'");
foreach ($finalIndexes as $idx) {
    echo "  Key: {$idx->Key_name} | Unique: " . ($idx->Non_unique == 0 ? 'YES' : 'NO') . "\n";
}

echo "\n✅ DONE! Email sekarang unik per user_id (multi-tenant safe)\n";
echo "Anda bisa menambah pegawai dengan email yang sama di akun berbeda.\n";
