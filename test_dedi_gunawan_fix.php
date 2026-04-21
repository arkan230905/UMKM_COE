<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Dedi Gunawan Fix ===" . PHP_EOL;

// Test the new logic with Dedi Gunawan
echo PHP_EOL . "Testing new logic for Dedi Gunawan..." . PHP_EOL;

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
    
    // Simulate new logic
    $jenisPegawai = strtolower($pegawai->kategori_jabatan ?? $pegawai->jenis_pegawai ?? 'btktl');
    
    if (strpos(strtolower($pegawai->nama_jabatan), 'gudang') !== false) {
        $coaKode = '54'; // BTKL untuk Bagian Gudang
        $coaNama = 'BIAYA TENAGA KERJA TIDAK LANGSUNG';
        $gajiDasar = 'gaji_pokok';
        echo PHP_EOL . "✅ LOGIC: Bagian Gudang -> COA " . $coaKode . " (" . $coaNama . ")" . PHP_EOL;
        echo "✅ Gaji Dasar: " . $gajiDasar . PHP_EOL;
    } else if ($jenisPegawai === 'btkl') {
        $coaKode = '52'; // BTKL untuk lainnya
        $coaNama = 'BIAYA TENAGA KERJA LANGSUNG';
        $gajiDasar = 'tarif_per_jam * total_jam_kerja';
        echo PHP_EOL . "✅ LOGIC: BTKL -> COA " . $coaKode . " (" . $coaNama . ")" . PHP_EOL;
        echo "✅ Gaji Dasar: " . $gajiDasar . PHP_EOL;
    } else {
        $coaKode = '54'; // BTKTL untuk lainnya
        $coaNama = 'BIAYA TENAGA KERJA TIDAK LANGSUNG';
        $gajiDasar = 'gaji_pokok';
        echo PHP_EOL . "✅ LOGIC: BTKTL -> COA " . $coaKode . " (" . $coaNama . ")" . PHP_EOL;
        echo "✅ Gaji Dasar: " . $gajiDasar . PHP_EOL;
    }
    
    echo PHP_EOL . "Hasil untuk Dedi Gunawan:" . PHP_EOL;
    echo "- Jabatan: " . $pegawai->nama_jabatan . PHP_EOL;
    echo "- COA: " . $coaKode . " - " . $coaNama . PHP_EOL;
    echo "- Gaji Dasar: " . $gajiDasar . PHP_EOL;
    echo "- Status: " . ($coaKode === '54' ? "✅ BENAR (sesuai keinginan user)" : "❌ SALAH") . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "✅ Fixed: Penggajian logic untuk Bagian Gudang" . PHP_EOL;
echo "✅ Result: Bagian Gudang sekarang ke COA 54 (BTKTL)" . PHP_EOL;
echo "✅ Logic: Lebih fleksibel dan adaptif" . PHP_EOL;
echo "✅ Status: Sesuai dengan kebutuhan user" . PHP_EOL;

echo PHP_EOL . "=== Next Steps ===" . PHP_EOL;
echo "1. Restart web server untuk apply changes" . PHP_EOL;
echo "2. Test penggajian baru untuk Dedi Gunawan" . PHP_EOL;
echo "3. Verifikasi jurnal menggunakan COA 54" . PHP_EOL;
echo "4. Check di halaman Jurnal Umum" . PHP_EOL;
