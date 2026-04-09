<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pembelian;
use App\Services\PembelianJournalService;

echo "=== TEST LOGIKA JURNAL PEMBELIAN ===\n\n";

// Ambil pembelian untuk testing
$pembelian = Pembelian::with([
    'details.bahanBaku',
    'details.bahanPendukung',
    'vendor',
    'kasBank'
])->first();

if (!$pembelian) {
    echo "❌ Tidak ada data pembelian untuk testing!\n";
    exit;
}

echo "📦 TESTING PEMBELIAN:\n";
echo "ID: {$pembelian->id}\n";
echo "Nomor: {$pembelian->nomor_pembelian}\n";
echo "Tanggal: {$pembelian->tanggal}\n";
echo "Vendor: " . ($pembelian->vendor->nama_vendor ?? 'N/A') . "\n";
echo "Subtotal: Rp " . number_format($pembelian->subtotal, 0, ',', '.') . "\n";
echo "PPN: {$pembelian->ppn_persen}% = Rp " . number_format($pembelian->ppn_nominal, 0, ',', '.') . "\n";
echo "Biaya Kirim: Rp " . number_format($pembelian->biaya_kirim, 0, ',', '.') . "\n";
echo "Total: Rp " . number_format($pembelian->total_harga, 0, ',', '.') . "\n";
echo "Metode Bayar: {$pembelian->payment_method}\n";
echo "Bank ID: " . ($pembelian->bank_id ?? 'N/A') . "\n\n";

echo "📋 DETAIL PEMBELIAN:\n";
foreach ($pembelian->details as $index => $detail) {
    echo ($index + 1) . ". ";
    
    if ($detail->bahan_baku_id) {
        echo "Bahan Baku: {$detail->bahanBaku->nama_bahan}";
        if ($detail->bahanBaku->coa_persediaan_id) {
            $coa = \App\Models\Coa::find($detail->bahanBaku->coa_persediaan_id);
            if ($coa) {
                echo " (COA: {$coa->kode_akun} - {$coa->nama_akun})";
            } else {
                echo " (COA: ID {$detail->bahanBaku->coa_persediaan_id} - NOT FOUND)";
            }
        } else {
            echo " (COA: Default 1104)";
        }
    } elseif ($detail->bahan_pendukung_id) {
        echo "Bahan Pendukung: {$detail->bahanPendukung->nama_bahan}";
        if ($detail->bahanPendukung->coa_persediaan_id) {
            $coa = \App\Models\Coa::find($detail->bahanPendukung->coa_persediaan_id);
            if ($coa) {
                echo " (COA: {$coa->kode_akun} - {$coa->nama_akun})";
            } else {
                echo " (COA: ID {$detail->bahanPendukung->coa_persediaan_id} - NOT FOUND)";
            }
        } else {
            echo " (COA: Default 1107)";
        }
    }
    
    $subtotal = $detail->jumlah * $detail->harga_satuan;
    echo "\n   Qty: {$detail->jumlah} {$detail->satuan} x Rp " . number_format($detail->harga_satuan, 0, ',', '.') . " = Rp " . number_format($subtotal, 0, ',', '.') . "\n";
}

// Tampilkan tabel dengan format jurnal tradisional
echo "\n=== JURNAL PEMBELIAN ===\n";
echo "Tanggal: " . $pembelian->tanggal . "\n";
echo "Ref: " . $pembelian->nomor_pembelian . "\n";
echo "Keterangan: Pembelian - " . ($pembelian->vendor->nama_vendor ?? 'Vendor') . "\n\n";

echo sprintf("%-50s %15s %15s\n", 'Nama Akun', 'Debit', 'Credit');
echo str_repeat('-', 80) . "\n";

$totalDebit = 0;
$totalCredit = 0;

// Tampilkan akun debit (rata kiri)
foreach ($pembelian->details as $detail) {
    $amount = $detail->jumlah * $detail->harga_satuan;
    
    if ($detail->bahan_baku_id && $detail->bahanBaku && $detail->bahanBaku->coa_persediaan_id) {
        $coa = \App\Models\Coa::where('kode_akun', $detail->bahanBaku->coa_persediaan_id)->first();
        $namaAkun = $coa ? $coa->nama_akun : 'COA tidak ditemukan';
    } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung && $detail->bahanPendukung->coa_persediaan_id) {
        $coa = \App\Models\Coa::where('kode_akun', $detail->bahanPendukung->coa_persediaan_id)->first();
        $namaAkun = $coa ? $coa->nama_akun : 'COA tidak ditemukan';
    } else {
        $namaAkun = 'Error: Tidak ada COA';
    }
    
    echo sprintf("%-50s %15s %15s\n", 
        $namaAkun,
        'Rp ' . number_format($amount, 0, ',', '.'),
        '-'
    );
    $totalDebit += $amount;
}

