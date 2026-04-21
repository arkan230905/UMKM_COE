<?php
/**
 * Direct database fix untuk enum tipe_akun COA
 */

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 Memperbaiki enum tipe_akun untuk COA...\n\n";

    // Step 1: Check current enum
    echo "Step 1: Memeriksa enum saat ini...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM coas LIKE 'tipe_akun'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current: " . $result['Type'] . "\n\n";

    // Step 2: Update existing 'BEBAN' values to 'Expense' (if any)
    echo "Step 2: Mengupdate nilai 'BEBAN' yang ada...\n";
    $stmt = $pdo->prepare("UPDATE coas SET tipe_akun = 'Expense' WHERE tipe_akun = 'BEBAN'");
    $stmt->execute();
    echo "✅ Updated records\n\n";

    // Step 3: Alter enum to include all values
    echo "Step 3: Mengupdate enum...\n";
    $sql = "ALTER TABLE coas MODIFY COLUMN tipe_akun ENUM(
        'Asset', 'Aset', 'ASET',
        'Liability', 'Kewajiban', 'KEWAJIBAN', 
        'Equity', 'Ekuitas', 'Modal', 'MODAL',
        'Revenue', 'Pendapatan', 'PENDAPATAN',
        'Expense', 'Beban', 'BEBAN', 'Biaya',
        'Biaya Bahan Baku', 'Biaya Tenaga Kerja Langsung', 
        'Biaya Overhead Pabrik', 'Biaya Tenaga Kerja Tidak Langsung', 
        'BOP Tidak Langsung Lainnya'
    ) NOT NULL";
    
    $pdo->exec($sql);
    echo "✅ Enum berhasil diupdate\n\n";

    // Step 4: Verify
    echo "Step 4: Verifikasi...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM coas LIKE 'tipe_akun'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "New: " . $result['Type'] . "\n\n";

    // Step 5: Test the problematic update
    echo "Step 5: Testing update yang bermasalah...\n";
    $stmt = $pdo->prepare("
        UPDATE coas 
        SET nama_akun = 'Biaya TENAGA KERJA TIDAK LANGSUNG', 
            tipe_akun = 'BEBAN', 
            tanggal_saldo_awal = '2026-04-01 00:00:00',
            updated_at = NOW()
        WHERE id = 166
    ");
    
    if ($stmt->execute()) {
        echo "✅ Test update berhasil!\n";
    } else {
        echo "❌ Test update gagal\n";
    }

    echo "\n🎉 SELESAI!\n";
    echo "Enum tipe_akun sudah diperbaiki.\n";
    echo "Controller juga sudah diupdate untuk mendukung semua nilai enum.\n";
    echo "Sekarang Anda dapat edit COA tanpa error.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>