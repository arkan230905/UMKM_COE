<?php
$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Conversion Ratios</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #000; color: #0f0; }
        .success { color: #0f0; font-weight: bold; }
        .error { color: #f00; font-weight: bold; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #0f0; padding: 8px; text-align: left; }
        th { background: #003300; }
    </style>
</head>
<body>
<h1>🔧 FIX AYAM KAMPUNG CONVERSION RATIOS</h1>
<pre><?php
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "CHECKING CURRENT CONVERSION RATIOS...\n\n";
    
    $stmt = $pdo->query("
        SELECT 
            bb.id,
            bb.nama_bahan,
            s_utama.nama as satuan_utama,
            s_sub1.nama as sub_satuan_1,
            bb.sub_satuan_1_konversi,
            s_sub2.nama as sub_satuan_2,
            bb.sub_satuan_2_konversi,
            s_sub3.nama as sub_satuan_3,
            bb.sub_satuan_3_konversi
        FROM bahan_bakus bb
        LEFT JOIN satuans s_utama ON bb.satuan_id = s_utama.id
        LEFT JOIN satuans s_sub1 ON bb.sub_satuan_1_id = s_sub1.id
        LEFT JOIN satuans s_sub2 ON bb.sub_satuan_2_id = s_sub2.id
        LEFT JOIN satuans s_sub3 ON bb.sub_satuan_3_id = s_sub3.id
        WHERE bb.id = 2
    ");
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "CURRENT CONFIGURATION:\n";
    echo "Nama: {$current['nama_bahan']}\n";
    echo "Satuan Utama: {$current['satuan_utama']}\n";
    echo "Sub Satuan 1: {$current['sub_satuan_1']} (konversi: {$current['sub_satuan_1_konversi']})\n";
    echo "Sub Satuan 2: {$current['sub_satuan_2']} (konversi: {$current['sub_satuan_2_konversi']})\n";
    echo "Sub Satuan 3: {$current['sub_satuan_3']} (konversi: {$current['sub_satuan_3_konversi']})\n\n";
    
    echo "<span class='error'>PROBLEM DETECTED:</span>\n";
    if ($current['sub_satuan_2_konversi'] != 1.5) {
        echo "- Sub Satuan 2 (Kilogram) konversi = {$current['sub_satuan_2_konversi']}, should be 1.5\n";
    }
    if ($current['sub_satuan_3_konversi'] != 1500) {
        echo "- Sub Satuan 3 (Gram) konversi = {$current['sub_satuan_3_konversi']}, should be 1500\n";
    }
    echo "\n";
    
    $pdo->beginTransaction();
    
    echo "FIXING CONVERSION RATIOS...\n";
    
    // Get satuan IDs
    $stmt = $pdo->query("SELECT id, nama FROM satuans WHERE nama IN ('Ekor', 'Potong', 'Kilogram', 'Kg', 'Gram')");
    $satuans = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $satuans[$row['nama']] = $row['id'];
    }
    
    // Update Ayam Kampung with correct conversions
    $ekorId = $satuans['Ekor'] ?? null;
    $potongId = $satuans['Potong'] ?? null;
    $kgId = $satuans['Kilogram'] ?? $satuans['Kg'] ?? null;
    $gramId = $satuans['Gram'] ?? null;
    
    $stmt = $pdo->prepare("
        UPDATE bahan_bakus SET
            satuan_id = ?,
            sub_satuan_1_id = ?,
            sub_satuan_1_konversi = 6.0000,
            sub_satuan_2_id = ?,
            sub_satuan_2_konversi = 1.5000,
            sub_satuan_3_id = ?,
            sub_satuan_3_konversi = 1500.0000
        WHERE id = 2
    ");
    $stmt->execute([$ekorId, $potongId, $kgId, $gramId]);
    
    echo "<span class='success'>✓ Updated conversion ratios</span>\n\n";
    
    // Verify
    $stmt = $pdo->query("
        SELECT 
            bb.id,
            bb.nama_bahan,
            s_utama.nama as satuan_utama,
            s_sub1.nama as sub_satuan_1,
            bb.sub_satuan_1_konversi,
            s_sub2.nama as sub_satuan_2,
            bb.sub_satuan_2_konversi,
            s_sub3.nama as sub_satuan_3,
            bb.sub_satuan_3_konversi
        FROM bahan_bakus bb
        LEFT JOIN satuans s_utama ON bb.satuan_id = s_utama.id
        LEFT JOIN satuans s_sub1 ON bb.sub_satuan_1_id = s_sub1.id
        LEFT JOIN satuans s_sub2 ON bb.sub_satuan_2_id = s_sub2.id
        LEFT JOIN satuans s_sub3 ON bb.sub_satuan_3_id = s_sub3.id
        WHERE bb.id = 2
    ");
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "UPDATED CONFIGURATION:\n";
    echo "Nama: {$updated['nama_bahan']}\n";
    echo "Satuan Utama: {$updated['satuan_utama']}\n";
    echo "Sub Satuan 1: {$updated['sub_satuan_1']} (konversi: {$updated['sub_satuan_1_konversi']}) → 1 Ekor = 6 Potong\n";
    echo "Sub Satuan 2: {$updated['sub_satuan_2']} (konversi: {$updated['sub_satuan_2_konversi']}) → 1 Ekor = 1.5 Kg\n";
    echo "Sub Satuan 3: {$updated['sub_satuan_3']} (konversi: {$updated['sub_satuan_3_konversi']}) → 1 Ekor = 1500 Gram\n\n";
    
    echo "EXPECTED DISPLAY:\n";
    echo "When viewing in Kilogram (with 28.3333 Ekor remaining):\n";
    echo "- Initial: 30 Ekor × 1.5 = 45 Kg\n";
    echo "- Production: 1.6667 Ekor × 1.5 = 2.5 Kg\n";
    echo "- Remaining: 28.3333 Ekor × 1.5 = 42.5 Kg\n\n";
    
    $pdo->commit();
    
    echo "<span class='success'>✅ SUCCESS! Conversion ratios fixed!</span>\n";
    echo "\nRefresh: <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2' style='color: #0ff;'>Laporan Stok</a>\n";
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
}
?></pre>
</body>
</html>