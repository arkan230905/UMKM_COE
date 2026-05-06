<?php
$content = file_get_contents('resources/views/transaksi/penjualan/create.blade.php');
echo 'File size: ' . strlen($content) . ' bytes' . PHP_EOL;
echo 'Lines: ' . substr_count($content, "\n") . PHP_EOL;

$jsStart = strpos($content, 'function setPriceFromSelect');
echo 'setPriceFromSelect found at position: ' . ($jsStart !== false ? $jsStart : 'NOT FOUND') . PHP_EOL;

$dataPrice = substr_count($content, 'data-price');
echo 'data-price occurrences: ' . $dataPrice . PHP_EOL;

// Find the option HTML with data-price
preg_match_all('/data-price="([^"]*)"/', $content, $matches);
echo 'data-price values found: ' . count($matches[1]) . PHP_EOL;
foreach (array_slice($matches[1], 0, 5) as $val) {
    echo '  value: "' . $val . '"' . PHP_EOL;
}

// Check if harga_jual is used in option
$optionSection = substr($content, strpos($content, 'data-price="{{ round'), 200);
echo "\nOption section around data-price:\n" . $optionSection . PHP_EOL;
