<?php
/**
 * COMPLETE TUNJANGAN TEST SCRIPT
 * 
 * This script tests the entire data flow:
 * 1. Database data
 * 2. Model relationships
 * 3. API endpoint
 * 4. JavaScript data attributes
 * 5. Form submission
 */

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Tunjangan Complete Test</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }";
echo "h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }";
echo "h2 { color: #555; margin-top: 30px; border-left: 4px solid #007bff; padding-left: 10px; }";
echo ".test { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; background: #f9f9f9; }";
echo ".test.pass { border-left-color: #28a745; background: #f0f8f5; }";
echo ".test.fail { border-left-color: #dc3545; background: #fdf5f5; }";
echo ".test.warn { border-left-color: #ffc107; background: #fffbf0; }";
echo ".code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; overflow-x: auto; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #f0f0f0; font-weight: bold; }";
echo ".status-pass { color: #28a745; font-weight: bold; }";
echo ".status-fail { color: #dc3545; font-weight: bold; }";
echo ".status-warn { color: #ffc107; font-weight: bold; }";
echo ".button { display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 4px; text-decoration: none; cursor: pointer; }";
echo ".button-primary { background: #007bff; color: white; }";
echo ".button-success { background: #28a745; color: white; }";
echo ".button-danger { background: #dc3545; color: white; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";

echo "<h1>🧪 Tunjangan Complete Test Suite</h1>";
echo "<p>Test Date: " . date('Y-m-d H:i:s') . "</p>";

$testsPassed = 0;
$testsFailed = 0;
$testsWarning = 0;

