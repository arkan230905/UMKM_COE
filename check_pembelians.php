<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECK PEMBELIANS IN DATABASE\n";
echo "============================\n";

$pembelians = \App\Models\Pembelian::with('vendor')->get();

echo "Total pembelians: " . $pembelians->count() . "\n\n";

if ($pembelians->count() > 0) {
    echo "Available pembelians:\n";
    foreach ($pembelians as $pembelian) {
        echo "ID: {$pembelian->id}, Nomor: {$pembelian->nomor_pembelian}, Vendor: " . ($pembelian->vendor ? $pembelian->vendor->nama_vendor : 'N/A') . "\n";
    }
} else {
    echo "No pembelians found in database.\n";
    echo "Creating a test pembelian for testing...\n";
    
    // Create test pembelian
    $vendor = \App\Models\Vendor::first();
    $coaKas = \App\Models\Coa::where('kode_akun', '112')->first();
    
    if (!$vendor || !$coaKas) {
        echo "Required data (vendor or COA) not found.\n";
        exit;
    }
    
    $pembelian = new \App\Models\Pembelian([
        'vendor_id' => $vendor->id,
        'nomor_faktur' => 'TEST-EDIT-001',
        'tanggal' => '2026-04-30',
        'subtotal' => 100000,
        'biaya_kirim' => 0,
        'ppn_persen' => 11,
        'ppn_nominal' => 11000,
        'total_harga' => 111000,
        'terbayar' => 111000,
        'sisa_pembayaran' => 0,
        'status' => 'lunas',
        'payment_method' => 'cash',
        'bank_id' => $coaKas->id,
        'keterangan' => 'Test pembelian for edit page',
    ]);
    $pembelian->save();
    
    echo "Created test pembelian ID: {$pembelian->id}\n";
    echo "You can now access: /transaksi/pembelian/{$pembelian->id}/edit\n";
}

echo "\nCheck completed.\n";
