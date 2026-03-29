<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Debugging Gas 30 Kg conversion...\n\n";

$gas = \App\Models\BahanPendukung::with(['satuanRelation', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->find(3);

if ($gas) {
    echo "Material: {$gas->nama_bahan}\n";
    echo "Main Unit: " . ($gas->satuanRelation->nama ?? 'N/A') . "\n\n";
    
    echo "Sub Units:\n";
    echo "1. " . ($gas->subSatuan1->nama ?? 'N/A') . " - Nilai: " . ($gas->sub_satuan_1_nilai ?? 'N/A') . "\n";
    echo "2. " . ($gas->subSatuan2->nama ?? 'N/A') . " - Nilai: " . ($gas->sub_satuan_2_nilai ?? 'N/A') . "\n";
    echo "3. " . ($gas->subSatuan3->nama ?? 'N/A') . " - Nilai: " . ($gas->sub_satuan_3_nilai ?? 'N/A') . "\n\n";
    
    // Test conversion step by step
    $jumlah = 500.0;
    $dariSatuan = 'Gram';
    $keSatuan = 'Tabung';
    
    echo "Converting {$jumlah} {$dariSatuan} to {$keSatuan}:\n";
    
    $dariSatuanLower = strtolower($dariSatuan);
    $keSatuanLower = strtolower($keSatuan);
    $satuanUtama = strtolower($gas->satuanRelation->nama ?? '');
    
    echo "From unit (lower): {$dariSatuanLower}\n";
    echo "To unit (lower): {$keSatuanLower}\n";
    echo "Main unit (lower): {$satuanUtama}\n\n";
    
    // Check each sub satuan
    for ($i = 1; $i <= 3; $i++) {
        $subSatuanId = $gas->{"sub_satuan_{$i}_id"};
        $subSatuanNilai = $gas->{"sub_satuan_{$i}_nilai"};
        
        if ($subSatuanId && $subSatuanNilai > 0) {
            $subSatuan = \App\Models\Satuan::find($subSatuanId);
            if ($subSatuan) {
                $subSatuanNama = strtolower($subSatuan->nama);
                echo "Sub Satuan {$i}: {$subSatuanNama} - Nilai: {$subSatuanNilai}\n";
                
                // Check if this matches the from unit
                if (str_contains($subSatuanNama, $dariSatuanLower)) {
                    echo "  ✓ Matches FROM unit ({$dariSatuanLower})\n";
                    
                    // Check if converting to main unit
                    if ($keSatuanLower === $satuanUtama || str_contains($satuanUtama, $keSatuanLower)) {
                        echo "  ✓ Converting to main unit ({$satuanUtama})\n";
                        $result = $jumlah / $subSatuanNilai;
                        echo "  Result: {$jumlah} / {$subSatuanNilai} = {$result}\n";
                    }
                }
            }
        }
    }
    
    // Test the actual method
    echo "\nActual method result: " . $gas->convertToSubUnit($jumlah, $dariSatuan, $keSatuan) . "\n";
}