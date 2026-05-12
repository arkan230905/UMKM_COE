<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Services\JournalService;

// Penggajian data
$penggajian_data = [
    [
        'id' => 1,
        'tanggal' => '2026-04-24',
        'gaji_pokok' => 140000,
        'tunjangan' => 525000,
        'asuransi' => 100000,
        'bonus' => 0,
        'total' => 765000
    ],
    [
        'id' => 3,
        'tanggal' => '2026-04-24',
        'gaji_pokok' => 72000,
        'tunjangan' => 495000,
        'asuransi' => 80000,
        'bonus' => 200000,
        'total' => 847000
    ],
    [
        'id' => 4,
        'tanggal' => '2026-04-25',
        'gaji_pokok' => 51000,
        'tunjangan' => 475000,
        'asuransi' => 0,
        'bonus' => 0,
        'total' => 526000
    ],
    [
        'id' => 5,
        'tanggal' => '2026-04-26',
        'gaji_pokok' => 2500000,
        'tunjangan' => 600000,
        'asuransi' => 150000,
        'bonus' => 0,
        'total' => 3250000
    ]
];

$service = new JournalService();

foreach ($penggajian_data as $pg) {
    echo "Creating journal for penggajian {$pg['id']}...\n";
    
    $lines = [];
    
    // BTKL or BOP (based on gaji_pokok amount)
    if ($pg['gaji_pokok'] > 500000) {
        // BOP TENAGA KERJA TIDAK LANGSUNG (54)
        $lines[] = [
            'coa_id' => DB::table('coas')->where('kode_akun', '54')->first()->id,
            'debit' => $pg['gaji_pokok'],
            'credit' => 0,
            'memo' => 'BOP Tenaga Kerja Tidak Langsung'
        ];
    } else {
        // BTKL (52)
        $lines[] = [
            'coa_id' => DB::table('coas')->where('kode_akun', '52')->first()->id,
            'debit' => $pg['gaji_pokok'],
            'credit' => 0,
            'memo' => 'Gaji Pokok'
        ];
    }
    
    // Beban Tunjangan (513)
    if ($pg['tunjangan'] > 0) {
        $lines[] = [
            'coa_id' => DB::table('coas')->where('kode_akun', '513')->first()->id,
            'debit' => $pg['tunjangan'],
            'credit' => 0,
            'memo' => 'Beban Tunjangan'
        ];
    }
    
    // Beban Asuransi (514)
    if ($pg['asuransi'] > 0) {
        $lines[] = [
            'coa_id' => DB::table('coas')->where('kode_akun', '514')->first()->id,
            'debit' => $pg['asuransi'],
            'credit' => 0,
            'memo' => 'Beban Asuransi'
        ];
    }
    
    // Beban Bonus (515)
    if ($pg['bonus'] > 0) {
        $lines[] = [
            'coa_id' => DB::table('coas')->where('kode_akun', '515')->first()->id,
            'debit' => $pg['bonus'],
            'credit' => 0,
            'memo' => 'Beban Bonus'
        ];
    }
    
    // Kas (112)
    $lines[] = [
        'coa_id' => DB::table('coas')->where('kode_akun', '112')->first()->id,
        'debit' => 0,
        'credit' => $pg['total'],
        'memo' => 'Pembayaran Gaji'
    ];
    
    try {
        $entry = $service->post(
            $pg['tanggal'],
            'payroll',
            $pg['id'],
            "Penggajian",
            $lines
        );
        echo "  ✓ Created entry {$entry->id}\n";
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";
