<?php
// Simple web-based stock update tool
// Place this file in your Laravel public folder and access via browser

// Load Laravel environment
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? '';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Bahan Pendukung Stock</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Update Bahan Pendukung Stock</h1>
        <p>This tool will update all bahan pendukung stock from 50 to 200 units.</p>
        
        <?php
        if ($_POST['action'] ?? '' === 'update') {
            try {
                $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Update stock
                $stmt = $pdo->prepare("UPDATE bahan_pendukungs SET stok = 200");
                $stmt->execute();
                $updated = $stmt->rowCount();
                
                echo "<div class='alert alert-success'>✅ Successfully updated $updated bahan pendukung records to 200 stock!</div>";
                
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
            }
        }
        
        // Show current data
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->query("SELECT id, nama_bahan, stok, harga_satuan FROM bahan_pendukungs ORDER BY id");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>📊 Current Bahan Pendukung Stock:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nama Bahan</th><th>Stock</th><th>Harga Satuan</th></tr>";
            
            foreach ($results as $row) {
                $stockColor = $row['stok'] == 50 ? 'color: red; font-weight: bold;' : 'color: green; font-weight: bold;';
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['nama_bahan']}</td>";
                echo "<td style='$stockColor'>{$row['stok']}</td>";
                echo "<td>Rp " . number_format($row['harga_satuan'], 0, ',', '.') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Check if any stock is still 50
            $needsUpdate = false;
            foreach ($results as $row) {
                if ($row['stok'] == 50) {
                    $needsUpdate = true;
                    break;
                }
            }
            
            if ($needsUpdate) {
                echo "<div class='alert alert-danger'>⚠️ Some items still have stock = 50. Click the button below to update them to 200.</div>";
                echo "<form method='POST'>";
                echo "<input type='hidden' name='action' value='update'>";
                echo "<button type='submit' class='btn btn-success' onclick='return confirm(\"Are you sure you want to update all bahan pendukung stock to 200?\")'>🔄 Update All Stock to 200</button>";
                echo "</form>";
            } else {
                echo "<div class='alert alert-success'>✅ All bahan pendukung stock is already set to 200!</div>";
                echo "<p><strong>Next steps:</strong></p>";
                echo "<ol>";
                echo "<li>Go back to your stock report page</li>";
                echo "<li>Refresh the page (Ctrl+F5)</li>";
                echo "<li>The stock should now show 200.00 Liter instead of 50.00 Liter</li>";
                echo "</ol>";
            }
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>❌ Database connection error: " . $e->getMessage() . "</div>";
            echo "<p>Please check your database credentials in the .env file:</p>";
            echo "<ul>";
            echo "<li>DB_HOST: $host</li>";
            echo "<li>DB_PORT: $port</li>";
            echo "<li>DB_DATABASE: $database</li>";
            echo "<li>DB_USERNAME: $username</li>";
            echo "</ul>";
        }
        ?>
        
        <hr>
        <p><small>💡 <strong>Tip:</strong> After updating, refresh your stock report page to see the changes.</small></p>
    </div>
</body>
</html>