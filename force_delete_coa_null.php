<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Force Delete COA dengan Company NULL ===\n\n";

try {
    // Disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    echo "Foreign key checks disabled\n";
    
    // Hapus COA dengan company_id = NULL
    $deleted = DB::table('coas')->whereNull('company_id')->delete();
    
    echo "✓ Dihapus: $deleted akun\n\n";
    
    // Enable foreign key checks kembali
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "Foreign key checks enabled\n\n";
    
    // Verifikasi
    $total = DB::table('coas')->count();
    $nullCount = DB::table('coas')->whereNull('company_id')->count();
    $company1 = DB::table('coas')->where('company_id', 1)->count();
    
    echo "Hasil:\n";
    echo "  Total COA: $total\n";
    echo "  Company NULL: $nullCount\n";
    echo "  Company 1: $company1\n\n";
    
    if ($nullCount == 0) {
        echo "✓ Berhasil! Semua COA dengan company_id NULL sudah dihapus!\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    
    // Pastikan foreign key checks diaktifkan kembali
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
}
