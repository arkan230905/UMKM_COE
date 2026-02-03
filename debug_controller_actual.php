<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: Actual Controller Logic ===" . PHP_EOL;

try {
    // Cari akun beban yang sudah ada budget di BOP Lainnya
    $akunBeban = \App\Models\BopLainnya::where('budget', '>', 0)
        ->where('is_active', true)
        ->with(['coa'])
        ->get()
        ->map(function($bop) {
            return $bop->coa;
        })
        ->filter(function($coa) {
            return $coa; // Hanya filter yang COA-nya ada (tanpa cek is_active)
        })
        ->unique('kode_akun'); // Hapus duplikat berdasarkan kode_akun
        
    // Cari akun kas (kode 101-102 untuk kas dan bank)
    $akunKas = \App\Models\Coa::whereIn('kode_akun', ['101', '102'])
        ->orderBy('kode_akun')
        ->get();
    
    echo "akunBeban count: " . $akunBeban->count() . PHP_EOL;
    echo "akunKas count: " . $akunKas->count() . PHP_EOL;
    
    if ($akunBeban->isEmpty()) {
        $error = 'Akun beban dengan budget belum diatur. ';
        $error .= 'Tidak ada akun beban dengan budget yang aktif. ';
        echo "BLOCKING ERROR: {$error}" . PHP_EOL;
        exit(1);
    }
    
    if ($akunKas->isEmpty()) {
        // Warning only, not blocking
        echo "WARNING: Tidak ada akun kas/bank yang aktif untuk pembayaran beban" . PHP_EOL;
    }
    
    echo "SUCCESS: Form akan dimuat dengan data:" . PHP_EOL;
    
    echo PHP_EOL . "=== Data akunBeban untuk view ===" . PHP_EOL;
    foreach ($akunBeban as $beban) {
        echo "- ID: {$beban->id}, kode_akun: {$beban->kode_akun}, nama_akun: {$beban->nama_akun}" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Data akunKas untuk view ===" . PHP_EOL;
    foreach ($akunKas as $kas) {
        echo "- ID: {$kas->id}, kode_akun: {$kas->kode_akun}, nama_akun: {$kas->nama_akun}" . PHP_EOL;
    }
    
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
