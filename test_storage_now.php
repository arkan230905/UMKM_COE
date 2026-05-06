<?php
echo "Testing Storage Route...\n\n";

$url = "http://127.0.0.1:8000/storage/bukti_faktur/1/1778021408_nota%20e2000.png";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

echo "URL: $url\n";
echo "HTTP Code: $httpCode\n";
echo "Content-Type: $contentType\n\n";

if ($httpCode == 200) {
    echo "✅ SUCCESS! Storage route is working!\n";
    echo "✅ File can be accessed\n";
    echo "✅ Content-Type is correct: $contentType\n\n";
    echo "🎉 You can now test in browser:\n";
    echo "   http://127.0.0.1:8000/transaksi/pembelian/1\n";
} else {
    echo "❌ FAILED! HTTP Code: $httpCode\n";
    echo "❌ Storage route is not working\n";
}
