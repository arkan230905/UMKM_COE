<?php

/**
 * Script untuk menganalisis dan memastikan neraca saldo balance
 * Memeriksa semua jurnal umum per user dan memastikan debit = kredit
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ANALISIS NERACA SALDO PER USER ===\n\n";

// 1. Ambil semua user yang memiliki data
$users = DB::table('users')
    ->select('id', 'name', 'email')
    ->get();

echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo str_repeat("=", 100) . "\n";
    echo "USER: {$user->name} (ID: {$user->id})\n";
    echo str_repeat("=", 100) . "\n\n";
    
    // 2. Cek jurnal umum user ini
    $journals = DB::table('jurnal_umum')
        ->where('user_id', $user->id)
        ->get();
    
    if ($journals->isEmpty()) {
        echo "⚠️  User ini belum memiliki jurnal umum.\n\n";
        continue;
    }
    
    echo "Total jurnal entries: " . $journals->count() . "\n";
    
    // 3. Hitung total debit dan kredit
    $totalDebit = $journals->sum('debit');
    $totalKredit = $journals->sum('kredit');
    $selisih = abs($totalDebit - $totalKredit);
    
    echo "Total Debit  : Rp " . number_format($totalDebit, 2, ',', '.') . "\n";
    echo "Total Kredit : Rp " . number_format($totalKredit, 2, ',', '.') . "\n";
    echo "Selisih      : Rp " . number_format($selisih, 2, ',', '.') . "\n";
    
    if ($selisih < 0.01) {
        echo "✅ BALANCED - Neraca saldo seimbang!\n\n";
    } else {
        echo "❌ NOT BALANCED - Ada selisih!\n\n";
        
        // 4. Analisis per tipe referensi
        echo "Analisis per tipe referensi:\n";
        echo str_repeat("-", 100) . "\n";
        printf("%-30s %-15s %-15s %-15s %-10s\n", 
            "Tipe Referensi", "Total Debit", "Total Kredit", "Selisih", "Status");
        echo str_repeat("-", 100) . "\n";
        
        $tipeReferensi = DB::table('jurnal_umum')
            ->where('user_id', $user->id)
            ->select('tipe_referensi', 
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(kredit) as total_kredit'))
            ->groupBy('tipe_referensi')
            ->get();
        
        foreach ($tipeReferensi as $tipe) {
            $debit = $tipe->total_debit ?? 0;
            $kredit = $tipe->total_kredit ?? 0;
            $diff = abs($debit - $kredit);
            $status = $diff < 0.01 ? '✅' : '❌';
            
            printf("%-30s %-15s %-15s %-15s %-10s\n",
                $tipe->tipe_referensi ?? 'NULL',
                number_format($debit, 0, ',', '.'),
                number_format($kredit, 0, ',', '.'),
                number_format($diff, 0, ',', '.'),
                $status
            );
        }
        echo str_repeat("-", 100) . "\n\n";
        
        // 5. Cari transaksi yang tidak balance
        echo "Transaksi yang tidak balance:\n";
        echo str_repeat("-", 100) . "\n";
        printf("%-15s %-15s %-30s %-15s %-15s %-15s\n", 
            "Referensi", "Tipe", "Tanggal", "Debit", "Kredit", "Selisih");
        echo str_repeat("-", 100) . "\n";
        
        $transaksiGroups = DB::table('jurnal_umum')
            ->where('user_id', $user->id)
            ->select('referensi', 'tipe_referensi', 'tanggal',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(kredit) as total_kredit'))
            ->groupBy('referensi', 'tipe_referensi', 'tanggal')
            ->get();
        
        $unbalancedCount = 0;
        foreach ($transaksiGroups as $trans) {
            $debit = $trans->total_debit ?? 0;
            $kredit = $trans->total_kredit ?? 0;
            $diff = abs($debit - $kredit);
            
            if ($diff >= 0.01) {
                printf("%-15s %-15s %-30s %-15s %-15s %-15s\n",
                    $trans->referensi ?? 'NULL',
                    substr($trans->tipe_referensi ?? 'NULL', 0, 15),
                    $trans->tanggal,
                    number_format($debit, 0, ',', '.'),
                    number_format($kredit, 0, ',', '.'),
                    number_format($diff, 0, ',', '.')
                );
                $unbalancedCount++;
            }
        }
        
        if ($unbalancedCount == 0) {
            echo "✅ Semua transaksi individual sudah balance.\n";
            echo "⚠️  Tapi total keseluruhan tidak balance - kemungkinan ada jurnal manual yang salah.\n";
        } else {
            echo "\nTotal transaksi tidak balance: $unbalancedCount\n";
        }
        echo str_repeat("-", 100) . "\n\n";
    }
    
    // 6. Tampilkan ringkasan per COA
    echo "Ringkasan per COA:\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-10s %-30s %-15s %-15s %-15s\n", 
        "Kode", "Nama Akun", "Total Debit", "Total Kredit", "Saldo");
    echo str_repeat("-", 100) . "\n";
    
    $coaSummary = DB::table('jurnal_umum as ju')
        ->join('coas as c', 'ju.coa_id', '=', 'c.id')
        ->where('ju.user_id', $user->id)
        ->select('c.kode_akun', 'c.nama_akun',
            DB::raw('SUM(ju.debit) as total_debit'),
            DB::raw('SUM(ju.kredit) as total_kredit'))
        ->groupBy('c.kode_akun', 'c.nama_akun')
        ->orderBy('c.kode_akun')
        ->get();
    
    foreach ($coaSummary as $coa) {
        $debit = $coa->total_debit ?? 0;
        $kredit = $coa->total_kredit ?? 0;
        $saldo = $debit - $kredit;
        
        printf("%-10s %-30s %-15s %-15s %-15s\n",
            $coa->kode_akun,
            substr($coa->nama_akun, 0, 30),
            number_format($debit, 0, ',', '.'),
            number_format($kredit, 0, ',', '.'),
            number_format($saldo, 0, ',', '.')
        );
    }
    echo str_repeat("-", 100) . "\n\n";
}

echo "\n=== SELESAI ===\n";
echo "Jika ada user yang tidak balance, periksa transaksi yang ditandai ❌\n";
echo "Kemungkinan penyebab:\n";
echo "1. Jurnal manual yang salah input\n";
echo "2. Transaksi yang tidak lengkap (hanya debit atau hanya kredit)\n";
echo "3. Bug di kode yang membuat jurnal\n\n";
