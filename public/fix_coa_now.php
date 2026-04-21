<?php
/**
 * EMERGENCY FIX untuk COA enum "BEBAN" error
 * Akses: http://127.0.0.1:8000/fix_coa_now.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>🚨 EMERGENCY FIX COA ENUM</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 EMERGENCY FIX COA ENUM "BEBAN"</h1>
        
        <?php
        try {
            // Database connection
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm;charset=utf8', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo '<div class="info"><strong>🔧 Memulai perbaikan enum tipe_akun...</strong></div>';
            
            // Step 1: Check current enum
            echo '<h3>Step 1: Memeriksa enum saat ini</h3>';
            $stmt = $pdo->query("SHOW COLUMNS FROM coas LIKE 'tipe_akun'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<pre>Current enum: ' . htmlspecialchars($result['Type']) . '</pre>';
            
            // Step 2: Update existing BEBAN values
            echo '<h3>Step 2: Update nilai BEBAN yang ada</h3>';
            $stmt = $pdo->prepare("UPDATE coas SET tipe_akun = 'Expense' WHERE tipe_akun = 'BEBAN'");
            $stmt->execute();
            $updated = $stmt->rowCount();
            echo '<div class="success">✅ Updated ' . $updated . ' records dari BEBAN ke Expense</div>';
            
            // Step 3: Alter enum
            echo '<h3>Step 3: Memperbaiki enum</h3>';
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
            echo '<div class="success">✅ Enum berhasil diupdate</div>';
            
            // Step 4: Verify
            echo '<h3>Step 4: Verifikasi</h3>';
            $stmt = $pdo->query("SHOW COLUMNS FROM coas LIKE 'tipe_akun'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<pre>New enum: ' . htmlspecialchars($result['Type']) . '</pre>';
            
            // Step 5: Test problematic update
            echo '<h3>Step 5: Test update yang bermasalah</h3>';
            $stmt = $pdo->prepare("
                UPDATE coas 
                SET nama_akun = 'Biaya TENAGA KERJA TIDAK LANGSUNG', 
                    tipe_akun = 'BEBAN', 
                    tanggal_saldo_awal = '2026-04-01 00:00:00',
                    updated_at = NOW()
                WHERE id = 166
            ");
            
            if ($stmt->execute()) {
                echo '<div class="success">✅ Test update BERHASIL! COA ID 166 berhasil diupdate dengan tipe "BEBAN"</div>';
            } else {
                echo '<div class="error">❌ Test update gagal</div>';
            }
            
            echo '<div class="success">';
            echo '<h2>🎉 PERBAIKAN SELESAI!</h2>';
            echo '<p><strong>✅ Enum tipe_akun sudah diperbaiki</strong></p>';
            echo '<p><strong>✅ COA dapat diupdate dengan tipe "BEBAN"</strong></p>';
            echo '<p><strong>✅ Error "Data truncated" sudah teratasi</strong></p>';
            echo '<p>Sekarang Anda dapat edit COA tanpa error.</p>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h2>❌ ERROR</h2>';
            echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>Solusi:</strong></p>';
            echo '<ul>';
            echo '<li>Pastikan database MySQL berjalan</li>';
            echo '<li>Pastikan kredensial database benar (host: 127.0.0.1, user: root, database: eadt_umkm)</li>';
            echo '<li>Pastikan Anda memiliki permission untuk ALTER TABLE</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div class="info">
            <h3>📋 Langkah Selanjutnya:</h3>
            <ol>
                <li>Kembali ke halaman edit COA ID 166</li>
                <li>Coba ubah tipe akun ke "BEBAN"</li>
                <li>Simpan - seharusnya tidak ada error lagi</li>
            </ol>
        </div>
    </div>
</body>
</html>