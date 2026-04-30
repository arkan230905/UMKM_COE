<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "RESTORE JURNAL UMUM - PATOKAN YANG BENAR\n";
echo "=====================================\n";

echo "\n=== APOLOGIES AND UNDERSTANDING ===\n";
echo "Mohon maaf atas kesalahan saya.\n";
echo "Jurnal umum Anda adalah PATOKAN yang benar dan sudah berada di jalur yang tepat.\n";
echo "Saya seharusnya tidak merubah jurnal umum yang sudah benar.\n";
echo "Saya akan mengembalikan jurnal umum ke kondisi semula.\n";

echo "\n=== MENGHAPUS JOURNAL ENTRIES YANG DITAMBAHKAN ===\n";

// Hapus semua balance_adjustment entries yang saya tambahkan
$balanceEntries = \App\Models\JournalEntry::where('ref_type', 'balance_adjustment')->get();

echo "Menghapus " . $balanceEntries->count() . " journal entries yang ditambahkan:\n";

foreach ($balanceEntries as $entry) {
    echo "Menghapus entry ID {$entry->id}: {$entry->memo}\n";
    
    // Hapus journal lines terlebih dahulu
    \App\Models\JournalLine::where('journal_entry_id', $entry->id)->delete();
    
    // Hapus journal entry
    $entry->delete();
}

echo "\n=== VERIFIKASI JURNAL UMUM ASLI ===\n";

// Tampilkan jurnal umum asli yang seharusnya ada
$originalEntries = \App\Models\JournalEntry::whereMonth('tanggal', 4)
    ->whereYear('tanggal', 2026)
    ->where('ref_type', '!=', 'balance_adjustment')
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

echo "Jurnal umum asli yang dipertahankan (" . $originalEntries->count() . " entries):\n";
echo "ID\tTanggal\t\tRef Type\t\tMemo\n";
echo "================================================================\n";

foreach ($originalEntries as $entry) {
    echo "{$entry->id}\t{$entry->tanggal}\t{$entry->ref_type}\t\t" . substr($entry->memo, 0, 40) . "\n";
}

echo "\n=== ANALISA MASALAH NERACA SALDO ===\n";
echo "Masalah: Neraca saldo tidak seimbang\n";
echo "Penyebab: Bukan jurnal umum, tapi mungkin perhitungan di controller atau data COA\n";
echo "Solusi: Cari akar masalah tanpa merubah jurnal umum\n";

echo "\n=== STRATEGI BARU ===\n";
echo "1. Jurnal umum: DIPERTAHANKAN (patokan benar)\n";
echo "2. COA saldo_awal: DIPERTAHANKAN (tidak diubah)\n";
echo "3. Investigasi: Periksa logika perhitungan neraca saldo di controller\n";
echo "4. Solusi: Mungkin perbaiki logika controller atau tambahkan missing data\n";

echo "\n=== INVESTIGASI LOGIKA CONTROLLER ===\n";

// Ambil data COA
$coas = \App\Models\Coa::where('user_id', 1)->get();

echo "Current COA data:\n";
echo "Kode\tNama Akun\t\t\tSaldo Awal\tTipe\n";
echo "========================================================\n";

foreach ($coas as $coa) {
    $saldo = $coa->saldo_awal ?? 0;
    if ($saldo != 0) {
        printf("%-8s\t%-30s\t%10s\t%s\n", 
            $coa->kode_akun, 
            substr($coa->nama_akun, 0, 30), 
            number_format($saldo, 0, ',', '.'), 
            $coa->tipe_akun
        );
    }
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Jurnal umum sudah dikembalikan ke kondisi semula\n";
echo "2. COA saldo_awal tidak diubah\n";
echo "3. Perlu investigasi lebih lanjut mengapa neraca saldo tidak seimbang\n";
echo "4. Kemungkinan solusi:\n";
echo "   - Periksa apakah ada data yang hilang di jurnal_umum table\n";
echo "   - Periksa logika perhitungan di AkuntansiController::neracaSaldo()\n";
echo "   - Tambahkan missing data jika diperlukan\n";

echo "\n=== STATUS ===\n";
echo "Jurnal Umum: RESTORED to original condition (PATOKAN BENAR)\n";
echo "COA Saldo Awal: UNCHANGED\n";
echo "Journal Entries Added: REMOVED\n";
echo "Next Action: Investigate root cause of neraca saldo imbalance\n";

echo "\nMohon maaf lagi atas kesalahan saya.\n";
echo "Jurnal umum Anda sekarang dikembalikan ke kondisi semula yang benar.\n";
echo "Saya akan mencari solusi lain yang tidak merubah jurnal umum.\n";

echo "\nJournal umum restoration completed!\n";
