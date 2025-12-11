<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking cart items...\n";

// Get all carts
$carts = App\Models\Cart::all();
echo "Total cart items: " . $carts->count() . "\n";

// Get valid product IDs
$validProductIds = App\Models\Produk::pluck('id')->toArray();
echo "Valid product IDs count: " . count($validProductIds) . "\n";

// Find invalid carts
$invalidCarts = App\Models\Cart::whereNotIn('produk_id', $validProductIds)->get();
echo "Invalid cart items: " . $invalidCarts->count() . "\n";

foreach ($invalidCarts as $cart) {
    echo "Removing cart ID {$cart->id} with invalid produk_id {$cart->produk_id}\n";
    $cart->delete();
}

echo "Cleanup completed!\n";
