<?php
// Halaman testing multi-tenant untuk local development
echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMCOST - Multi-Tenant Testing</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; }
        .test-section h3 { color: #495057; margin-bottom: 15px; }
        .status-ok { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .status-info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .card { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; }
        .user-card { border-left: 4px solid #007bff; }
        .user-card.owner { border-left-color: #28a745; }
        .user-card.kasir { border-left-color: #ffc107; }
        .user-card.pegawai { border-left-color: #dc3545; }
        .data-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .data-table th, .data-table td { padding: 8px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .data-table th { background: #f8f9fa; font-weight: bold; }
        .highlight { background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏢 SIMCOST - Multi-Tenant Testing</h1>
        
        <div class="test-section status-info">
            <h3>📋 Multi-Tenant System Test</h3>
            <p><strong>Purpose:</strong> Memastikan dashboard menampilkan data sesuai user yang login (multi-tenant isolation)</p>
            <p><strong>Current User:</strong> User ID ' . (session('user_id', 'Not logged in')) . ' - Role: ' . (session('user_role', 'Unknown') . '</p>
        </div>

        <div class="test-section">
            <h3>👥 Test Different User Sessions</h3>
            <p>Login dengan user berbeda untuk melihat data yang berbeda di dashboard:</p>
            <div class="grid">
                <div class="card user-card owner">
                    <h4>👑 Owner (User ID 1)</h4>
                    <p><strong>Data:</strong> 15 Pegawai, 25 Produk, 45 Pelanggan</p>
                    <a href="/login" class="btn btn-success">Login as Owner</a>
                    <small>Email: arkan@gmail.com</small>
                </div>
                <div class="card user-card kasir">
                    <h4>💰 Kasir (User ID 2)</h4>
                    <p><strong>Data:</strong> 8 Pegawai, 18 Produk, 32 Pelanggan</p>
                    <a href="/login" class="btn btn-warning">Login as Kasir</a>
                    <small>Email: kasir@example.com</small>
                </div>
                <div class="card user-card pegawai">
                    <h4>👨‍💼 Pegawai Pembelian (User ID 3)</h4>
                    <p><strong>Data:</strong> 12 Pegawai, 20 Produk, 28 Pelanggan</p>
                    <a href="/login" class="btn">Login as Pegawai</a>
                    <small>Email: pegawai@example.com</small>
                </div>
                <div class="card user-card pegawai">
                    <h4>👷 Pegawai (User ID 4)</h4>
                    <p><strong>Data:</strong> 6 Pegawai, 12 Produk, 20 Pelanggan</p>
                    <a href="/login" class="btn">Login as Staff</a>
                    <small>Email: staff@example.com</small>
                </div>
            </div>
        </div>

        <div class="test-section status-ok">
            <h3>✅ Multi-Tenant Verification</h3>
            <div class="highlight">
                <strong>🔍 Cara Testing Multi-Tenant:</strong><br>
                1. Login dengan user yang berbeda<br>
                2. Akses dashboard <a href="/dashboard" class="btn">/dashboard</a><br>
                3. Perhatikan data yang berbeda untuk setiap user<br>
                4. Pastikan tidak ada data dari user lain yang muncul
            </div>
        </div>

        <div class="test-section">
            <h3>📊 Expected Data per User</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Role</th>
                        <th>Pegawai</th>
                        <th>Produk</th>
                        <th>Pelanggan</th>
                        <th>Total Sales</th>
                        <th>Bank Accounts</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>1</strong></td>
                        <td>Owner</td>
                        <td>15</td>
                        <td>25</td>
                        <td>45</td>
                        <td>Rp 28.000.000</td>
                        <td>3 Accounts</td>
                    </tr>
                    <tr>
                        <td><strong>2</strong></td>
                        <td>Kasir</td>
                        <td>8</td>
                        <td>18</td>
                        <td>32</td>
                        <td>Rp 18.000.000</td>
                        <td>2 Accounts</td>
                    </tr>
                    <tr>
                        <td><strong>3</strong></td>
                        <td>Pegawai Pembelian</td>
                        <td>12</td>
                        <td>20</td>
                        <td>28</td>
                        <td>Rp 21.000.000</td>
                        <td>3 Accounts</td>
                    </tr>
                    <tr>
                        <td><strong>4</strong></td>
                        <td>Pegawai</td>
                        <td>6</td>
                        <td>12</td>
                        <td>20</td>
                        <td>Rp 13.000.000</td>
                        <td>2 Accounts</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="test-section">
            <h3>🔗 Quick Access</h3>
            <div class="grid">
                <a href="/dashboard" class="btn btn-success">📊 Dashboard</a>
                <a href="/login" class="btn">🔐 Switch User</a>
                <a href="/master-data/btkl/create" class="btn">📝 BTKL Form</a>
                <a href="/test_local.php" class="btn">🧪 Local Test</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 8px;">
            <h3>🎯 Multi-Tenant Success Criteria</h3>
            <p>✅ Dashboard menampilkan data berbeda untuk setiap user</p>
            <p>✅ Tidak ada data leakage antar user</p>
            <p>✅ User hanya melihat data miliknya sendiri</p>
            <p>✅ Chart dan bank accounts sesuai user</p>
        </div>
    </div>
</body>
</html>';
