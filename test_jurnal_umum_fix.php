<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Jurnal Umum Fix...\n\n";

// Test the exact same query as the controller after fix
echo "Testing Fixed Query Logic:\n";
echo "========================\n";

$jurnalUmumQuery = \DB::table('jurnal_umum as ju')
    ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
    ->select([
        'ju.id',
        'ju.tanggal',
        'ju.coa_id',
        'ju.debit',
        'ju.kredit',
        'ju.keterangan',
        'ju.tipe_referensi',
        'ju.referensi',
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun',
        'ju.created_at',
        \DB::raw("'ju_' as ref_type"),
        \DB::raw('NULL as ref_id')
    ])
    ->where(function($q) {
        $q->where('ju.debit', '>', 0)
          ->orWhere('ju.kredit', '>', 0);
    })
    ->whereIn('ju.tipe_referensi', [
        'penyusutan', 'adjustment', 'manual', 'pembelian'
    ])
    ->orderBy('ju.created_at','asc')
    ->orderBy('ju.id','asc');

$results = $jurnalUmumQuery->get();

echo "Found {$results->count()} entries\n\n";

if ($results->count() > 0) {
    // Test the grouping logic that was causing the error
    echo "Testing Grouping Logic:\n";
    echo "======================\n";
    
    try {
        $jurnalUmumGrouped = $results->groupBy(function($item) {
            return $item->tanggal . '|' . $item->keterangan; // Fixed: was $item->memo
        });
        
        echo "Grouping successful! Found {$jurnalUmumGrouped->count()} groups\n\n";
        
        // Test the entry creation logic
        echo "Testing Entry Creation:\n";
        echo "======================\n";
        
        foreach ($jurnalUmumGrouped->take(2) as $key => $group) {
            $firstItem = $group->first();
            
            // This should work now without error
            $entry = (object) [
                'id' => 'ju_' . $firstItem->id,
                'tanggal' => $firstItem->tanggal,
                'created_at' => $firstItem->created_at,
                'ref_type' => $firstItem->ref_type,
                'ref_id' => null,
                'memo' => $firstItem->keterangan, // Fixed: was $firstItem->memo
                'lines' => $group->map(function($item) {
                    return (object) [
                        'id' => $item->id,
                        'debit' => $item->debit,
                        'credit' => $item->kredit,
                        'memo' => null,
                        'account_code' => $item->kode_akun,
                        'account_name' => $item->nama_akun,
                        'account_type' => $item->tipe_akun,
                        'coa' => (object) [
                            'kode_akun' => $item->kode_akun,
                            'nama_akun' => $item->nama_akun,
                            'tipe_akun' => $item->tipe_akun
                        ]
                    ];
                })
            ];
            
            echo "Group: {$key}\n";
            echo "Entry ID: {$entry->id}\n";
            echo "Memo: {$entry->memo}\n";
            echo "Lines: {$entry->lines->count()}\n";
            echo "Total Debit: " . $entry->lines->sum('debit') . "\n";
            echo "Total Credit: " . $entry->lines->sum('credit') . "\n";
            echo "----------------\n";
        }
        
        echo "All tests passed! The error should be fixed.\n";
        
    } catch (Exception $e) {
        echo "Error in grouping: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "No entries found to test\n";
}

echo "\nJurnal Umum fix test completed!\n";
