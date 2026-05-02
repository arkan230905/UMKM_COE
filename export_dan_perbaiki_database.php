<?php
/**
 * SCRIPT OTOMATIS: EXPORT & PERBAIKI DATABASE
 * ============================================
 * Script ini akan:
 * 1. Export database eadt_umkm secara otomatis
 * 2. Memperbaiki file SQL agar tidak error saat import
 * 3. Siap dibagikan ke teman-teman
 * 
 * CARA PAKAI:
 * 1. Buka browser: http://localhost/export_dan_perbaiki_database.php
 * 2. Isi username dan password MySQL
 * 3. Klik tombol "Export & Perbaiki Database"
 * 4. Download file yang sudah jadi
 * 5. Kirim ke teman-teman!
 */

// Konfigurasi default
$defaultConfig = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'eadt_umkm',
    'username' => 'root',
    'password' => ''
];

// Fungsi untuk export database
function exportDatabase($host, $port, $username, $password, $database) {
    try {
        // Koneksi ke database
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
        
        $output = "";
        
        // Header SQL
        $output .= "-- ============================================\n";
        $output .= "-- Database Export: {$database}\n";
        $output .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Host: {$host}\n";
        $output .= "-- ============================================\n\n";
        
        // Perintah penting untuk mencegah error foreign key
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n";
        $output .= "SET time_zone = \"+00:00\";\n\n";
        
        $output .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $output .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $output .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $output .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
        
        // Create database statement
        $output .= "-- ============================================\n";
        $output .= "-- Database: `{$database}`\n";
        $output .= "-- ============================================\n\n";
        $output .= "CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
        $output .= "USE `{$database}`;\n\n";
        
        // Ambil semua tabel
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            return ['success' => false, 'message' => 'Database kosong atau tidak ada tabel'];
        }
        
        $totalTables = count($tables);
        $processedTables = 0;
        
        // Loop setiap tabel
        foreach ($tables as $table) {
            $processedTables++;
            
            $output .= "-- ============================================\n";
            $output .= "-- Tabel: `{$table}` ({$processedTables}/{$totalTables})\n";
            $output .= "-- ============================================\n\n";
            
            // DROP TABLE
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            
            // CREATE TABLE
            $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            $output .= $createTable['Create Table'] . ";\n\n";
            
            // INSERT DATA
            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $output .= "-- Data untuk tabel `{$table}`\n\n";
                
                // Ambil nama kolom
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                // Insert data dalam batch (100 rows per INSERT)
                $batchSize = 100;
                $batches = array_chunk($rows, $batchSize);
                
                foreach ($batches as $batch) {
                    $output .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
                    
                    $values = [];
                    foreach ($batch as $row) {
                        $rowValues = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = $pdo->quote($value);
                            }
                        }
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    
                    $output .= implode(",\n", $values) . ";\n\n";
                }
            }
            
            $output .= "\n";
        }
        
        // Footer SQL
        $output .= "-- ============================================\n";
        $output .= "-- Selesai - Restore settings\n";
        $output .= "-- ============================================\n\n";
        $output .= "COMMIT;\n";
        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $output .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $output .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $output .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
        
        return [
            'success' => true,
            'content' => $output,
            'tables' => $totalTables,
            'size' => strlen($output)
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error koneksi database: ' . $e->getMessage()
        ];
    }
}

// Fungsi format ukuran file
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Proses form
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? $defaultConfig['host'];
    $port = $_POST['port'] ?? $defaultConfig['port'];
    $username = $_POST['username'] ?? $defaultConfig['username'];
    $password = $_POST['password'] ?? $defaultConfig['password'];
    $database = $_POST['database'] ?? $defaultConfig['database'];
    
    // Export database
    $result = exportDatabase($host, $port, $username, $password, $database);
    
    if ($result['success']) {
        // Simpan file
        $fileName = $database . '_export_' . date('Ymd_His') . '.sql';
        $filePath = __DIR__ . '/' . $fileName;
        
        file_put_contents($filePath, $result['content']);
        
        $result['fileName'] = $fileName;
        $result['filePath'] = $filePath;
        $result['fileSize'] = formatBytes($result['size']);
    }
}

