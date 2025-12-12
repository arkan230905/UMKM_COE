<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking remaining cart items...\n";

$carts = App\Models\Cart::with('produk')->get();

foreach ($carts as $cart) {
    echo "Cart ID: {$cart->id}\n";
    echo "Produk ID: {$cart->produk_id}\n";
    echo "User ID: {$cart->user_id}\n";
    echo "Produk exists: " . ($cart->produk ? "YES ({$cart->produk->nama_produk})" : "NO") . "\n";
    echo "-------------------\n";
}

echo "Total: " . $carts->count() . " items\n";
