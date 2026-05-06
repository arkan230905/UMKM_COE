<?php

// Simple local development index that bypasses database
echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMCOST - Local Development</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        .status { background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #27ae60; }
        .links { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0; }
        .link-card { background: #3498db; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; transition: transform 0.2s; }
        .link-card:hover { transform: translateY(-2px); background: #2980b9; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 SIMCOST - Local Development Mode</h1>
        
        <div class="status">
            ✅ <strong>Local Development Server Active</strong><br>
            📍 Running on: http://127.0.0.1:8000<br>
            🛠️ Database queries disabled for local development
        </div>

        <div class="warning">
            ⚠️ <strong>Note:</strong> This is a simplified local development version. Database-dependent features are disabled.
        </div>

        <h3>🔗 Available Pages</h3>
        <div class="links">
            <a href="/login" class="link-card">🔐 Login Page</a>
            <a href="/register" class="link-card">📝 Register Page</a>
            <a href="/master-data/btkl/create" class="link-card">📋 BTKL Form (UI Only)</a>
            <a href="/master-data/pegawai" class="link-card">👥 Employee Management</a>
            <a href="/master-data/jabatan" class="link-card">💼 Job Positions</a>
            <a href="/laporan" class="link-card">📊 Reports</a>
        </div>

        <h3>🎯 For Full Functionality</h3>
        <p>Use your hosted site with complete database functionality:</p>
        <div style="background: #ecf0f1; padding: 15px; border-radius: 5px; text-align: center;">
            <strong>🌐 Production Site:</strong><br>
            <a href="http://jobcost.eadtmanufaktur.com/login" target="_blank" style="color: #3498db; text-decoration: none; font-size: 18px;">
                http://jobcost.eadtmanufaktur.com/login
            </a><br>
            <small>Login: arkan@gmail.com / arkan230905</small>
        </div>

        <h3>📝 Development Notes</h3>
        <ul>
            <li>✅ Frontend/UI development works perfectly</li>
            <li>✅ Form layouts and styling are functional</li>
            <li>✅ JavaScript interactions work</li>
            <li>❌ Database operations are disabled</li>
            <li>❌ Data persistence is not available</li>
        </ul>
    </div>
</body>
</html>';
