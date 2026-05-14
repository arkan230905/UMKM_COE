<!DOCTYPE html>
<html>
<head>
    <title>Test BOP Save</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h1>Test BOP Proses Save & Display</h1>
    
    <?php
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    try {
        // Get BOP Proses data
        $bopProses = \App\Models\BopProses::where('is_active', true)
            ->orderBy('id', 'desc')
            ->get();
        
        echo "<p class='success'>✓ Query berhasil! Total data: " . $bopProses->count() . "</p>";
        
        if ($bopProses->count() > 0) {
            echo "<table>";
            echo "<tr>";
            echo "<th>ID</th>";
            echo "<th>Nama BOP Proses</th>";
            echo "<th>Proses Produksi ID</th>";
            echo "<th>BOP per Unit</th>";
            echo "<th>Total BOP per Jam</th>";
            echo "<th>Komponen</th>";
            echo "<th>Is Active</th>";
            echo "</tr>";
            
            foreach ($bopProses as $bop) {
                $komponenBop = is_array($bop->komponen_bop) ? $bop->komponen_bop : [];
                $komponenCount = count($komponenBop);
                
                echo "<tr>";
                echo "<td>{$bop->id}</td>";
                echo "<td>" . ($bop->nama_bop_proses ?? '<em>NULL</em>') . "</td>";
                echo "<td>" . ($bop->proses_produksi_id ?? '<em>NULL</em>') . "</td>";
                echo "<td>Rp " . number_format($bop->bop_per_unit, 2, ',', '.') . "</td>";
                echo "<td>Rp " . number_format($bop->total_bop_per_jam, 2, ',', '.') . "</td>";
                echo "<td>{$komponenCount} komponen</td>";
                echo "<td>" . ($bop->is_active ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
                
                // Show komponen details
                if ($komponenCount > 0) {
                    echo "<tr>";
                    echo "<td colspan='7' style='background-color: #f9f9f9;'>";
                    echo "<strong>Komponen:</strong><br>";
                    foreach ($komponenBop as $idx => $komp) {
                        $name = $komp['component'] ?? $komp['name'] ?? 'Unknown';
                        $rate = $komp['rate_per_hour'] ?? 0;
                        echo ($idx + 1) . ". {$name}: Rp " . number_format($rate, 2, ',', '.') . "<br>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            }
            
            echo "</table>";
        } else {
            echo "<p class='error'>✗ Tidak ada data BOP Proses yang ditemukan!</p>";
            echo "<p>Silakan tambah data BOP Proses melalui form.</p>";
        }
        
    } catch (\Exception $e) {
        echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    ?>
    
    <hr>
    <p><a href="/master-data/bop">← Kembali ke halaman BOP</a></p>
</body>
</html>
