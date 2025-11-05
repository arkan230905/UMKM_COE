<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

// Create a request
$request = \Illuminate\Http\Request::create('/master-data/presensi', 'GET');

// Handle the request
$response = $kernel->handle($request);

// Get the content
$content = $response->getContent();

// Extract the table rows with pegawai data
preg_match_all('/<td class="fw-bold text-white">(.*?)<\/td>/s', $content, $matches);

echo "=== HTML OUTPUT DARI ROUTE ===\n\n";

if (!empty($matches[1])) {
    foreach ($matches[1] as $index => $match) {
        echo "Baris " . ($index + 1) . ":\n";
        // Clean up HTML for readability
        $clean = strip_tags($match);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);
        echo $clean . "\n";
        echo "---\n";
    }
} else {
    echo "Tidak ada data ditemukan\n";
    echo "\nSample HTML (first 2000 chars):\n";
    echo substr($content, 0, 2000);
}

$kernel->terminate($request, $response);
