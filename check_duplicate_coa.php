<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

// Cek duplikat berdasarkan kode_akun dan company_id
$duplicates = DB::table('coas')
    ->select('kode_akun', 'company_id', DB::raw('COUNT(*) as count'))
    ->groupBy('kode_akun', 'company_id')
    ->having('count', '>', 1)
    ->get();

if ($duplicates->count() > 0) {
    echo "❌ Ditemukan duplikat COA:\n";
    foreach ($duplicates as $dup) {
        echo "  - Kode: {$dup->kode_akun}, Company: {$dup->company_id}, Jumlah: {$dup->count}\n";
        
        // Tampilkan detail
        $details = Coa::where('kode_akun', $dup->kode_akun)
            ->where('company_id', $dup->company_id)
            ->get(['id', 'nama_akun', 'created_at']);
        
        foreach ($details as $detail) {
            echo "    ID: {$detail->id}, Nama: {$detail->nama_akun}, Created: {$detail->created_at}\n";
        }
    }
    
    echo "\n⚠️  Hapus duplikat? (y/n): ";
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    
    if(trim($line) == 'y'){
        foreach ($duplicates as $dup) {
            // Ambil semua record duplikat
            $records = Coa::where('kode_akun', $dup->kode_akun)
                ->where('company_id', $dup->company_id)
                ->orderBy('id', 'asc')
                ->get();
            
            // Hapus semua kecuali yang pertama
            $first = true;
            foreach ($records as $record) {
                if ($first) {
                    $first = false;
                    continue;
                }
                echo "  Menghapus ID: {$record->id} - {$record->nama_akun}\n";
                $record->delete();
            }
        }
        echo "✓ Duplikat berhasil dihapus!\n";
    }
} else {
    echo "✓ Tidak ada duplikat COA\n";
}

// Tampilkan total COA per company
echo "\nTotal COA per company:\n";
$totals = DB::table('coas')
    ->select('company_id', DB::raw('COUNT(*) as total'))
    ->groupBy('company_id')
    ->get();

foreach ($totals as $total) {
    $companyId = $total->company_id ?? 'NULL (Template)';
    echo "  Company {$companyId}: {$total->total} akun\n";
}
