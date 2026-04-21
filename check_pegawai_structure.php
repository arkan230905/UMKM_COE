<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Pegawai Table Structure ===" . PHP_EOL;

// Get table structure
$columns = DB::select("DESCRIBE pegawais");
echo "Columns in pegawais table:" . PHP_EOL;
foreach ($columns as $column) {
    echo "- " . $column->Field . " (" . $column->Type . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Search for Dedi Gunawan ===" . PHP_EOL;

// Try different column names
$possibleColumns = ['nama', 'nama_lengkap', 'nama_pegawai', 'pegawai_nama'];
$found = false;

foreach ($possibleColumns as $column) {
    try {
        $pegawai = DB::table('pegawais')
            ->where($column, 'like', '%Dedi Gunawan%')
            ->first();
        
        if ($pegawai) {
            echo PHP_EOL . "✅ Found in column '" . $column . "':" . PHP_EOL;
            echo "ID: " . $pegawai->id . PHP_EOL;
            echo "Nama: " . $pegawai->$column . PHP_EOL;
            echo "Jabatan: " . ($pegawai->jabatan ?? 'N/A') . PHP_EOL;
            echo "Jenis Pegawai: " . ($pegawai->jenis_pegawai ?? 'N/A') . PHP_EOL;
            $found = true;
            break;
        }
    } catch (\Exception $e) {
        echo "❌ Error with column '" . $column . "': " . $e->getMessage() . PHP_EOL;
    }
}

if (!$found) {
    echo PHP_EOL . "❌ Dedi Gunawan tidak ditemukan di kolom manapun" . PHP_EOL;
}

echo PHP_EOL . "=== All Pegawai Data ===" . PHP_EOL;

// Show all pegawai data
$allPegawais = DB::table('pegawais')->limit(10)->get();
foreach ($allPegawais as $p) {
    echo "ID: " . $p->id . " | Nama: " . ($p->nama ?? $p->nama_lengkap ?? 'N/A') . " | Jabatan: " . ($p->jabatan ?? 'N/A') . " | Jenis: " . ($p->jenis_pegawai ?? 'N/A') . PHP_EOL;
}
