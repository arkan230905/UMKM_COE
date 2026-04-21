<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Cek struktur tabel
$columns = DB::select("DESCRIBE journal_entries");
echo "=== KOLOM JOURNAL_ENTRIES ===\n";
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== SAMPLE DATA ===\n";
$entries = DB::table('journal_entries')->limit(5)->get();
foreach ($entries as $entry) {
    echo "ID: " . $entry->id . " | ";
    foreach ((array)$entry as $key => $val) {
        if ($key !== 'id') {
            echo $key . ": " . $val . " | ";
        }
    }
    echo "\n";
}
