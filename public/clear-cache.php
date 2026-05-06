<?php
/**
 * ============================================================
 * CACHE CLEARER FOR HOSTING (NO SSH ACCESS)
 * ============================================================
 * 
 * CARA PAKAI:
 * 1. Upload file ini ke folder: public/
 * 2. Akses via browser: https://your-domain.com/clear-cache.php
 * 3. HAPUS file ini setelah selesai!
 * 
 * PENTING: File ini hanya untuk emergency jika tidak ada SSH
 * ============================================================
 */

// Security: Uncomment dan ganti dengan password Anda
// $PASSWORD = 'your-secret-password-here';
// if (!isset($_GET['password']) || $_GET['password'] !== $PASSWORD) {
//     die('❌ Unauthorized access!');
// }

echo "<!DOCTYPE html>
<html>
<head>
    <title>Cache Clearer</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 40px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        h1 {
            margin: 0 0 30px 0;
            font-size: 2.5rem;
        }
        .result {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #4ade80;
        }
        .success {
            color: #4ade80;
            font-weight: bold;
        }
        .error {
            color: #f87171;
            font-weight: bold;
        }
        .warning {
            background: rgba(251, 191, 36, 0.2);
            border-left-color: #fbbf24;
            color: #fbbf24;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        code {
            background: rgba(0,0,0,0.3);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧹 Cache Clearer</h1>
";

try {
    // Load Laravel
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<div class='result'>";
    
    // Clear cache
    echo "<p>🔄 Clearing application cache...</p>";
    $kernel->call('cache:clear');
    echo "<p class='success'>✅ Application cache cleared</p>";
    
    // Clear config
    echo "<p>🔄 Clearing configuration cache...</p>";
    $kernel->call('config:clear');
    echo "<p class='success'>✅ Configuration cache cleared</p>";
    
    // Clear route
    echo "<p>🔄 Clearing route cache...</p>";
    $kernel->call('route:clear');
    echo "<p class='success'>✅ Route cache cleared</p>";
    
    // Clear view
    echo "<p>🔄 Clearing compiled views...</p>";
    $kernel->call('view:clear');
    echo "<p class='success'>✅ Compiled views cleared</p>";
    
    // Clear optimize
    echo "<p>🔄 Clearing optimization cache...</p>";
    $kernel->call('optimize:clear');
    echo "<p class='success'>✅ Optimization cache cleared</p>";
    
    echo "</div>";
    
    echo "<div class='result'>
        <h2>🎉 Success!</h2>
        <p>All caches have been cleared successfully.</p>
        <p><strong>Next steps:</strong></p>
        <ol>
            <li>Clear your browser cache (Ctrl + Shift + Delete)</li>
            <li>Hard refresh the page (Ctrl + F5)</li>
            <li>Test your dashboard</li>
            <li><strong style='color:#f87171;'>DELETE THIS FILE IMMEDIATELY!</strong></li>
        </ol>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='result' style='border-left-color: #f87171;'>
        <p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>
        <p>Please check:</p>
        <ul>
            <li>File path is correct</li>
            <li>Vendor folder exists</li>
            <li>Laravel is properly installed</li>
        </ul>
    </div>";
}

echo "
        <div class='warning'>
            <h3>⚠️ SECURITY WARNING</h3>
            <p><strong>DELETE THIS FILE IMMEDIATELY AFTER USE!</strong></p>
            <p>This file can expose your application to security risks.</p>
            <p>To delete via cPanel:</p>
            <ol>
                <li>Go to File Manager</li>
                <li>Navigate to <code>public/</code></li>
                <li>Find <code>clear-cache.php</code></li>
                <li>Right-click → Delete</li>
            </ol>
        </div>
    </div>
</body>
</html>";
?>
