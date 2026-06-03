<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

echo "=== USERS ===\n";
foreach (DB::table('users')->get() as $user) {
    echo "ID: {$user->id} | Name: {$user->name} | Email: {$user->email} | Role: {$user->role} | Perusahaan ID: {$user->perusahaan_id}\n";
}

echo "\n=== PERUSAHAANS ===\n";
foreach (DB::table('perusahaan')->get() as $p) {
    echo "ID: {$p->id} | User ID: {$p->user_id} | Name: {$p->nama} | Slug: {$p->slug} | Kode: {$p->kode}\n";
}

echo "\n=== PRODUKS ===\n";
foreach (DB::table('produks')->get() as $p) {
    echo "ID: {$p->id} | User ID: {$p->user_id} | Kode: {$p->kode_produk} | Name: {$p->nama_produk} | Stok: {$p->stok}\n";
}

echo "\n=== STOCK MOVEMENTS (Product) ===\n";
foreach (DB::table('stock_movements')->where('item_type', 'product')->get() as $sm) {
    echo "ID: {$sm->id} | Item ID: {$sm->item_id} | Date: {$sm->tanggal} | Direction: {$sm->direction} | Qty: {$sm->qty} | Ref Type: {$sm->ref_type} | Ref ID: {$sm->ref_id}\n";
}

echo "\n=== STOCK LAYERS (Product) ===\n";
foreach (DB::table('stock_layers')->where('item_type', 'product')->get() as $sl) {
    echo "ID: {$sl->id} | Item ID: {$sl->item_id} | Remaining: {$sl->remaining_qty} | Ref Type: {$sl->ref_type} | Ref ID: {$sl->ref_id}\n";
}

echo "\n=== RETUR PENJUALAN ===\n";
foreach (DB::table('retur_penjualans')->get() as $rp) {
    echo "ID: {$rp->id} | Data: " . json_encode($rp) . "\n";
}

echo "\n=== DETAIL RETUR PENJUALAN ===\n";
foreach (DB::table('detail_retur_penjualans')->get() as $drp) {
    echo "ID: {$drp->id} | Retur ID: {$drp->retur_penjualan_id} | Product ID: {$drp->produk_id} | Qty: {$drp->qty_retur} | Subtotal: {$drp->subtotal}\n";
}

echo "\n=== PENJUALAN DETAILS ===\n";
foreach (DB::table('penjualan_details')->get() as $pd) {
    $pj = DB::table('penjualans')->where('id', $pd->penjualan_id)->first();
    echo "ID: {$pd->id} | Penjualan ID: {$pd->penjualan_id} | User ID: " . ($pj->user_id ?? 'N/A') . " | Product ID: {$pd->produk_id} | Qty: {$pd->jumlah} | Subtotal: {$pd->subtotal} | Status: " . ($pj->status ?? 'N/A') . "\n";
}
