<?php
// Halaman testing untuk local development
echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMCOST - Local Development Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; }
        .test-section h3 { color: #495057; margin-bottom: 15px; }
        .status-ok { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .status-warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .card { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 SIMCOST - Local Development Testing</h1>
        
        <div class="test-section status-ok">
            <h3>✅ Server Status</h3>
            <p><strong>Local Server:</strong> http://127.0.0.1:8000</p>
            <p><strong>Environment:</strong> ' . (app()->environment('local') ? 'LOCAL (Development Mode)' : 'Production') . '</p>
            <p><strong>Laravel Version:</strong> ' . app()->version() . '</p>
            <p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>
        </div>

        <div class="test-section status-ok">
            <h3>🔐 Authentication Test</h3>
            <p>Mock authentication system aktif untuk local development</p>
            <div class="grid">
                <div class="card">
                    <h4>Owner Login</h4>
                    <a href="/login" class="btn btn-success">Test Owner Login</a>
                    <p>Email: arkan@gmail.com<br>Password: apapun</p>
                </div>
                <div class="card">
                    <h4>Pegawai Login</h4>
                    <a href="/login" class="btn">Test Pegawai Login</a>
                    <p>Email: apapun<br>Password: apapun</p>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h3>📋 BTKL Feature Test</h3>
            <p>Testing halaman BTKL (Biaya Tenaga Kerja Langsung)</p>
            <div class="grid">
                <a href="/master-data/btkl/create" class="btn">📝 BTKL Create Form</a>
                <a href="/master-data/btkl" class="btn">📊 BTKL List</a>
                <a href="/master-data/jabatan" class="btn">💼 Jabatan Management</a>
                <a href="/master-data/pegawai" class="btn">👥 Pegawai Management</a>
            </div>
        </div>

        <div class="test-section">
            <h3>🌐 Navigation Test</h3>
            <div class="grid">
                <a href="/" class="btn">🏠 Home</a>
                <a href="/login" class="btn">🔐 Login</a>
                <a href="/register" class="btn">📝 Register</a>
                <a href="/dashboard" class="btn">📊 Dashboard</a>
            </div>
        </div>

        <div class="test-section status-warning">
            <h3>⚠️ Local Development Notes</h3>
            <ul>
                <li>✅ <strong>Frontend/UI:</strong> Semua fitur visual berfungsi</li>
                <li>✅ <strong>Forms:</strong> Form BTKL dan input validation berfungsi</li>
                <li>✅ <strong>Authentication:</strong> Mock login tanpa database</li>
                <li>❌ <strong>Database:</strong> Tidak ada koneksi database (untuk development)</li>
                <li>❌ <strong>Data Persistence:</strong> Data tidak tersimpan (development mode)</li>
            </ul>
        </div>

        <div class="test-section">
            <h3>🎯 Development Workflow</h3>
            <ol>
                <li><strong>UI Development:</strong> Gunakan local server untuk styling & layout</li>
                <li><strong>Form Testing:</strong> Test BTKL dropdown dan calculations</li>
                <li><strong>JavaScript:</strong> Test interactions dan dynamic content</li>
                <li><strong>Responsive:</strong> Test mobile & tablet views</li>
                <li><strong>Deployment:</strong> Push ke GitHub dan deploy ke hosting</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 8px;">
            <h3>🚀 Ready for Development!</h3>
            <p>Local development environment sudah siap. Silakan mulai development BTKL features.</p>
            <p><strong>Next Steps:</strong> Test login → Access BTKL page → Fix dropdown → Deploy ke hosting</p>
        </div>
    </div>
</body>
</html>';
