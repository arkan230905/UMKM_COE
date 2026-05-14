<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get count before deletion
$countBefore = \Illuminate\Support\Facades\DB::table('btkls')->count();
echo "=== HAPUS DATA BTKL ===\n";
echo "Data sebelum dihapus: " . $countBefore . " records\n\n";

// Delete all BTKL data
$deleted = \Illuminate\Support\Facades\DB::table('btkls')->delete();
echo "Data yang dihapus: " . $deleted . " records\n";

// Verify
$countAfter = \Illuminate\Support\Facades\DB::table('btkls')->count();
echo "Data setelah dihapus: " . $countAfter . " records\n";

if ($countAfter === 0) {
    echo "\n✓ Semua data BTKL berhasil dihapus!\n";
} else {
    echo "\n✗ Ada " . $countAfter . " records yang masih tersisa\n";
}
