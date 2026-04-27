<!DOCTYPE html>
<html>
<head>
    <title>Debug BOP Proses</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .section { margin: 30px 0; padding: 20px; background: #f9f9f9; border-left: 4px solid #4CAF50; }
        h2 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .log-entry { margin: 10px 0; padding: 10px; background: #fff; border-left: 3px solid #2196F3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug BOP Proses</h1>
        
        <?php
        require __DIR__.'/../vendor/autoload.php';
        $app = require_once __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        echo "<div class='section'>";
        echo "<h2>1. Database Connection Test</h2>";
        try {
            DB::connection()->getPdo();
            echo "<p class='success'>✓ Database connected successfully!</p>";
            echo "<p>Database: " . DB::connection()->getDatabaseName() . "</p>";
        } catch (\Exception $e) {
            echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2>2. BOP Proses Table Structure</h2>";
        try {
            $columns = DB::select("DESCRIBE bop_proses");
            echo "<table>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>{$col->Field}</td>";
                echo "<td>{$col->Type}</td>";
                echo "<td>{$col->Null}</td>";
                echo "<td>{$col->Key}</td>";
                echo "<td>" . ($col->Default ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (\Exception $e) {
            echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2>3. All BOP Proses Records (including inactive)</h2>";
        try {
            $allBop = DB::table('bop_proses')->orderBy('id', 'desc')->get();
            echo "<p class='success'>✓ Total records in database: " . $allBop->count() . "</p>";
            
            if ($allBop->count() > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Nama BOP Proses</th><th>Proses Produksi ID</th><th>BOP/Unit</th><th>Total BOP/Jam</th><th>Is Active</th><th>Created At</th></tr>";
                foreach ($allBop as $bop) {
                    $rowClass = $bop->is_active ? '' : 'style="background-color: #ffebee;"';
                    echo "<tr {$rowClass}>";
                    echo "<td>{$bop->id}</td>";
                    echo "<td>" . ($bop->nama_bop_proses ?? '<em>NULL</em>') . "</td>";
                    echo "<td>" . ($bop->proses_produksi_id ?? '<em>NULL</em>') . "</td>";
                    echo "<td>Rp " . number_format($bop->bop_per_unit, 2, ',', '.') . "</td>";
                    echo "<td>Rp " . number_format($bop->total_bop_per_jam, 2, ',', '.') . "</td>";
                    echo "<td>" . ($bop->is_active ? '<span class="success">Active</span>' : '<span class="error">Inactive</span>') . "</td>";
                    echo "<td>{$bop->created_at}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠ No records found in bop_proses table!</p>";
            }
        } catch (\Exception $e) {
            echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2>4. Active BOP Proses (as shown in index page)</h2>";
        try {
            $activeBop = \App\Models\BopProses::where('is_active', true)->orderBy('id', 'desc')->get();
            echo "<p class='success'>✓ Active records: " . $activeBop->count() . "</p>";
            
            if ($activeBop->count() > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Nama BOP Proses</th><th>BOP/Unit</th><th>Komponen</th><th>Details</th></tr>";
                foreach ($activeBop as $bop) {
                    $komponenBop = is_array($bop->komponen_bop) ? $bop->komponen_bop : [];
                    echo "<tr>";
                    echo "<td>{$bop->id}</td>";
                    echo "<td>" . ($bop->nama_bop_proses ?? '<em>NULL</em>') . "</td>";
                    echo "<td>Rp " . number_format($bop->bop_per_unit, 2, ',', '.') . "</td>";
                    echo "<td>" . count($komponenBop) . " komponen</td>";
                    echo "<td><pre>" . json_encode($komponenBop, JSON_PRETTY_PRINT) . "</pre></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠ No active BOP Proses found!</p>";
                echo "<p>This is why the index page shows 'Belum ada data BOP'</p>";
            }
        } catch (\Exception $e) {
            echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2>5. Recent Laravel Logs (BOP related)</h2>";
        try {
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                $logs = file_get_contents($logFile);
                $logLines = explode("\n", $logs);
                $bopLogs = array_filter($logLines, function($line) {
                    return stripos($line, 'BOP') !== false;
                });
                
                if (count($bopLogs) > 0) {
                    echo "<p class='success'>✓ Found " . count($bopLogs) . " BOP-related log entries</p>";
                    $recentLogs = array_slice($bopLogs, -20);
                    foreach ($recentLogs as $log) {
                        echo "<div class='log-entry'>" . htmlspecialchars($log) . "</div>";
                    }
                } else {
                    echo "<p class='warning'>⚠ No BOP-related logs found</p>";
                }
            } else {
                echo "<p class='warning'>⚠ Log file not found</p>";
            }
        } catch (\Exception $e) {
            echo "<p class='error'>✗ Error reading logs: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2>6. Test Insert</h2>";
        echo "<p>Click the button below to test inserting a sample BOP Proses:</p>";
        echo "<form method='POST' action='debug_bop_insert.php'>";
        echo "<button type='submit' style='padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;'>Test Insert Sample Data</button>";
        echo "</form>";
        echo "</div>";
        ?>
        
        <hr>
        <p><a href="/master-data/bop">← Kembali ke halaman BOP</a></p>
    </div>
</body>
</html>
