<?php
/**
 * COMPREHENSIVE PRODUCTION DEBUGGING SCRIPT
 * 
 * This script checks:
 * 1. Current logged-in user ID
 * 2. Pegawai data and their user_id
 * 3. Jabatan data and their user_id
 * 4. Relasi between Pegawai and Jabatan
 * 5. Tunjangan values in both tables
 * 6. Global scope filtering
 */

echo "<h1>🔍 PRODUCTION TUNJANGAN DEBUGGING</h1>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Get current user
    $currentUser = auth()->user();
    echo "<h2>1. Current Logged-In User</h2>";
    echo "<p><strong>User ID:</strong> " . ($currentUser ? $currentUser->id : 'NOT LOGGED IN') . "</p>";
    echo "<p><strong>User Email:</strong> " . ($currentUser ? $currentUser->email : 'N/A') . "</p>";
    
    if (!$currentUser) {
        echo "<p style='color:red;'>❌ NOT LOGGED IN - Cannot proceed with debugging</p>";
        exit;
    }
    
    $userId = $currentUser->id;
    
    // Check Pegawai data
    echo "<h2>2. Pegawai Data (with global scope applied)</h2>";
    $pegawais = \App\Models\Pegawai::with('jabatanRelasi')->get();
    echo "<p>Total pegawai visible to current user: " . count($pegawais) . "</p>";
    
    if (count($pegawais) > 0) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>ID</th><th>Nama</th><th>User ID</th><th>Jabatan ID</th><th>Jabatan Nama</th><th>Tunjangan Transport</th><th>Tunjangan Konsumsi</th></tr>";
        
        foreach ($pegawais as $pegawai) {
            $tunjanganTransport = $pegawai->jabatanRelasi ? ($pegawai->jabatanRelasi->tunjangan_transport ?? 0) : 0;
            $tunjanganKonsumsi = $pegawai->jabatanRelasi ? ($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0) : 0;
            
            echo "<tr>";
            echo "<td>{$pegawai->id}</td>";
            echo "<td>{$pegawai->nama}</td>";
            echo "<td>{$pegawai->user_id}</td>";
            echo "<td>{$pegawai->jabatan_id}</td>";
            echo "<td>" . ($pegawai->jabatanRelasi ? $pegawai->jabatanRelasi->nama : 'NULL') . "</td>";
            echo "<td>Rp " . number_format($tunjanganTransport, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($tunjanganKonsumsi, 0, ',', '.') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>⚠️ No pegawai found for current user</p>";
    }
    
    // Check Jabatan data
    echo "<h2>3. Jabatan Data (with global scope applied)</h2>";
    $jabatans = \App\Models\Jabatan::get();
    echo "<p>Total jabatan visible to current user: " . count($jabatans) . "</p>";
    
    if (count($jabatans) > 0) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>ID</th><th>Nama</th><th>User ID</th><th>Tunjangan Transport</th><th>Tunjangan Konsumsi</th><th>Gaji Pokok</th></tr>";
        
        foreach ($jabatans as $jabatan) {
            echo "<tr>";
            echo "<td>{$jabatan->id}</td>";
            echo "<td>{$jabatan->nama}</td>";
            echo "<td>{$jabatan->user_id}</td>";
            echo "<td>Rp " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($jabatan->gaji_pokok ?? 0, 0, ',', '.') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>⚠️ No jabatan found for current user</p>";
    }
    
    // Check ALL data in database (bypassing global scope)
    echo "<h2>4. ALL Pegawai Data in Database (bypassing global scope)</h2>";
    $allPegawais = \App\Models\Pegawai::withoutGlobalScopes()->get();
    echo "<p>Total pegawai in database: " . count($allPegawais) . "</p>";
    
    if (count($allPegawais) > 0) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>ID</th><th>Nama</th><th>User ID</th><th>Jabatan ID</th><th>Belongs to Current User?</th></tr>";
        
        foreach ($allPegawais as $pegawai) {
            $belongsToCurrentUser = $pegawai->user_id == $userId ? '✅ YES' : '❌ NO';
            echo "<tr>";
            echo "<td>{$pegawai->id}</td>";
            echo "<td>{$pegawai->nama}</td>";
            echo "<td>{$pegawai->user_id}</td>";
            echo "<td>{$pegawai->jabatan_id}</td>";
            echo "<td style='color:" . ($pegawai->user_id == $userId ? 'green' : 'red') . ";'>{$belongsToCurrentUser}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check ALL Jabatan data (bypassing global scope)
    echo "<h2>5. ALL Jabatan Data in Database (bypassing global scope)</h2>";
    $allJabatans = \App\Models\Jabatan::withoutGlobalScopes()->get();
    echo "<p>Total jabatan in database: " . count($allJabatans) . "</p>";
    
    if (count($allJabatans) > 0) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>ID</th><th>Nama</th><th>User ID</th><th>Tunjangan Transport</th><th>Tunjangan Konsumsi</th><th>Belongs to Current User?</th></tr>";
        
        foreach ($allJabatans as $jabatan) {
            $belongsToCurrentUser = $jabatan->user_id == $userId ? '✅ YES' : '❌ NO';
            echo "<tr>";
            echo "<td>{$jabatan->id}</td>";
            echo "<td>{$jabatan->nama}</td>";
            echo "<td>{$jabatan->user_id}</td>";
            echo "<td>Rp " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "</td>";
            echo "<td style='color:" . ($jabatan->user_id == $userId ? 'green' : 'red') . ";'>{$belongsToCurrentUser}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test API endpoint directly
    echo "<h2>6. Test API Endpoint Directly</h2>";
    if (count($pegawais) > 0) {
        $testPegawai = $pegawais[0];
        echo "<p>Testing with Pegawai ID: {$testPegawai->id} ({$testPegawai->nama})</p>";
        
        // Simulate the API call
        $controller = new \App\Http\Controllers\PenggajianController();
        $response = $controller->getEmployeeData($testPegawai->id);
        $data = json_decode($response->getContent(), true);
        
        echo "<p><strong>API Response:</strong></p>";
        echo "<pre>";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</pre>";
        
        if (isset($data['tunjangan_transport'])) {
            $color = $data['tunjangan_transport'] > 0 ? 'green' : 'red';
            echo "<p style='color:{$color};'><strong>Tunjangan Transport in API Response: Rp " . number_format($data['tunjangan_transport'], 0, ',', '.') . "</strong></p>";
        }
    }
    
    // Summary
    echo "<h2>7. Summary & Recommendations</h2>";
    
    if (count($pegawais) == 0) {
        echo "<p style='color:red;'>❌ ISSUE: No pegawai data visible to current user (User ID: {$userId})</p>";
        echo "<p>This means the pegawai data belongs to a different user_id in the database.</p>";
        echo "<p><strong>SOLUTION:</strong> Reassign pegawai data to current user using the FixTunjanganUserMismatch command.</p>";
    } else {
        echo "<p style='color:green;'>✅ Pegawai data is visible to current user</p>";
        
        // Check if tunjangan values are present
        $hasTunjangan = false;
        foreach ($pegawais as $pegawai) {
            if ($pegawai->jabatanRelasi && ($pegawai->jabatanRelasi->tunjangan_transport > 0 || $pegawai->jabatanRelasi->tunjangan_konsumsi > 0)) {
                $hasTunjangan = true;
                break;
            }
        }
        
        if ($hasTunjangan) {
            echo "<p style='color:green;'>✅ Tunjangan values are present in database</p>";
            echo "<p><strong>NEXT STEPS:</strong></p>";
            echo "<ol>";
            echo "<li>Clear all caches: <code>php artisan optimize:clear</code></li>";
            echo "<li>Clear browser cache (Ctrl+Shift+Delete)</li>";
            echo "<li>Hard refresh page (Ctrl+F5)</li>";
            echo "<li>Test again in browser</li>";
            echo "</ol>";
        } else {
            echo "<p style='color:orange;'>⚠️ WARNING: Tunjangan values are 0 or missing in database</p>";
            echo "<p><strong>SOLUTION:</strong> Update jabatan data with correct tunjangan values.</p>";
        }
    }
    
} catch (\Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
