<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Penggajian Classification Logic ===" . PHP_EOL;

// The problem: Bagian Gudang should be BTKL, not BTKTL
echo PHP_EOL . "Masalah:" . PHP_EOL;
echo "- Jabatan: Bagian Gudang (kategori: btktl di jabatan)" . PHP_EOL;
echo "- Tapi sistem klasifikasikan sebagai BTKTL" . PHP_EOL;
echo "- Seharusnya: Bagian Gudang = BTKL (Tenaga Kerja Langsung)" . PHP_EOL;

echo PHP_EOL . "=== Current Logic (WRONG) ===" . PHP_EOL;
echo "Logic saat ini yang salah:" . PHP_EOL;
echo "if (\$jenisPegawai === 'btkl') {" . PHP_EOL;
echo "    // Logic untuk BTKL" . PHP_EOL;
echo "} else {" . PHP_EOL;
echo "    // Logic untuk BTKTL" . PHP_EOL;
echo "}" . PHP_EOL;

echo PHP_EOL . "=== Correct Logic ===" . PHP_EOL;
echo "Logic yang seharusnya:" . PHP_EOL;
echo "if (\$jenisPegawai === 'btkl') {" . PHP_EOL;
echo "    // Logic untuk BTKL" . PHP_EOL;
echo "} else {" . PHP_EOL;
echo "    // Logic untuk BTKTL" . PHP_EOL;
echo "}" . PHP_EOL;

echo PHP_EOL . "=== The REAL Issue ===" . PHP_EOL;
echo "Masalah sebenarnya bukan di logic klasifikasi," . PHP_EOL;
echo "tapi di DATA JABATAN yang salah!" . PHP_EOL;
echo PHP_EOL . "Cek data jabatan:" . PHP_EOL;

// Check jabatan data for Bagian Gudang
$jabatanGudang = DB::table('jabatans')
    ->where('nama', 'like', '%gudang%')
    ->first();

if ($jabatanGudang) {
    echo "Jabatan Gudang:" . PHP_EOL;
    echo "- ID: " . $jabatanGudang->id . PHP_EOL;
    echo "- Nama: " . $jabatanGudang->nama . PHP_EOL;
    echo "- Kategori: " . $jabatanGudang->kategori . PHP_EOL;
    echo "- Kategori (enum): " . $jabatanGudang->kategori . PHP_EOL;
    
    // The problem is in the jabatan table!
    if ($jabatanGudang->kategori === 'btktl') {
        echo PHP_EOL . "❌ INI MASALAHNYA!" . PHP_EOL;
        echo "Kategori jabatan 'Bagian Gudang' = 'btktl' (SALAH!)" . PHP_EOL;
        echo "Seharusnya = 'btkl' (BENAR!)" . PHP_EOL;
        echo PHP_EOL . "Solusi: Update kategori jabatan di database" . PHP_EOL;
    } else {
        echo PHP_EOL . "✅ Kategori jabatan sudah benar: " . $jabatanGudang->kategori . PHP_EOL;
    }
}

echo PHP_EOL . "=== Check Other Departments ===" . PHP_EOL;

// Check other departments to understand pattern
$otherJabatans = DB::table('jabatans')
    ->where('nama', 'not like', '%gudang%')
    ->limit(5)
    ->get();

echo "Other Jabatan Categories:" . PHP_EOL;
foreach ($otherJabatans as $jabatan) {
    echo "- " . $jabatan->nama . " (Kategori: " . $jabatan->kategori . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Solutions ===" . PHP_EOL;

echo "Solusi 1: Fix Jabatan Data (RECOMMENDED)" . PHP_EOL;
echo "- UPDATE jabatans SET kategori = 'btkl' WHERE nama LIKE '%gudang%'" . PHP_EOL;
echo "- Ini akan memperbaiki semua jabatan gudang sekaligus" . PHP_EOL;

echo PHP_EOL . "Solusi 2: Fix Penggajian Logic" . PHP_EOL;
echo "- Tambah logic khusus untuk department gudang" . PHP_EOL;
echo "- if (strpos(strtolower(\$jabatan->nama), 'gudang') !== false) {" . PHP_EOL;
echo "-     \$jenisPegawai = 'btkl'; // Force ke BTKL untuk gudang" . PHP_EOL;
echo "- }" . PHP_EOL;

echo PHP_EOL . "=== Recommended Action ===" . PHP_EOL;
echo "💡 Rekomendasi: Solusi 1 (Fix Jabatan Data)" . PHP_EOL;
echo "✅ Lebih permanen dan fundamental" . PHP_EOL;
echo "✅ Tidak perlu ubah logic penggajian" . PHP_EOL;
echo "✅ Data konsisten untuk semua transaksi" . PHP_EOL;

echo PHP_EOL . "Creating fix script..." . PHP_EOL;

// Create fix script
$fixScript = "<?php" . PHP_EOL;
$fixScript .= "require __DIR__.'/vendor/autoload.php';" . PHP_EOL;
$fixScript .= "\$app = require_once __DIR__.'/bootstrap/app.php';" . PHP_EOL;
$fixScript .= "\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);" . PHP_EOL;
$fixScript .= "\$kernel->bootstrap();" . PHP_EOL;
$fixScript .= PHP_EOL . "echo \"=== Fix Jabatan Gudang Category ===\" . PHP_EOL;" . PHP_EOL;
$fixScript .= PHP_EOL . "// Update all gudang-related jabatans to btkl" . PHP_EOL;
$fixScript .= "\$result = DB::table('jabatans')" . PHP_EOL;
$fixScript .= "    ->where('nama', 'like', '%gudang%')" . PHP_EOL;
$fixScript .= "    ->update(['kategori' => 'btkl']);" . PHP_EOL;
$fixScript .= PHP_EOL . "echo \"Updated \" . \$result . \" jabatan gudang records\" . PHP_EOL;" . PHP_EOL;
$fixScript .= PHP_EOL . "echo \"✅ Kategori jabatan gudang diubah ke 'btkl'\" . PHP_EOL;" . PHP_EOL;

file_put_contents(__DIR__ . '/fix_jabatan_gudang.php', $fixScript);

echo "✅ Created fix_jabatan_gudang.php script" . PHP_EOL;
echo "Run: php fix_jabatan_gudang.php" . PHP_EOL;
