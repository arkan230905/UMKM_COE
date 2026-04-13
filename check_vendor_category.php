<?php

$pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');

// Check purchase 4 and its vendor
echo "Checking purchase 4 and its vendor:\n";

$stmt = $pdo->prepare('
    SELECT p.id, p.vendor_id, v.nama_vendor, v.kategori as vendor_kategori
    FROM pembelians p
    LEFT JOIN vendors v ON p.vendor_id = v.id
    WHERE p.id = 4
');
$stmt->execute();
$purchase = $stmt->fetch(PDO::FETCH_ASSOC);

if ($purchase) {
    echo "Purchase ID: {$purchase['id']}\n";
    echo "Vendor ID: {$purchase['vendor_id']}\n";
    echo "Vendor Name: {$purchase['nama_vendor']}\n";
    echo "Vendor Category: {$purchase['vendor_kategori']}\n";
    
    // Check what the display logic should be
    $vendorKategori = strtolower($purchase['vendor_kategori'] ?? '');
    
    if ($vendorKategori === 'bahan baku') {
        echo "\nExpected behavior: Show only Bahan Baku section\n";
        echo "Bahan Baku: visible\n";
        echo "Bahan Pendukung: hidden\n";
    } elseif ($vendorKategori === 'bahan pendukung') {
        echo "\nExpected behavior: Show only Bahan Pendukung section\n";
        echo "Bahan Baku: hidden\n";
        echo "Bahan Pendukung: visible\n";
    } else {
        echo "\nExpected behavior: Show both sections\n";
        echo "Bahan Baku: visible\n";
        echo "Bahan Pendukung: visible\n";
    }
} else {
    echo "Purchase not found\n";
}

// Also check all vendors and their categories
echo "\n--- All Vendors ---\n";
$stmt2 = $pdo->prepare('SELECT id, nama_vendor, kategori FROM vendors ORDER BY nama_vendor');
$stmt2->execute();
$vendors = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($vendors as $vendor) {
    echo "ID: {$vendor['id']}, Name: {$vendor['nama_vendor']}, Category: {$vendor['kategori']}\n";
}