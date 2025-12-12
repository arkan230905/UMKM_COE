<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking reviews...\n";

$reviews = App\Models\Review::with('order', 'user')->get();

echo "Total reviews: " . $reviews->count() . "\n";

foreach ($reviews as $review) {
    echo "Review ID: {$review->id}\n";
    echo "Order ID: {$review->order_id}\n";
    echo "User ID: {$review->user_id}\n";
    echo "Rating: {$review->rating}\n";
    echo "Comment: {$review->comment}\n";
    echo "Order Status: " . $review->order->status . "\n";
    echo "Payment Status: " . $review->order->payment_status . "\n";
    echo "-------------------\n";
}

// Test produk relationship
echo "\nTesting produk relationship:\n";
foreach ($reviews as $review) {
    $produk = $review->produk;
    echo "Review ID {$review->id} -> Produk: " . ($produk ? $produk->nama_produk : 'NULL') . "\n";
}
