<?php

echo "=== CREATE SAMPLE BOP DATA ===\n\n";

echo "Creating sample BOP data for testing...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Creating sample BOP data for proses_bduksi_id 1 and 2...\n";
    
    // Create sample BOP data for testing
    $sampleBOP = [
        [
            'user_id' => 1,
            'proses_bduksi_id' => 1,
            'komponen_bop' => 'Listrik',
            'total_bop_per_produk' => 150000,
            'total_bop_per_jam' => 1500,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'user_id' => 1,
            'proses_bduksi_id' => 2,
            'komponen_bop' => 'Gas BBM',
            'total_bop_per_produk' => 500000,
            'total_bop_per_jam' => 5000,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    \Illuminate\Support\Facades\DB::table('bop_proses')->insert($sampleBOP);
    
    echo "✅ Created sample BOP data:\n";
    echo "  - Proses BTKL ID 1: Listrik (Rp 150,000)\n";
    echo "  - Proses BTKL ID 2: Gas BBM (Rp 500,000)\n";
    
    echo "\n=== SAMPLE BOP DATA CREATED ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
