<?php
/**
 * AUDIT SCRIPT: Multi-Tenant Data Isolation Check
 * 
 * Script ini mengaudit semua controller untuk memastikan tidak ada kebocoran data
 * multi-tenant pada operasi CRUD (Create, Read, Update, Delete)
 * 
 * Halaman yang diaudit:
 * 1. Bahan Pendukung
 * 2. Produk
 * 3. Perhitungan Biaya Bahan Baku
 * 4. BTKL (Proses Produksi)
 * 5. BOP (Biaya Overhead Pabrik)
 * 6. Harga Pokok Produksi
 * 7. Produksi
 * 8. Pembelian
 * 9. Penjualan
 * 10. Presensi
 * 11. Penggajian
 * 12. Pembayaran Beban
 * 13. Pelunasan Utang
 * 14. Laporan Pembelian
 * 15. Laporan Stok
 * 16. Laporan Penjualan
 * 17. Laporan Penggajian
 * 18. Laporan Pembayaran Beban
 * 19. Laporan Pelunasan Utang
 * 20. Laporan Kas dan Bank
 * 21. Jurnal Umum
 * 22. Buku Besar
 * 23. Neraca Saldo
 * 24. Laporan Posisi Keuangan
 * 25. Laba Rugi
 * 26. Tentang Perusahaan
 * 27. Profil
 * 28. Kelola CATALOG
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== AUDIT MULTI-TENANT DATA ISOLATION ===\n\n";

// Daftar controller dan method yang perlu diaudit
$controllersToAudit = [
    'BahanPendukungController' => ['index', 'store', 'update', 'destroy'],
    'ProdukController' => ['index', 'store', 'update', 'destroy', 'katalogPelanggan'],
    'BiayaBahanController' => ['index', 'store', 'update'],
    'ProsesProduksiController' => ['index', 'store', 'update', 'destroy'],
    'BopController' => ['index', 'store', 'update', 'destroy'],
    'BomController' => ['index', 'store', 'update', 'destroy'],
    'ProduksiController' => ['index', 'store', 'update', 'destroy'],
    'PembelianController' => ['index', 'store', 'update', 'destroy'],
    'PenjualanController' => ['index', 'store', 'update', 'destroy'],
    'PresensiController' => ['index', 'store', 'update', 'destroy'],
    'PenggajianController' => ['index', 'store', 'update', 'destroy'],
    'ExpensePaymentController' => ['index', 'store'],
    'PelunasanUtangController' => ['index', 'store'],
    'LaporanController' => ['pembelian', 'stok', 'penjualan', 'penggajian', 'pembayaranBeban', 'pelunasanUtang', 'kasBank'],
    'JurnalController' => ['index'],
    'BukuBesarController' => ['index'],
    'NeracaSaldoController' => ['index'],
    'LaporanKeuanganController' => ['posisiKeuangan', 'labaRugi'],
    'PerusahaanController' => ['index', 'update'],
    'ProfilController' => ['index', 'update'],
    'CatalogController' => ['index', 'update'],
];

// Check models untuk user_id di fillable
$modelsToCheck = [
    'BahanPendukung',
    'Produk',
    'Bop',
    'ProsesProduksi',
    'Bom',
    'Produksi',
    'Pembelian',
    'Penjualan',
    'Presensi',
    'Penggajian',
    'ExpensePayment',
    'PelunasanUtang',
    'Perusahaan',
];

echo "1. CHECKING MODELS FOR user_id IN FILLABLE\n";
echo str_repeat("=", 80) . "\n";

foreach ($modelsToCheck as $modelName) {
    $modelClass = "App\\Models\\{$modelName}";
    
    if (!class_exists($modelClass)) {
        echo "❌ {$modelName}: Model not found\n";
        continue;
    }
    
    $model = new $modelClass();
    $fillable = $model->getFillable();
    
    if (in_array('user_id', $fillable)) {
        echo "✅ {$modelName}: user_id in fillable\n";
    } else {
        echo "⚠️  {$modelName}: user_id NOT in fillable - NEEDS FIX\n";
    }
}

echo "\n2. CHECKING DATABASE TABLES FOR user_id COLUMN\n";
echo str_repeat("=", 80) . "\n";

$tablesToCheck = [
    'bahan_pendukungs',
    'produks',
    'bops',
    'proses_produksis',
    'boms',
    'produksis',
    'pembelians',
    'penjualans',
    'presensis',
    'penggajians',
    'expense_payments',
    'pelunasan_utangs',
    'perusahaans',
];

foreach ($tablesToCheck as $table) {
    try {
        $columns = DB::select("SHOW COLUMNS FROM {$table}");
        $hasUserId = false;
        
        foreach ($columns as $column) {
            if ($column->Field === 'user_id') {
                $hasUserId = true;
                break;
            }
        }
        
        if ($hasUserId) {
            echo "✅ {$table}: has user_id column\n";
        } else {
            echo "⚠️  {$table}: NO user_id column - NEEDS MIGRATION\n";
        }
    } catch (\Exception $e) {
        echo "❌ {$table}: Error checking - {$e->getMessage()}\n";
    }
}

echo "\n3. CHECKING FOR ORPHANED DATA (records without user_id)\n";
echo str_repeat("=", 80) . "\n";

foreach ($tablesToCheck as $table) {
    try {
        $count = DB::table($table)->whereNull('user_id')->count();
        
        if ($count > 0) {
            echo "⚠️  {$table}: {$count} orphaned records (user_id IS NULL)\n";
        } else {
            echo "✅ {$table}: No orphaned records\n";
        }
    } catch (\Exception $e) {
        // Table might not have user_id column yet
        echo "⚠️  {$table}: Cannot check - {$e->getMessage()}\n";
    }
}

echo "\n4. SUMMARY\n";
echo str_repeat("=", 80) . "\n";
echo "Audit completed. Please review the results above.\n";
echo "\nACTIONS REQUIRED:\n";
echo "1. Add user_id to fillable in models marked with ⚠️\n";
echo "2. Create migrations for tables without user_id column\n";
echo "3. Run fix_orphaned_data.sql to assign orphaned records to correct users\n";
echo "4. Manually audit controller code to ensure:\n";
echo "   - index() methods use ->where('user_id', auth()->id())\n";
echo "   - store() methods add 'user_id' => auth()->id()\n";
echo "   - update() methods don't allow changing user_id\n";
echo "   - destroy() methods check ownership before deleting\n";
echo "\n";
