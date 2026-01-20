<?php

/**
 * Debug Script: Cek Data Kas & Bank
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG: Kas & Bank Dashboard ===\n\n";

// 1. Cek COA Kas & Bank
echo "1. CEK COA KAS & BANK\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$coas = \App\Helpers\AccountHelper::getKasBankAccounts();

if ($coas->isEmpty()) {
    echo "❌ TIDAK ADA COA KAS & BANK!\n";
    echo "   Silakan buat COA dengan kode: 1101, 1102, 1103, 101, atau 102\n\n";
} else {
    echo "✅ Ditemukan " . $coas->count() . " COA Kas & Bank:\n\n";
    
    foreach ($coas as $coa) {
        echo "📦 {$coa->nama_akun}\n";
        echo "   Kode: {$coa->kode_akun}\n";
        echo "   Saldo Awal: Rp " . number_format($coa->saldo_awal, 0, ',', '.') . "\n";
        echo "   Tipe: {$coa->tipe_akun}\n";
        echo "   Header: " . ($coa->is_akun_header ? 'Ya' : 'Tidak') . "\n";
        echo "\n";
    }
}

// 2. Cek Accounts Table
echo "2. CEK ACCOUNTS TABLE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!\Schema::hasTable('accounts')) {
    echo "❌ Table 'accounts' TIDAK ADA!\n\n";
} else {
    $accounts = \DB::table('accounts')->whereIn('code', ['1101', '1102', '1103', '101', '102'])->get();
    
    if ($accounts->isEmpty()) {
        echo "❌ TIDAK ADA DATA di table 'accounts' untuk Kas & Bank\n\n";
    } else {
        echo "✅ Ditemukan " . $accounts->count() . " account:\n\n";
        
        foreach ($accounts as $account) {
            echo "📦 {$account->name}\n";
            echo "   ID: {$account->id}\n";
            echo "   Code: {$account->code}\n";
            echo "\n";
        }
    }
}

// 3. Cek Journal Lines
echo "3. CEK JOURNAL LINES\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!\Schema::hasTable('journal_lines')) {
    echo "❌ Table 'journal_lines' TIDAK ADA!\n\n";
} else {
    $totalJournalLines = \DB::table('journal_lines')->count();
    echo "✅ Total journal_lines: {$totalJournalLines}\n\n";
    
    if ($totalJournalLines > 0 && !$coas->isEmpty()) {
        foreach ($coas as $coa) {
            $account = \DB::table('accounts')->where('code', $coa->kode_akun)->first();
            
            if ($account) {
                $debit = \DB::table('journal_lines')->where('account_id', $account->id)->sum('debit');
                $kredit = \DB::table('journal_lines')->where('account_id', $account->id)->sum('credit');
                $saldo = $coa->saldo_awal + $debit - $kredit;
                
                echo "📦 {$coa->nama_akun} ({$coa->kode_akun})\n";
                echo "   Account ID: {$account->id}\n";
                echo "   Saldo Awal: Rp " . number_format($coa->saldo_awal, 0, ',', '.') . "\n";
                echo "   Total Debit: Rp " . number_format($debit, 0, ',', '.') . "\n";
                echo "   Total Kredit: Rp " . number_format($kredit, 0, ',', '.') . "\n";
                echo "   Saldo Akhir: Rp " . number_format($saldo, 0, ',', '.') . "\n";
                echo "\n";
            } else {
                echo "⚠️  {$coa->nama_akun} ({$coa->kode_akun})\n";
                echo "   TIDAK ADA di table 'accounts'\n\n";
            }
        }
    }
}

// 4. Hitung Total Kas & Bank (seperti di Dashboard)
echo "4. TOTAL KAS & BANK (DASHBOARD)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$total = 0;

if (!$coas->isEmpty()) {
    foreach ($coas as $coa) {
        $saldoAwal = (float)$coa->saldo_awal;
        
        $account = \DB::table('accounts')->where('code', $coa->kode_akun)->first();
        if (!$account) {
            $total += $saldoAwal;
            continue;
        }
        
        if (\Schema::hasTable('journal_lines')) {
            $debit = \DB::table('journal_lines')->where('account_id', $account->id)->sum('debit');
            $kredit = \DB::table('journal_lines')->where('account_id', $account->id)->sum('credit');
            $total += $saldoAwal + (float)$debit - (float)$kredit;
        } else {
            $total += $saldoAwal;
        }
    }
}

echo "💰 Total Kas & Bank: Rp " . number_format(max(0, $total), 0, ',', '.') . "\n\n";

if ($total == 0) {
    echo "⚠️  TOTAL KAS & BANK = 0\n\n";
    echo "KEMUNGKINAN PENYEBAB:\n";
    echo "1. Belum ada COA Kas & Bank dengan kode: 1101, 1102, 1103, 101, 102\n";
    echo "2. Saldo awal COA = 0\n";
    echo "3. Belum ada transaksi yang mempengaruhi Kas & Bank\n";
    echo "4. COA Kas & Bank belum terhubung dengan table 'accounts'\n";
    echo "5. Belum ada journal_lines untuk akun Kas & Bank\n\n";
    
    echo "SOLUSI:\n";
    echo "1. Buat COA Kas & Bank dengan saldo awal\n";
    echo "2. Lakukan transaksi (Penjualan, Pembelian, dll)\n";
    echo "3. Pastikan COA terhubung dengan accounts\n";
    echo "4. Cek journal_lines sudah ter-record\n\n";
}

// 5. Cek Transaksi Penjualan
echo "5. CEK TRANSAKSI PENJUALAN\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$totalPenjualan = \App\Models\Penjualan::count();
$totalNilaiPenjualan = \App\Models\Penjualan::sum('total');

echo "Total Transaksi: {$totalPenjualan}\n";
echo "Total Nilai: Rp " . number_format($totalNilaiPenjualan, 0, ',', '.') . "\n\n";

if ($totalPenjualan > 0) {
    echo "✅ Ada transaksi penjualan\n";
    echo "   Seharusnya mempengaruhi Kas & Bank jika payment_method = 'cash'\n\n";
    
    $penjualanCash = \App\Models\Penjualan::where('payment_method', 'cash')->count();
    $nilaiCash = \App\Models\Penjualan::where('payment_method', 'cash')->sum('total');
    
    echo "   Penjualan Cash: {$penjualanCash} transaksi\n";
    echo "   Nilai Cash: Rp " . number_format($nilaiCash, 0, ',', '.') . "\n\n";
} else {
    echo "⚠️  Belum ada transaksi penjualan\n\n";
}

// 6. Cek Transaksi Pembelian
echo "6. CEK TRANSAKSI PEMBELIAN\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$totalPembelian = \App\Models\Pembelian::count();
$totalNilaiPembelian = \App\Models\Pembelian::sum('total');

echo "Total Transaksi: {$totalPembelian}\n";
echo "Total Nilai: Rp " . number_format($totalNilaiPembelian, 0, ',', '.') . "\n\n";

if ($totalPembelian > 0) {
    echo "✅ Ada transaksi pembelian\n";
    echo "   Seharusnya mempengaruhi Kas & Bank jika payment_method = 'cash'\n\n";
    
    $pembelianCash = \App\Models\Pembelian::where('payment_method', 'cash')->count();
    $nilaiCash = \App\Models\Pembelian::where('payment_method', 'cash')->sum('total');
    
    echo "   Pembelian Cash: {$pembelianCash} transaksi\n";
    echo "   Nilai Cash: Rp " . number_format($nilaiCash, 0, ',', '.') . "\n\n";
} else {
    echo "⚠️  Belum ada transaksi pembelian\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ DEBUG SELESAI\n\n";

if ($total == 0 && $totalPenjualan > 0) {
    echo "⚠️  PERHATIAN:\n";
    echo "Ada transaksi penjualan tapi Kas & Bank = 0\n";
    echo "Kemungkinan:\n";
    echo "1. Transaksi belum ter-record ke journal_lines\n";
    echo "2. COA Kas & Bank belum terhubung dengan accounts\n";
    echo "3. Saldo awal COA = 0 dan belum ada mutasi\n\n";
}