// PPN Masukan (Debit)
if ($pembelian->ppn_nominal > 0) {
    echo sprintf("%-50s %15s %15s\n", 
        'PPN Masukan',
        'Rp ' . number_format($pembelian->ppn_nominal, 0, ',', '.'),
        '-'
    );
    $totalDebit += $pembelian->ppn_nominal;
}

// Biaya Kirim (Debit)
if ($pembelian->biaya_kirim > 0) {
    echo sprintf("%-50s %15s %15s\n", 
        'Biaya Kirim',
        'Rp ' . number_format($pembelian->biaya_kirim, 0, ',', '.'),
        '-'
    );
    $totalDebit += $pembelian->biaya_kirim;
}

// Akun kredit (menjorok ke kanan dengan 4 spasi)
$totalCredit = $pembelian->total_harga;
switch ($pembelian->payment_method) {
    case 'cash':
        if ($pembelian->bank_id) {
            $coa = \App\Models\Coa::find($pembelian->bank_id);
            $namaAkunKredit = $coa ? $coa->nama_akun : 'Kas';
        } else {
            $namaAkunKredit = 'Kas';
        }
        break;
    case 'transfer':
        if ($pembelian->bank_id) {
            $coa = \App\Models\Coa::find($pembelian->bank_id);
            $namaAkunKredit = $coa ? $coa->nama_akun : 'Kas Bank';
        } else {
            $namaAkunKredit = 'Kas Bank';
        }
        break;
    case 'credit':
    default:
        $namaAkunKredit = 'Utang Usaha';
        break;
}

// Tampilkan akun kredit dengan indentasi (4 spasi di depan)
echo sprintf("%-50s %15s %15s\n", 
    '    ' . $namaAkunKredit,  // 4 spasi untuk indentasi
    '-',
    'Rp ' . number_format($totalCredit, 0, ',', '.')
);

echo str_repeat('-', 80) . "\n";
echo sprintf("%-50s %15s %15s\n", 'TOTAL', 
    'Rp ' . number_format($totalDebit, 0, ',', '.'), 
    'Rp ' . number_format($totalCredit, 0, ',', '.')
);

// Validasi balance
$isBalanced = abs($totalDebit - $totalCredit) < 0.01;
echo "\n" . ($isBalanced ? '✅ BALANCE' : '❌ NOT BALANCED') . "\n";

if (!$isBalanced) {
    echo "Selisih: Rp " . number_format(abs($totalDebit - $totalCredit), 0, ',', '.') . "\n";
}

echo "\n=== TEST PEMBUATAN JURNAL ===\n";
$confirm = readline("Apakah Anda ingin membuat jurnal untuk pembelian ini? (y/n): ");

if (strtolower($confirm) === 'y') {
    try {
        $journalService = new PembelianJournalService();
        $journal = $journalService->createJournalFromPembelian($pembelian);
        
        if ($journal) {
            echo "✅ BERHASIL! Jurnal dibuat dengan ID: {$journal->id}\n";
            echo "Memo: {$journal->memo}\n";
            echo "Tanggal: {$journal->tanggal}\n";
            
            echo "\nDetail jurnal lines:\n";
            foreach ($journal->lines as $line) {
                $coa = $line->coa;
                echo "- {$coa->kode_akun} {$coa->nama_akun}: ";
                if ($line->debit > 0) {
                    echo "Debit Rp " . number_format($line->debit, 0, ',', '.');
                } else {
                    echo "Credit Rp " . number_format($line->credit, 0, ',', '.');
                }
                echo " ({$line->memo})\n";
            }
        } else {
            echo "❌ Jurnal tidak dibuat (mungkin tidak ada detail)\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "Pembuatan jurnal dibatalkan.\n";
}

echo "\n🎯 KESIMPULAN:\n";
echo "Logika jurnal pembelian telah diimplementasikan dengan fitur:\n";
echo "✅ COA spesifik per bahan baku/pendukung\n";
echo "✅ Handling PPN Masukan\n";
echo "✅ Biaya kirim\n";
echo "✅ Metode pembayaran (cash/transfer/credit)\n";
echo "✅ Bank account spesifik\n";
echo "✅ Validasi balance debit-credit\n";

?>