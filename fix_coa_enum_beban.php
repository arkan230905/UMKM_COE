<?php
/**
 * Script untuk memperbaiki enum tipe_akun COA yang menyebabkan error "BEBAN"
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

try {
    // Load Laravel application
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "🔧 Memperbaiki enum tipe_akun untuk COA...\n\n";

    // Step 1: Update existing 'BEBAN' values to 'Expense'
    echo "Step 1: Mengupdate nilai 'BEBAN' menjadi 'Expense'...\n";
    $updated = DB::table('coas')->where('tipe_akun', 'BEBAN')->update(['tipe_akun' => 'Expense']);
    echo "✅ Updated $updated records from 'BEBAN' to 'Expense'\n\n";

    // Step 2: Check current enum values
    echo "Step 2: Memeriksa struktur tabel saat ini...\n";
    $result = DB::select("SHOW COLUMNS FROM coas LIKE 'tipe_akun'");
    if (!empty($result)) {
        echo "Current enum: " . $result[0]->Type . "\n\n";
    }

    // Step 3: Alter table to include all possible enum values
    echo "Step 3: Mengupdate enum untuk mendukung semua nilai...\n";
    
    $sql = "ALTER TABLE coas MODIFY COLUMN tipe_akun ENUM(
        'Asset', 'Aset',
        'Liability', 'Kewajiban', 
        'Equity', 'Ekuitas', 'Modal',
        'Revenue', 'Pendapatan',
        'Expense', 'Beban', 'BEBAN', 'Biaya',
        'Biaya Bahan Baku', 'Biaya Tenaga Kerja Langsung', 
        'Biaya Overhead Pabrik', 'Biaya Tenaga Kerja Tidak Langsung', 
        'BOP Tidak Langsung Lainnya'
    ) NOT NULL";
    
    DB::statement($sql);
    echo "✅ Enum berhasil diupdate\n\n";

    // Step 4: Verify the fix
    echo "Step 4: Verifikasi perbaikan...\n";
    $result = DB::select("SHOW COLUMNS FROM coas LIKE 'tipe_akun'");
    if (!empty($result)) {
        echo "New enum: " . $result[0]->Type . "\n\n";
    }

    // Step 5: Test update that was failing
    echo "Step 5: Testing update yang sebelumnya gagal...\n";
    $testUpdate = DB::table('coas')
        ->where('id', 166)
        ->update([
            'nama_akun' => 'Biaya TENAGA KERJA TIDAK LANGSUNG',
            'tipe_akun' => 'BEBAN',
            'tanggal_saldo_awal' => '2026-04-01 00:00:00'
        ]);
    
    if ($testUpdate) {
        echo "✅ Test update berhasil!\n";
    } else {
        echo "❌ Test update gagal\n";
    }

    echo "\n🎉 SELESAI!\n";
    echo "Enum tipe_akun sudah diperbaiki dan sekarang mendukung nilai 'BEBAN'.\n";
    echo "Anda dapat melanjutkan edit COA tanpa error.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>