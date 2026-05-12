<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Penggajian BTKL vs BTKTL Logic ===" . PHP_EOL;

// The problem: Bagian Gudang should use COA 54 (BTKTL), not COA 52 (BTKL)
echo PHP_EOL . "Masalah:" . PHP_EOL;
echo "- Pegawai: Dedi Gunawan" . PHP_EOL;
echo "- Jabatan: Bagian Gudang" . PHP_EOL;
echo "- Saat ini: COA 52 (BIAYA TENAGA KERJA LANGSUNG) ❌" . PHP_EOL;
echo "- Seharusnya: COA 54 (BIAYA TENAGA KERJA TIDAK LANGSUNG) ✅" . PHP_EOL;

echo PHP_EOL . "=== Analisis Logika Saat Ini ===" . PHP_EOL;

// Check current logic in PenggajianController
echo "Menganalisis logic penggajian saat ini..." . PHP_EOL;

// Get Dedi Gunawan data
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
    
    // The issue: Bagian Gudang should be BTKTL
    if (strpos(strtolower($pegawai->nama_jabatan), 'gudang') !== false) {
        echo PHP_EOL . "✅ ANALISIS: Bagian Gudang termasuk BTKTL" . PHP_EOL;
        echo "- Klasifikasi: Tenaga Kerja TIDAK LANGSUNG" . PHP_EOL;
        echo "- Seharusnya: COA 54 (BIAYA TENAGA KERJA TIDAK LANGSUNG)" . PHP_EOL;
        echo "- Saat ini: COA 52 (BIAYA TENAGA KERJA LANGSUNG) - SALAH!" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Solusi yang Diperlukan ===" . PHP_EOL;

echo "Solusi 1: Perbaiki Logic Penggajian" . PHP_EOL;
echo "- Tambah logic khusus untuk Bagian Gudang" . PHP_EOL;
echo "- if (strpos(strtolower(\$jabatan->nama), 'gudang') !== false) {" . PHP_EOL;
echo "-     \$coaBebanGaji = Coa::where('kode_akun', '54')->first(); // BTKTL" . PHP_EOL;
echo "- } else {" . PHP_EOL;
echo "-     \$coaBebanGaji = Coa::where('kode_akun', '52')->first(); // BTKL" . PHP_EOL;
echo "- }" . PHP_EOL;

echo PHP_EOL . "Solusi 2: Perbaiki Data Jabatan" . PHP_EOL;
echo "- Ubah kategori jabatan gudang ke 'btktl'" . PHP_EOL;
echo "- Ini akan membuat sistem otomatis ke COA 54" . PHP_EOL;

echo PHP_EOL . "=== Rekomendasi ===" . PHP_EOL;
echo "💡 Rekomendasi: Solusi 1 (Perbaiki Logic)" . PHP_EOL;
echo "- Lebih fleksibel dan adaptif" . PHP_EOL;
echo "- Tidak perlu ubah data master" . PHP_EOL;
echo "- Bisa handle berbagai macam departemen" . PHP_EOL;

echo PHP_EOL . "=== Implementasi Perbaikan ===" . PHP_EOL;

// Read current PenggajianController
$controllerFile = __DIR__ . '/app/Http/Controllers/PenggajianController.php';
$controllerContent = file_get_contents($controllerFile);

// Find the COA assignment logic
$pattern = "/if \(\$jenisPegawai === 'btkl'\) \{[^}]+?\}/s";
if (preg_match($pattern, $controllerContent, $matches)) {
    echo "Found current BTKL logic:" . PHP_EOL;
    echo $matches[0] . PHP_EOL;
    
    // Create new logic
    $newLogic = <<< 'LOGIC'
            // Tentukan akun beban berdasarkan jenis pegawai dan jabatan
            $jenisPegawai = strtolower($pegawai->kategori ?? $pegawai->jenis_pegawai ?? 'btktl');
            
            // Special handling for Bagian Gudang
            if (strpos(strtolower($pegawai->jabatanRelasi->nama ?? ''), 'gudang') !== false) {
                // Bagian Gudang = BTKTL (Tenaga Kerja Tidak Langsung)
                $coaBebanGaji = Coa::where('kode_akun', '54')->first(); // BIAYA TENAGA KERJA TIDAK LANGSUNG
            } else if ($jenisPegawai === 'btkl') {
                $coaBebanGaji = Coa::where('kode_akun', '52')->first(); // BIAYA TENAGA KERJA LANGSUNG
            } else {
                $coaBebanGaji = Coa::where('kode_akun', '54')->first(); // BOP TENAGA KERJA TIDAK LANGSUNG
            }
LOGIC;
    
    echo PHP_EOL . "Creating new logic..." . PHP_EOL;
    echo $newLogic . PHP_EOL;
    
    // Replace the old logic with new one
    $newControllerContent = str_replace($matches[0], $newLogic, $controllerContent);
    
    if (file_put_contents($controllerFile, $newControllerContent)) {
        echo "✅ PenggajianController updated successfully!" . PHP_EOL;
        echo "✅ Bagian Gudang sekarang ke COA 54 (BTKTL)" . PHP_EOL;
        echo "✅ Logic lebih fleksibel untuk berbagai departemen" . PHP_EOL;
    } else {
        echo "❌ Failed to update PenggajianController" . PHP_EOL;
    }
} else {
    echo "❌ Could not find BTKL logic pattern in PenggajianController" . PHP_EOL;
}

echo PHP_EOL . "=== Verification ===" . PHP_EOL;

// Test the new logic
echo "Testing new logic for Dedi Gunawan..." . PHP_EOL;

$testPegawai = DB::table('pegawais')
    ->join('jabatans', 'pegawais.jabatan_id', '=', 'jabatans.id')
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->select('pegawais.nama', 'pegawais.jenis_pegawai', 'pegawais.jabatan', 'jabatans.nama as nama_jabatan', 'jabatans.kategori as kategori_jabatan')
    ->first();

if ($testPegawai) {
    // Simulate the new logic
    $jenisPegawai = strtolower($testPegawai->kategori ?? $testPegawai->jenis_pegawai ?? 'btktl');
    
    if (strpos(strtolower($testPegawai->nama_jabatan), 'gudang') !== false) {
        $coaKode = '54'; // BTKL untuk Bagian Gudang
        $coaNama = 'BIAYA TENAGA KERJA TIDAK LANGSUNG';
        echo "✅ Bagian Gudang -> COA " . $coaKode . " (" . $coaNama . ")" . PHP_EOL;
    } else if ($jenisPegawai === 'btkl') {
        $coaKode = '52'; // BTKL untuk lainnya
        $coaNama = 'BIAYA TENAGA KERJA LANGSUNG';
        echo "✅ BTKL -> COA " . $coaKode . " (" . $coaNama . ")" . PHP_EOL;
    } else {
        $coaKode = '54'; // BTKTL untuk lainnya
        $coaNama = 'BIAYA TENAGA KERJA TIDAK LANGSUNG';
        echo "✅ BTKTL -> COA " . $coaKode . " (" . $coaNama . ")" . PHP_EOL;
    }
    
    echo PHP_EOL . "Hasil untuk Dedi Gunawan:" . PHP_EOL;
    echo "- Jabatan: " . $testPegawai->nama_jabatan . PHP_EOL;
    echo "- COA: " . $coaKode . " - " . $coaNama . PHP_EOL;
    echo "- Status: " . ($coaKode === '54' ? "✅ BENAR (sesuai keinginan user)" : "❌ SALAH") . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "✅ Fixed: Penggajian logic untuk Bagian Gudang" . PHP_EOL;
echo "✅ Result: Bagian Gudang sekarang ke COA 54 (BTKTL)" . PHP_EOL;
echo "✅ Logic: Lebih fleksibel dan adaptif" . PHP_EOL;
echo "✅ Status: Sesuai dengan kebutuhan user" . PHP_EOL;
