<?php

/**
 * Script untuk mereset SEMUA COA saldo_awal ke 0
 * Jalankan: php reset_all_coa_saldo_awal.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;

echo "🔧 RESET SEMUA COA SALDO AWAL KE 0\n";
echo "=====================================\n\n";

try {
    // Get all COA with non-zero saldo_awal
    $coasWithSaldo = Coa::where('saldo_awal', '!=', 0)->get();
    
    echo "📊 Ditemukan " . $coasWithSaldo->count() . " COA dengan saldo awal tidak nol\n\n";
    
    if ($coasWithSaldo->count() > 0) {
        echo "Daftar COA yang akan direset:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-10s %-40s %15s\n", "Kode", "Nama Akun", "Saldo Awal");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($coasWithSaldo as $coa) {
            printf("%-10s %-40s %15s\n", 
                $coa->kode_akun, 
                substr($coa->nama_akun, 0, 40),
                'Rp ' . number_format($coa->saldo_awal, 0, ',', '.')
            );
        }
        
        echo str_repeat("-", 80) . "\n\n";
        
        // Reset all to 0
        echo "🔄 Mereset semua saldo awal ke 0...\n";
        
        $updated = 0;
        foreach ($coasWithSaldo as $coa) {
            $coa->saldo_awal = 0;
            $coa->save();
            $updated++;
        }
        
        echo "✅ Berhasil mereset {$updated} COA\n\n";
    } else {
        echo "✅ Semua COA sudah memiliki saldo awal = 0\n\n";
    }
    
    // Verify
    echo "🔍 Verifikasi hasil...\n";
    $remaining = Coa::where('saldo_awal', '!=', 0)->count();
    
    if ($remaining == 0) {
        echo "✅ SUKSES: Semua COA saldo_awal = 0\n";
    } else {
        echo "⚠️  WARNING: Masih ada {$remaining} COA dengan saldo awal tidak nol\n";
    }
    
    echo "\n=====================================\n";
    echo "✅ SELESAI\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
