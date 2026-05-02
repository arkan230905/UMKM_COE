<?php
/**
 * Script untuk memperbaiki file SQL export agar tidak error saat import
 * Menambahkan perintah disable foreign key checks dan optimasi lainnya
 * 
 * Cara pakai:
 * 1. Upload file SQL yang ingin diperbaiki ke folder yang sama dengan script ini
 * 2. Buka browser: http://localhost/fix_sql_export.php
 * 3. Pilih file SQL yang ingin diperbaiki
 * 4. Klik "Perbaiki File SQL"
 * 5. Download file yang sudah diperbaiki
 */

// Fungsi untuk memperbaiki file SQL
function fixSQLFile($inputFile, $outputFile) {
    // Baca isi file SQL
    $sqlContent = file_get_contents($inputFile);
    
    if ($sqlContent === false) {
        return ['success' => false, 'message' => 'Gagal membaca file SQL'];
    }
    
    // Header SQL yang akan ditambahkan di awal file
    $header = "-- ============================================\n";
    $header .= "-- File SQL yang sudah diperbaiki\n";
    $header .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
    $header .= "-- ============================================\n\n";
    $header .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $header .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $header .= "SET AUTOCOMMIT = 0;\n";
    $header .= "START TRANSACTION;\n";
    $header .= "SET time_zone = \"+00:00\";\n\n";
    $header .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
    $header .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
    $header .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
    $header .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
    
    // Footer SQL yang akan ditambahkan di akhir file
    $footer = "\n\n-- ============================================\n";
    $footer .= "-- Selesai - Restore settings\n";
    $footer .= "-- ============================================\n\n";
    $footer .= "COMMIT;\n";
    $footer .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $footer .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
    $footer .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
    $footer .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
    
    // Hapus header lama jika ada
    $sqlContent = preg_replace('/^\/\*!40101 SET @OLD_CHARACTER_SET_CLIENT.*?\*\/;\s*/s', '', $sqlContent);
    $sqlContent = preg_replace('/^SET FOREIGN_KEY_CHECKS.*?;\s*/m', '', $sqlContent);
    $sqlContent = preg_replace('/^SET SQL_MODE.*?;\s*/m', '', $sqlContent);
    
    // Gabungkan header + content + footer
    $fixedContent = $header . $sqlContent . $footer;
    
    // Simpan ke file baru
    $result = file_put_contents($outputFile, $fixedContent);
    
    if ($result === false) {
        return ['success' => false, 'message' => 'Gagal menyimpan file yang sudah diperbaiki'];
    }
    
    $fileSize = formatBytes(filesize($outputFile));
    
    return [
        'success' => true, 
        'message' => 'File berhasil diperbaiki!',
        'output_file' => $outputFile,
        'file_size' => $fileSize
    ];
}

// Fungsi untuk format ukuran file
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Proses form jika ada submit
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])) {
    $uploadedFile = $_FILES['sql_file'];
    
    // Validasi file
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        $result = ['success' => false, 'message' => 'Error saat upload file'];
    } else {
        $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        
        if ($fileExtension !== 'sql') {
            $result = ['success' => false, 'message' => 'File harus berformat .sql'];
        } else {
            // Nama file output
            $originalName = pathinfo($uploadedFile['name'], PATHINFO_FILENAME);
            $outputFileName = $originalName . '_fixed.sql';
            $outputFilePath = __DIR__ . '/' . $outputFileName;
            
            // Perbaiki file
            $result = fixSQLFile($uploadedFile['tmp_name'], $outputFilePath);
            
            if ($result['success']) {
                $result['download_link'] = $outputFileName;
            }
        }
    }
}

