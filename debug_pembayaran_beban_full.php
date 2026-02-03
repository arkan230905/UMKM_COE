<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: Full PembayaranBebanController@create ===" . PHP_EOL;

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
        
    // Cari akun kas (kode 1101-1103 untuk kas dan bank)
    $akunKas = \App\Models\Coa::where(function($query) {
            $query->where('kode_akun', 'like', '1101%')  // Kas
                  ->orWhere('kode_akun', 'like', '1102%') // Bank
                  ->orWhere('kode_akun', 'like', '1103%'); // Kas di Bank
        })
        ->orderBy('kode_akun')
        ->get();
    
    echo "akunBeban count: " . $akunBeban->count() . PHP_EOL;
    echo "akunKas count: " . $akunKas->count() . PHP_EOL;
    
    if ($akunBeban->isEmpty() || $akunKas->isEmpty()) {
        $error = 'Akun beban dengan budget atau akun kas belum diatur. ';
        $error .= $akunBeban->isEmpty() ? 'Tidak ada akun beban dengan budget yang aktif. ' : '';
        $error .= $akunKas->isEmpty() ? 'Tidak ada akun kas/bank yang aktif.' : '';
        echo "ERROR: {$error}" . PHP_EOL;
    } else {
        echo "SUCCESS: Data lengkap" . PHP_EOL;
        
        echo PHP_EOL . "=== Data akunBeban untuk view ===" . PHP_EOL;
        foreach ($akunBeban as $beban) {
            echo "- ID: {$beban->id}, kode_akun: {$beban->kode_akun}, nama_akun: {$beban->nama_akun}" . PHP_EOL;
        }
        
        echo PHP_EOL . "=== Data akunKas untuk view ===" . PHP_EOL;
        foreach ($akunKas as $kas) {
            echo "- ID: {$kas->id}, kode_akun: {$kas->kode_akun}, nama_akun: {$kas->nama_akun}" . PHP_EOL;
        }
    }
    
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
