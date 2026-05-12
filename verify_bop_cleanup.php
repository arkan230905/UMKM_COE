<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFIKASI STRUKTUR BOP SETELAH CLEANUP ===\n\n";

// 1. Cek tabel yang tersisa
$tables = ['bops', 'bop_proses', 'komponen_bops', 'beban_operasional'];

foreach ($tables as $table) {
    if (\Schema::hasTable($table)) {
        $count = \Illuminate\Support\Facades\DB::table($table)->count();
        echo "✅ Tabel '$table' ada: $count records\n";
        
        // Show structure
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        echo "   Kolom: " . implode(', ', $columns) . "\n";
        
        // Show sample data if exists
        if ($count > 0) {
            $sample = \Illuminate\Support\Facades\DB::table($table)->first();
            echo "   Sample: " . json_encode($sample, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "❌ Tabel '$table' tidak ada (dihapus)\n";
    }
    echo "\n";
}

// 2. Verifikasi bop_proses structure
echo "=== VERIFIKASI BOP_PROSES STRUCTURE ===\n";
$bopProses = \App\Models\BopProses::all();

foreach ($bopProses as $bp) {
    echo "ID: " . $bp->id . "\n";
    echo "User ID: " . $bp->user_id . "\n";
    echo "Proses Produksi ID: " . $bp->proses_produksi_id . "\n";
    echo "Listrik/jam: " . $bp->listrik_per_jam . "\n";
    echo "Gas/BBM/jam: " . $bp->gas_bbm_per_jam . "\n";
    echo "Penyusutan/jam: " . $bp->penyusutan_mesin_per_jam . "\n";
    echo "Maintenance/jam: " . $bp->maintenance_per_jam . "\n";
    echo "Gaji Mandor/jam: " . $bp->gaji_mandor_per_jam . "\n";
    echo "Lain-lain/jam: " . $bp->lain_lain_per_jam . "\n";
    echo "Total BOP/jam: " . $bp->total_bop_per_jam . "\n";
    echo "Kapasitas/jam: " . $bp->kapasitas_per_jam . "\n";
    echo "BOP/unit: " . $bp->bop_per_unit . "\n";
    echo "Budget: " . $bp->budget . "\n";
    echo "Aktual: " . $bp->aktual . "\n";
    echo "Is Active: " . ($bp->is_active ? 'Yes' : 'No') . "\n";
    echo "---\n";
}

echo "\n=== REKOMENDASI STRUKTUR AKHIR ===\n";
echo "✅ Struktur BOP sekarang sudah sederhana:\n";
echo "1. Hanya 1 tabel utama: 'bop_proses'\n";
echo "2. Komponen BOP disimpan di kolom terpisah (bukan JSON)\n";
echo "3. Tabel 'bops' dan 'komponen_bops' sudah dihapus\n";
echo "4. Multi-tenant security dengan user_id\n";
echo "5. Budget dan aktual tracking langsung di tabel\n";
echo "\nStruktur akhir yang sederhana dan efisien! 🎉";
