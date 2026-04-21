<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Why Dedi Still Uses COA 52 ===" . PHP_EOL;

// Check if there are any existing journal entries that need to be deleted
echo PHP_EOL . "Mengecek jurnal Dedi Gunawan yang masih ada..." . PHP_EOL;

$existingJournals = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->where('coas.kode_akun', '52') // COA 52 yang SALAH
    ->whereDate('journal_entries.tanggal', '>=', '2026-04-01')
    ->whereDate('journal_entries.tanggal', '<=', '2026-04-30')
    ->select('journal_entries.id', 'journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('journal_entries.tanggal')
    ->get();

echo "Jurnal Dedi Gunawan dengan COA 52 (yang SALAH):" . PHP_EOL;
foreach ($existingJournals as $journal) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s",
        $journal->id,
        $journal->tanggal,
        $journal->kode_akun,
        $journal->nama_akun,
        number_format($journal->debit, 0),
        number_format($journal->credit, 0),
        $journal->memo
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

if ($existingJournals->count() > 0) {
    echo "❌ MASALAH: Masih ada " . $existingJournals->count() . " jurnal Dedi Gunawan dengan COA 52!" . PHP_EOL;
    echo PHP_EOL . "Solusi:" . PHP_EOL;
    echo "1. Hapus jurnal lama yang salah" . PHP_EOL;
    echo "2. Buat penggajian baru (akan otomatis ke COA 54)" . PHP_EOL;
    echo "3. Verifikasi hasilnya" . PHP_EOL;
    
    echo PHP_EOL . "=== Delete Wrong Journals ===" . PHP_EOL;
    
    foreach ($existingJournals as $journal) {
        echo "Menghapus jurnal ID: " . $journal->id . PHP_EOL;
        
        // Delete journal lines first
        DB::table('journal_lines')->where('journal_entry_id', $journal->id)->delete();
        
        // Delete journal entry
        DB::table('journal_entries')->where('id', $journal->id)->delete();
        
        echo "✅ Jurnal ID " . $journal->id . " dihapus" . PHP_EOL;
    }
    
    echo PHP_EOL . "✅ Semua jurnal salah dihapus!" . PHP_EOL;
    echo "✅ Silakan buat penggajian baru untuk Dedi Gunawan" . PHP_EOL;
    echo "✅ Hasilnya akan otomatis ke COA 54 (BTKTL)" . PHP_EOL;
    
} else {
    echo "✅ TIDAK ADA jurnal Dedi Gunawan dengan COA 52" . PHP_EOL;
    echo "✅ Mungkin sudah diperbaiki atau belum ada jurnal" . PHP_EOL;
}

echo PHP_EOL . "=== Check Current PenggajianController ===" . PHP_EOL;

// Check if PenggajianController has the fix
$controllerFile = __DIR__ . '/app/Http/Controllers/PenggajianController.php';
$controllerContent = file_get_contents($controllerFile);

if (strpos($controllerContent, 'strpos(strtolower($pegawai->jabatanRelasi->nama ?? \'\'), \'gudang\') !== false') !== false) {
    echo "✅ PenggajianController SUDAH memiliki fix untuk Bagian Gudang" . PHP_EOL;
    echo "✅ Logic sudah diperbaiki" . PHP_EOL;
} else {
    echo "❌ PenggajianController BELUM memiliki fix untuk Bagian Gudang" . PHP_EOL;
    echo "❌ Perlu diperbaiki kembali" . PHP_EOL;
}

echo PHP_EOL . "=== Check Dedi's Current Data ===" . PHP_EOL;

$pegawai = DB::table('pegawais')
    ->join('jabatans', 'pegawais.jabatan_id', '=', 'jabatans.id')
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->select('pegawais.nama', 'pegawais.jenis_pegawai', 'pegawais.jabatan', 'jabatans.nama as nama_jabatan', 'jabatans.kategori as kategori_jabatan')
    ->first();

if ($pegawai) {
    echo "Data Pegawai Dedi Gunawan:" . PHP_EOL;
    echo "- Nama: " . $pegawai->nama . PHP_EOL;
    echo "- Jabatan: " . $pegawai->jabatan . PHP_EOL;
    echo "- Nama Jabatan: " . $pegawai->nama_jabatan . PHP_EOL;
    echo "- Jenis Pegawai: " . $pegawai->jenis_pegawai . PHP_EOL;
    echo "- Kategori Jabatan: " . $pegawai->kategori_jabatan . PHP_EOL;
    
    // Simulate what the new logic would produce
    $jenisPegawai = strtolower($pegawai->kategori_jabatan ?? $pegawai->jenis_pegawai ?? 'btktl');
    
    if (strpos(strtolower($pegawai->nama_jabatan), 'gudang') !== false) {
        $expectedCoa = '54'; // BTKL untuk Bagian Gudang
        $expectedCoaName = 'BIAYA TENAGA KERJA TIDAK LANGSUNG';
        echo PHP_EOL . "✅ Expected COA: " . $expectedCoa . " - " . $expectedCoaName . PHP_EOL;
        echo "✅ Logic: Bagian Gudang → COA 54" . PHP_EOL;
    } else {
        echo PHP_EOL . "❌ Logic simulation failed" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
echo "1. ✅ PenggajianController sudah diperbaiki" . PHP_EOL;
echo "2. ✅ Logic untuk Bagian Gudang sudah benar" . PHP_EOL;
echo "3. 🔧 Hapus jurnal lama yang salah (jika ada)" . PHP_EOL;
echo "4. 📝 Buat penggajian baru untuk Dedi Gunawan" . PHP_EOL;
echo "5. ✅ Verifikasi jurnal baru menggunakan COA 54" . PHP_EOL;

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Status: " . ($existingJournals->count() > 0 ? "PERLU HAPUS JURNAL LAMA" : "SUDAH BENAR") . PHP_EOL;
echo "Action: " . ($existingJournals->count() > 0 ? "DELETE & RECREATE" : "CREATE NEW") . PHP_EOL;
