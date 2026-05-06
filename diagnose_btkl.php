<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', 'coe12345');

// Semua user
echo "=== USERS ===\n";
foreach ($pdo->query("SELECT id, name, email FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo $r['id'] . " | " . $r['name'] . " | " . $r['email'] . "\n";
}

// Jabatan BTKL per user
echo "\n=== JABATAN BTKL PER USER ===\n";
foreach ($pdo->query("SELECT j.id, j.nama, j.user_id, j.tarif, COUNT(p.id) as jml_pegawai
    FROM jabatans j
    LEFT JOIN pegawais p ON p.jabatan_id = j.id AND p.user_id = j.user_id
    WHERE j.kategori = 'btkl'
    GROUP BY j.id, j.nama, j.user_id, j.tarif
    ORDER BY j.user_id, j.nama")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "jabatan_id={$r['id']} | nama={$r['nama']} | user_id={$r['user_id']} | tarif={$r['tarif']} | pegawai={$r['jml_pegawai']}\n";
}
