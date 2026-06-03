<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Perusahaan;
use App\Models\Produk;

function checkDashboardForSlug($slug) {
    $perusahaan = Perusahaan::where('slug', $slug)->first();
    if (!$perusahaan) {
        echo "Slug {$slug} not found!\n";
        return;
    }
    
    echo "=== SLUG: {$slug} ({$perusahaan->nama}) ===\n";
    $produks = Produk::withoutGlobalScopes()
        ->where('user_id', $perusahaan->user_id)
        ->get();
        
    foreach ($produks as $produk) {
        echo "Product: {$produk->nama_produk} (ID: {$produk->id}) | Stock column: {$produk->stok}\n";
    }
    echo "\n";
}

checkDashboardForSlug('pr-6a09deaddd260'); // PT Arkan Trans Jaya
checkDashboardForSlug('pr-6a0b296f45155'); // Center of Excellence (EADT SIMCOST)
