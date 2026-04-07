<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Checking BTKL data for Ayam Crispy...\n\n";

$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();

if (!$bomJobCosting) {
    echo "❌ BOM Job Costing not found for product ID 3\n";
    exit;
}

$btkls = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();

echo "📋 BTKL processes for Ayam Crispy:\n";
foreach ($btkls as $btkl) {
    echo "- {$btkl->nama_proses}: Rp " . number_format($btkl->subtotal, 0, ',', '.') . "\n";
}

// Check if there's a typo in the source data
$typoProcess = $btkls->where('nama_proses', 'Perbumbuan')->first();
if ($typoProcess) {
    echo "\n⚠️  Found typo in source data: 'Perbumbuan'\n";
    echo "Fixing source data...\n";
    
    $typoProcess->update(['nama_proses' => 'Pembumbuan']);
    echo "✅ Fixed source BTKL data: 'Perbumbuan' → 'Pembumbuan'\n";
}

echo "\n🎉 Done!\n";