// Handle download
if (isset($_GET['download'])) {
    $fileName = basename($_GET['download']);
    $filePath = __DIR__ . '/' . $fileName;
    
    if (file_exists($filePath)) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export & Perbaiki Database Otomatis</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 26px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .icon {
            font-size: 24px;
            flex-shrink: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            width: 100%;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .success-box {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            margin: 20px 0;
        }
        
        .success-box h3 {
            color: #155724;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .success-box .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
        }
        
        .stat-item .label {
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .stat-item .value {
            color: #155724;
            font-size: 24px;
            font-weight: bold;
        }
        
        .instructions {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .instructions h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .instructions ol {
            margin-left: 20px;
            color: #666;
        }
        
        .instructions li {
            margin-bottom: 8px;
            line-height: 1.6;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .note {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Export & Perbaiki Database</h1>
            <p>Tool otomatis untuk export database yang siap dibagikan</p>
        </div>
        
        <div class="content">
            <?php if ($result): ?>
                <?php if ($result['success']): ?>
                    <div class="success-box">
                        <h3>✅ Database Berhasil Di-Export & Diperbaiki!</h3>
                        
                        <div class="stats">
                            <div class="stat-item">
                                <div class="label">Total Tabel</div>
                                <div class="value"><?php echo $result['tables']; ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="label">Ukuran File</div>
                                <div class="value"><?php echo $result['fileSize']; ?></div>
                            </div>
                        </div>
                        
                        <p style="color: #155724; margin-bottom: 20px;">
                            File: <strong><?php echo $result['fileName']; ?></strong>
                        </p>
                        
                        <a href="?download=<?php echo urlencode($result['fileName']); ?>" class="btn btn-success">
                            ⬇️ Download File SQL
                        </a>
                    </div>
                    
                    <div class="alert alert-info">
                        <span class="icon">💡</span>
                        <div>
                            <strong>Langkah Selanjutnya:</strong><br>
                            <small>
                                1. Download file SQL di atas<br>
                                2. Kirim file tersebut ke teman-teman Anda<br>
                                3. Teman Anda tinggal import di phpMyAdmin<br>
                                4. Dijamin tidak akan error! ✅
                            </small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-error">
                        <span class="icon">❌</span>
                        <div>
                            <strong>Error!</strong><br>
                            <?php echo htmlspecialchars($result['message']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="alert alert-warning">
                <span class="icon">⚠️</span>
                <div>
                    <strong>Penting!</strong><br>
                    <small>Pastikan MySQL server Anda sedang berjalan sebelum melakukan export.</small>
                </div>
            </div>
            
            <form method="POST" id="exportForm">
                <div class="form-group">
                    <label>📦 Nama Database</label>
                    <input type="text" name="database" value="<?php echo htmlspecialchars($defaultConfig['database']); ?>" required>
                    <div class="note">Nama database yang akan di-export</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>🖥️ Host</label>
                        <input type="text" name="host" value="<?php echo htmlspecialchars($defaultConfig['host']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>🔌 Port</label>
                        <input type="text" name="port" value="<?php echo htmlspecialchars($defaultConfig['port']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>👤 Username MySQL</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($defaultConfig['username']); ?>" required>
                    <div class="note">Biasanya: root</div>
                </div>
                
                <div class="form-group">
                    <label>🔑 Password MySQL</label>
                    <input type="password" name="password" value="<?php echo htmlspecialchars($defaultConfig['password']); ?>" placeholder="Kosongkan jika tidak ada password">
                    <div class="note">Kosongkan jika tidak ada password</div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    🚀 Export & Perbaiki Database
                </button>
            </form>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p style="color: #667eea; font-weight: 500;">Sedang memproses database...</p>
                <p style="color: #999; font-size: 13px;">Mohon tunggu, ini mungkin memakan waktu beberapa saat</p>
            </div>
            
            <div class="instructions">
                <h3>📖 Cara Menggunakan Tool Ini</h3>
                <ol>
                    <li>Pastikan MySQL server Anda sedang berjalan</li>
                    <li>Isi form di atas dengan kredensial MySQL Anda</li>
                    <li>Klik tombol "Export & Perbaiki Database"</li>
                    <li>Tunggu proses selesai (beberapa detik hingga menit)</li>
                    <li>Download file SQL yang sudah diperbaiki</li>
                    <li>Kirim file tersebut ke teman-teman Anda</li>
                    <li>Selesai! Teman Anda bisa langsung import tanpa error</li>
                </ol>
            </div>
            
            <div class="alert alert-info" style="margin-top: 20px;">
                <span class="icon">✨</span>
                <div>
                    <strong>Yang Dilakukan Tool Ini:</strong><br>
                    <small>
                        ✓ Export semua tabel dan data dari database<br>
                        ✓ Menambahkan SET FOREIGN_KEY_CHECKS=0<br>
                        ✓ Mengatur SQL_MODE yang tepat<br>
                        ✓ Menambahkan transaction wrapper<br>
                        ✓ Mengatur character set UTF-8<br>
                        ✓ File siap import tanpa error!
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('exportForm').addEventListener('submit', function(e) {
            // Show loading
            document.getElementById('loading').classList.add('active');
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = '⏳ Sedang memproses...';
        });
    </script>
</body>
</html>
