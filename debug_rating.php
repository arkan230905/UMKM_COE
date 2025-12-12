<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debug Rating Query\n";

// Test direct query
$productId = 1; // Nasi Ayam Ketumbar

echo "Testing product ID: $productId\n";

// Check if order items exist for this product
$orderItems = DB::table('order_items')
    ->where('produk_id', $productId)
    ->get();

echo "Order items for product: " . $orderItems->count() . "\n";

foreach ($orderItems as $item) {
    echo "Order ID: {$item->order_id}, Produk ID: {$item->produk_id}\n";
}

// Check reviews for these orders
if ($orderItems->count() > 0) {
    $orderIds = $orderItems->pluck('order_id');
    
    $reviews = DB::table('reviews')
        ->whereIn('order_id', $orderIds)
        ->get();
        
    echo "Reviews for these orders: " . $reviews->count() . "\n";
    
    foreach ($reviews as $review) {
        echo "Review ID: {$review->id}, Order ID: {$review->order_id}, Rating: {$review->rating}\n";
    }
    
    // Test the exact query from controller
    $avgRating = DB::table('reviews')
        ->join('order_items', 'reviews.order_id', '=', 'order_items.order_id')
        ->where('order_items.produk_id', $productId)
        ->avg('reviews.rating');
        
    $reviewsCount = DB::table('reviews')
        ->join('order_items', 'reviews.order_id', '=', 'order_items.order_id')
        ->where('order_items.produk_id', $productId)
        ->count();
        
    echo "Average Rating: " . ($avgRating ?? 0) . "\n";
    echo "Reviews Count: " . $reviewsCount . "\n";
}
