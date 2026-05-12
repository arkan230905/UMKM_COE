<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ANALISIS STRUKTUR BOP SAAT INI ===\n\n";

// 1. Cek tabel yang ada
$tables = ['bops', 'bop_proses', 'komponen_bops', 'bop_lainnyas', 'beban_operasional'];

foreach ($tables as $table) {
    if (\Schema::hasTable($table)) {
        $count = \Illuminate\Support\Facades\DB::table($table)->count();
        echo "✅ Tabel '$table' ada: $count records\n";
        
        // Show sample data
        if ($count > 0) {
            $sample = \Illuminate\Support\Facades\DB::table($table)->first();
            echo "   Sample: " . json_encode($sample, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "❌ Tabel '$table' tidak ada\n";
    }
    echo "\n";
}

// 2. Analisis data bop_proses yang ada
echo "=== ANALISIS BOP_PROSES ===\n";
$bopProses = \App\Models\BopProses::all();

foreach ($bopProses as $bp) {
    echo "ID: " . $bp->id . "\n";
    echo "User ID: " . $bp->user_id . "\n";
    echo "Proses Produksi ID: " . $bp->proses_produksi_id . "\n";
    echo "Komponen BOP (JSON): " . $bp->komponen_bop . "\n";
    echo "Total BOP per jam: " . $bp->total_bop_per_jam . "\n";
    echo "---\n";
}

// 3. Cek apakah ada relasi ke komponen_bops
echo "\n=== CEK RELASI KE KOMPONEN_BOPS ===\n";
$komponenBops = \App\Models\KomponenBop::all();
echo "Jumlah komponen_bops: " . $komponenBops->count() . "\n";

// 4. Cek bop_lainnyas
echo "\n=== CEK BOP_LAINNYAS ===\n";
if (\Schema::hasTable('bop_lainnyas')) {
    $bopLainnyas = \Illuminate\Support\Facades\DB::table('bop_lainnyas')->get();
    echo "Jumlah bop_lainnyas: " . $bopLainnyas->count() . "\n";
    foreach ($bopLainnyas as $bl) {
        echo "- " . $bl->nama_akun . " (Budget: " . $bl->budget . ")\n";
    }
}

echo "\n=== REKOMENDASI STRUKTUR BOP SEDERHANA ===\n";
echo "Usulkan untuk menggunakan SATU tabel 'bop_proses' yang diperbaiki:\n";
echo "1. Hapus tabel 'bops' (tidak digunakan)\n";
echo "2. Hapus tabel 'komponen_bops' (tidak digunakan)\n";
echo "3. Pertahankan 'bop_proses' dengan struktur yang lebih baik\n";
echo "4. Pertahankan 'bop_lainnyas' untuk BOP lainnya\n";
echo "5. Pertahankan 'beban_operasional' untuk master data\n";
echo "\nStruktur bop_proses yang disarankan:\n";
echo "- id, user_id, proses_produksi_id\n";
echo "- listrik_per_jam, gas_bbm_per_jam, penyusutan_mesin_per_jam\n";
echo "- maintenance_per_jam, gaji_mandor_per_jam, lain_lain_per_jam\n";
echo "- total_bop_per_jam, kapasitas_per_jam, bop_per_unit\n";
echo "- budget, aktual, is_active, created_at, updated_at\n";
echo "- (Hapus kolom komponen_bop JSON karena sudah ada kolom terpisah)\n";