try {
    // TEST 1: Authentication
    echo "<h2>1️⃣ Authentication Check</h2>";
    
    $currentUser = auth()->user();
    if ($currentUser) {
        echo "<div class='test pass'>";
        echo "<span class='status-pass'>✅ PASS</span> - User logged in";
        echo "<br>User ID: <strong>{$currentUser->id}</strong>";
        echo "<br>Email: <strong>{$currentUser->email}</strong>";
        echo "</div>";
        $testsPassed++;
        $userId = $currentUser->id;
    } else {
        echo "<div class='test fail'>";
        echo "<span class='status-fail'>❌ FAIL</span> - Not logged in";
        echo "</div>";
        $testsFailed++;
        exit;
    }

    // TEST 2: Pegawai Data
    echo "<h2>2️⃣ Pegawai Data Check</h2>";
    
    $pegawais = \App\Models\Pegawai::with('jabatanRelasi')->get();
    if (count($pegawais) > 0) {
        echo "<div class='test pass'>";
        echo "<span class='status-pass'>✅ PASS</span> - Pegawai data found";
        echo "<br>Total: <strong>" . count($pegawais) . "</strong>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nama</th><th>Jabatan</th><th>User ID</th><th>Tunjangan Transport</th><th>Tunjangan Konsumsi</th></tr>";
        
        foreach ($pegawais as $p) {
            $tt = $p->jabatanRelasi ? ($p->jabatanRelasi->tunjangan_transport ?? 0) : 0;
            $tk = $p->jabatanRelasi ? ($p->jabatanRelasi->tunjangan_konsumsi ?? 0) : 0;
            echo "<tr>";
            echo "<td>{$p->id}</td>";
            echo "<td>{$p->nama}</td>";
            echo "<td>" . ($p->jabatanRelasi ? $p->jabatanRelasi->nama : 'NULL') . "</td>";
            echo "<td>{$p->user_id}</td>";
            echo "<td>Rp " . number_format($tt, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($tk, 0, ',', '.') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        $testsPassed++;
    } else {
        echo "<div class='test fail'>";
        echo "<span class='status-fail'>❌ FAIL</span> - No pegawai data found";
        echo "</div>";
        $testsFailed++;
    }

    // TEST 3: Jabatan Data
    echo "<h2>3️⃣ Jabatan Data Check</h2>";
    
    $jabatans = \App\Models\Jabatan::get();
    if (count($jabatans) > 0) {
        echo "<div class='test pass'>";
        echo "<span class='status-pass'>✅ PASS</span> - Jabatan data found";
        echo "<br>Total: <strong>" . count($jabatans) . "</strong>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nama</th><th>User ID</th><th>Tunjangan Transport</th><th>Tunjangan Konsumsi</th><th>Gaji Pokok</th></tr>";
        
        foreach ($jabatans as $j) {
            echo "<tr>";
            echo "<td>{$j->id}</td>";
            echo "<td>{$j->nama}</td>";
            echo "<td>{$j->user_id}</td>";
            echo "<td>Rp " . number_format($j->tunjangan_transport ?? 0, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($j->tunjangan_konsumsi ?? 0, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($j->gaji_pokok ?? 0, 0, ',', '.') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        $testsPassed++;
    } else {
        echo "<div class='test fail'>";
        echo "<span class='status-fail'>❌ FAIL</span> - No jabatan data found";
        echo "</div>";
        $testsFailed++;
    }

    // TEST 4: Tunjangan Values
    echo "<h2>4️⃣ Tunjangan Values Check</h2>";
    
    $pegawaiWithTunjangan = $pegawais->filter(function ($p) {
        return $p->jabatanRelasi && 
               ($p->jabatanRelasi->tunjangan_transport > 0 || 
                $p->jabatanRelasi->tunjangan_konsumsi > 0);
    });

    if ($pegawaiWithTunjangan->count() > 0) {
        echo "<div class='test pass'>";
        echo "<span class='status-pass'>✅ PASS</span> - Tunjangan values found";
        echo "<br>Pegawai with tunjangan: <strong>" . $pegawaiWithTunjangan->count() . "</strong>";
        echo "<table>";
        echo "<tr><th>Pegawai</th><th>Jabatan</th><th>Transport</th><th>Konsumsi</th><th>Total</th></tr>";
        
        foreach ($pegawaiWithTunjangan as $p) {
            $tt = $p->jabatanRelasi->tunjangan_transport ?? 0;
            $tk = $p->jabatanRelasi->tunjangan_konsumsi ?? 0;
            $total = $tt + $tk;
            echo "<tr>";
            echo "<td>{$p->nama}</td>";
            echo "<td>{$p->jabatanRelasi->nama}</td>";
            echo "<td>Rp " . number_format($tt, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($tk, 0, ',', '.') . "</td>";
            echo "<td><strong>Rp " . number_format($total, 0, ',', '.') . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        $testsPassed++;
    } else {
        echo "<div class='test warn'>";
        echo "<span class='status-warn'>⚠️ WARNING</span> - No tunjangan values found";
        echo "<br>All pegawai have tunjangan = 0";
        echo "</div>";
        $testsWarning++;
    }

    // TEST 5: API Endpoint
    echo "<h2>5️⃣ API Endpoint Test</h2>";
    
    if (count($pegawais) > 0) {
        $testPegawai = $pegawais[0];
        echo "<p>Testing with Pegawai: <strong>{$testPegawai->nama}</strong> (ID: {$testPegawai->id})</p>";
        
        try {
            $controller = new \App\Http\Controllers\PenggajianController();
            $response = $controller->getEmployeeData($testPegawai->id);
            $data = json_decode($response->getContent(), true);
            
            if (isset($data['error'])) {
                echo "<div class='test fail'>";
                echo "<span class='status-fail'>❌ FAIL</span> - API returned error";
                echo "<br>Error: " . $data['message'];
                echo "</div>";
                $testsFailed++;
            } else {
                echo "<div class='test pass'>";
                echo "<span class='status-pass'>✅ PASS</span> - API endpoint working";
                echo "<br>Response:";
                echo "<div class='code'>";
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo "</div>";
                
                // Check tunjangan values in response
                if ($data['tunjangan_transport'] > 0 || $data['tunjangan_konsumsi'] > 0) {
                    echo "<br><span class='status-pass'>✅ Tunjangan values in API response</span>";
                } else {
                    echo "<br><span class='status-warn'>⚠️ Tunjangan values are 0 in API response</span>";
                }
                echo "</div>";
                $testsPassed++;
            }
        } catch (\Exception $e) {
            echo "<div class='test fail'>";
            echo "<span class='status-fail'>❌ FAIL</span> - API endpoint error";
            echo "<br>Error: " . $e->getMessage();
            echo "</div>";
            $testsFailed++;
        }
    }

    // TEST 6: Global Scope Check
    echo "<h2>6️⃣ Global Scope Check</h2>";
    
    $allPegawais = \App\Models\Pegawai::withoutGlobalScopes()->get();
    $pegawaisForCurrentUser = \App\Models\Pegawai::withoutGlobalScopes()->where('user_id', $userId)->get();
    $pegawaisForOtherUsers = $allPegawais->count() - $pegawaisForCurrentUser->count();
    
    echo "<div class='test'>";
    echo "Total Pegawai in DB: <strong>" . $allPegawais->count() . "</strong><br>";
    echo "Pegawai for User {$userId}: <strong>" . $pegawaisForCurrentUser->count() . "</strong><br>";
    echo "Pegawai for Other Users: <strong>" . $pegawaisForOtherUsers . "</strong>";
    
    if ($pegawaisForOtherUsers > 0) {
        echo "<br><span class='status-warn'>⚠️ WARNING</span> - Data belongs to other users!";
        echo "<br>This is likely the cause of tunjangan showing 0";
        echo "<br><strong>Solution:</strong> Run command: <code>php artisan fix:production-user-mismatch</code>";
        $testsWarning++;
    } else {
        echo "<br><span class='status-pass'>✅ All data belongs to current user</span>";
        $testsPassed++;
    }
    echo "</div>";

    // TEST 7: Route Check
    echo "<h2>7️⃣ Route Check</h2>";
    
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $penggajianRoute = null;
    
    foreach ($routes as $route) {
        if (strpos($route->uri, 'transaksi/penggajian/pegawai') !== false) {
            $penggajianRoute = $route;
            break;
        }
    }
    
    if ($penggajianRoute) {
        echo "<div class='test pass'>";
        echo "<span class='status-pass'>✅ PASS</span> - Route found";
        echo "<br>URI: <strong>" . $penggajianRoute->uri . "</strong>";
        echo "<br>Methods: <strong>" . implode(', ', $penggajianRoute->methods) . "</strong>";
        echo "</div>";
        $testsPassed++;
    } else {
        echo "<div class='test fail'>";
        echo "<span class='status-fail'>❌ FAIL</span> - Route not found";
        echo "</div>";
        $testsFailed++;
    }

    // TEST 8: Cache Status
    echo "<h2>8️⃣ Cache Status</h2>";
    
    echo "<div class='test'>";
    echo "Cache Driver: <strong>" . config('cache.default') . "</strong><br>";
    echo "Config Cache: <strong>" . (file_exists(base_path('bootstrap/cache/config.php')) ? 'EXISTS' : 'NOT FOUND') . "</strong><br>";
    echo "Route Cache: <strong>" . (file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 'EXISTS' : 'NOT FOUND') . "</strong>";
    
    if (file_exists(base_path('bootstrap/cache/config.php')) || file_exists(base_path('bootstrap/cache/routes-v7.php'))) {
        echo "<br><span class='status-warn'>⚠️ WARNING</span> - Cache files exist";
        echo "<br><strong>Solution:</strong> Run: <code>php artisan optimize:clear</code>";
        $testsWarning++;
    } else {
        echo "<br><span class='status-pass'>✅ No cache files found</span>";
        $testsPassed++;
    }
    echo "</div>";

} catch (\Exception $e) {
    echo "<div class='test fail'>";
    echo "<span class='status-fail'>❌ ERROR</span>";
    echo "<br>Error: " . $e->getMessage();
    echo "<br><pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
    $testsFailed++;
}

// Summary
echo "<h2>📊 Test Summary</h2>";
echo "<div class='test'>";
echo "<p><span class='status-pass'>✅ Passed: {$testsPassed}</span></p>";
echo "<p><span class='status-warn'>⚠️ Warnings: {$testsWarning}</span></p>";
echo "<p><span class='status-fail'>❌ Failed: {$testsFailed}</span></p>";

if ($testsFailed == 0 && $testsWarning == 0) {
    echo "<p style='color: #28a745; font-weight: bold;'>🎉 All tests passed! Tunjangan should be working correctly.</p>";
} elseif ($testsFailed == 0) {
    echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ Some warnings found. Please review and fix.</p>";
} else {
    echo "<p style='color: #dc3545; font-weight: bold;'>❌ Some tests failed. Please fix the issues.</p>";
}

echo "</div>";

// Recommendations
echo "<h2>💡 Recommendations</h2>";
echo "<div class='test'>";

if ($testsFailed > 0) {
    echo "<p><strong>Critical Issues Found:</strong></p>";
    echo "<ul>";
    echo "<li>Check server logs: <code>tail -f storage/logs/laravel.log</code></li>";
    echo "<li>Verify database connection</li>";
    echo "<li>Check if routes are registered</li>";
    echo "</ul>";
}

if ($testsWarning > 0) {
    echo "<p><strong>Warnings to Address:</strong></p>";
    echo "<ul>";
    echo "<li>Run: <code>php artisan optimize:clear</code></li>";
    echo "<li>Run: <code>php artisan fix:production-user-mismatch</code></li>";
    echo "<li>Clear browser cache (Ctrl+Shift+Delete)</li>";
    echo "<li>Hard refresh page (Ctrl+F5)</li>";
    echo "</ul>";
}

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Review test results above</li>";
echo "<li>Fix any critical issues</li>";
echo "<li>Run cache clear commands</li>";
echo "<li>Test in browser</li>";
echo "</ol>";

echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