// Scan file SQL yang ada di folder
$sqlFiles = glob(__DIR__ . '/*.sql');
$availableFiles = [];
foreach ($sqlFiles as $file) {
    $fileName = basename($file);
    // Skip file yang sudah diperbaiki
    if (strpos($fileName, '_fixed.sql') === false) {
        $availableFiles[] = [
            'name' => $fileName,
            'size' => formatBytes(filesize($file)),
            'date' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perbaiki File SQL Export</title>
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
            max-width: 800px;
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
            font-size: 28px;
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
            align-items: center;
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
        
        .icon {
            font-size: 24px;
        }
        
        .upload-section {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            transition: all 0.3s;
        }
        
        .upload-section:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .upload-section input[type="file"] {
            display: none;
        }
        
        .upload-label {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .upload-label:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .file-name {
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
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
        }
        
        .files-list {
            margin-top: 30px;
        }
        
        .files-list h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .file-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .file-info {
            flex: 1;
        }
        
        .file-info strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        
        .file-info small {
            color: #666;
            font-size: 12px;
        }
        
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .instructions h3 {
            color: #856404;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .instructions ol {
            margin-left: 20px;
            color: #856404;
        }
        
        .instructions li {
            margin-bottom: 8px;
            line-height: 1.6;
        }
        
        .download-section {
            text-align: center;
            padding: 20px;
            background: #d4edda;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .download-section h3 {
            color: #155724;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Perbaiki File SQL Export</h1>
            <p>Tool untuk memperbaiki file SQL agar tidak error saat import</p>
        </div>
        
        <div class="content">
            <?php if ($result): ?>
                <?php if ($result['success']): ?>
                    <div class="alert alert-success">
                        <span class="icon">✅</span>
                        <div>
                            <strong>Berhasil!</strong><br>
                            <?php echo $result['message']; ?><br>
                            <small>Ukuran file: <?php echo $result['file_size']; ?></small>
                        </div>
                    </div>
                    
                    <div class="download-section">
                        <h3>📥 File Siap Didownload</h3>
                        <p style="margin-bottom: 15px; color: #155724;">
                            File SQL Anda sudah diperbaiki dan siap dibagikan ke teman-teman!
                        </p>
                        <a href="<?php echo $result['download_link']; ?>" class="btn btn-success" download>
                            ⬇️ Download File yang Sudah Diperbaiki
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-error">
                        <span class="icon">❌</span>
                        <div>
                            <strong>Error!</strong><br>
                            <?php echo $result['message']; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-section">
                    <h3 style="margin-bottom: 15px; color: #333;">📤 Upload File SQL</h3>
                    <p style="color: #666; margin-bottom: 20px;">
                        Pilih file SQL export dari database Anda
                    </p>
                    
                    <label for="sql_file" class="upload-label">
                        📁 Pilih File SQL
                    </label>
                    <input type="file" name="sql_file" id="sql_file" accept=".sql" required>
                    
                    <div class="file-name" id="fileName">Belum ada file dipilih</div>
                    
                    <button type="submit" class="btn btn-primary" style="margin-top: 20px;" id="submitBtn" disabled>
                        🔧 Perbaiki File SQL
                    </button>
                </div>
            </form>
            
            <?php if (!empty($availableFiles)): ?>
                <div class="files-list">
                    <h3>📋 File SQL yang Tersedia di Folder Ini</h3>
                    <div class="alert alert-info">
                        <span class="icon">ℹ️</span>
                        <div>
                            <small>File-file di bawah ini bisa langsung diperbaiki dengan upload di atas</small>
                        </div>
                    </div>
                    
                    <?php foreach ($availableFiles as $file): ?>
                        <div class="file-item">
                            <div class="file-info">
                                <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                                <small>
                                    Ukuran: <?php echo $file['size']; ?> | 
                                    Tanggal: <?php echo $file['date']; ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="instructions">
                <h3>📖 Cara Menggunakan Tool Ini</h3>
                <ol>
                    <li>Export database Anda dari phpMyAdmin (format SQL)</li>
                    <li>Upload file SQL tersebut menggunakan form di atas</li>
                    <li>Klik tombol "Perbaiki File SQL"</li>
                    <li>Download file yang sudah diperbaiki (nama file akan ditambah "_fixed")</li>
                    <li>Kirim file yang sudah diperbaiki ke teman-teman Anda</li>
                    <li>Teman Anda tinggal import file tersebut tanpa error!</li>
                </ol>
            </div>
            
            <div class="alert alert-info" style="margin-top: 20px;">
                <span class="icon">💡</span>
                <div>
                    <strong>Yang Diperbaiki oleh Tool Ini:</strong><br>
                    <small>
                        ✓ Menambahkan SET FOREIGN_KEY_CHECKS=0<br>
                        ✓ Mengatur SQL_MODE yang tepat<br>
                        ✓ Menambahkan transaction wrapper<br>
                        ✓ Mengatur character set UTF-8<br>
                        ✓ Restore semua setting di akhir file
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Update nama file saat dipilih
        document.getElementById('sql_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Belum ada file dipilih';
            document.getElementById('fileName').textContent = fileName;
            document.getElementById('submitBtn').disabled = !e.target.files[0];
        });
        
        // Validasi sebelum submit
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('sql_file');
            if (!fileInput.files[0]) {
                e.preventDefault();
                alert('Silakan pilih file SQL terlebih dahulu!');
                return false;
            }
            
            const fileName = fileInput.files[0].name;
            if (!fileName.toLowerCase().endsWith('.sql')) {
                e.preventDefault();
                alert('File harus berformat .sql!');
                return false;
            }
            
            // Show loading
            document.getElementById('submitBtn').textContent = '⏳ Sedang memproses...';
            document.getElementById('submitBtn').disabled = true;
        });
    </script>
</body>
</html>
