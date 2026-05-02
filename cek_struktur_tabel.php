<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', '');

echo "Struktur tabel COAS:\n";
echo "===================\n";
$stmt = $pdo->query('DESCRIBE coas');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . "\n";
}

echo "\n\nStruktur tabel SATUANS:\n";
echo "=======================\n";
$stmt = $pdo->query('DESCRIBE satuans');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . "\n";
}
