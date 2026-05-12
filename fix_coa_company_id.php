<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Memperbaiki Company ID COA ===\n\n";

// Cek data sebelum
$totalBefore = DB::table('coas')->count();
$nullCompany = DB::table('coas')->whereNull('company_id')->count();
$company1 = DB::table('coas')->where('company_id', 1)->count();

echo "Status sebelum:\n";
echo "  Total: $totalBefore akun\n";
echo "  Company NULL: $nullCompany akun\n";
echo "  Company 1: $company1 akun\n\n";

// Update COA dengan company_id = NULL menjadi company_id = 1
echo "Mengupdate COA dengan company_id = NULL menjadi company_id = 1...\n";
$updated = DB::table('coas')
    ->whereNull('company_id')
    ->update(['company_id' => 1]);
echo "✓ Diupdate: $updated akun\n\n";

// Cek data setelah
$totalAfter = DB::table('coas')->count();
$company1After = DB::table('coas')->where('company_id', 1)->count();

echo "Status setelah:\n";
echo "  Total: $totalAfter akun\n";
echo "  Company 1: $company1After akun\n\n";

// Verifikasi tidak ada company NULL lagi
$stillNull = DB::table('coas')->whereNull('company_id')->count();
if ($stillNull == 0) {
    echo "✓ Tidak ada COA dengan company_id NULL lagi!\n";
} else {
    echo "✗ Masih ada $stillNull COA dengan company_id NULL!\n";
}

echo "\n✓ Perbaikan selesai!\n";
