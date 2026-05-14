<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Insert BOP</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Insert BOP Proses</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                DB::beginTransaction();
                
                $testData = [
                    'nama_bop_proses' => 'Test BOP - ' . date('Y-m-d H:i:s'),
                    'proses_produksi_id' => null,
                    'komponen_bop' => [
                        [
                            'component' => 'Listrik',
                            'rate_per_hour' => 5000,
                            'description' => 'Biaya listrik'
                        ],
                        [
                            'component' => 'Air',
                            'rate_per_hour' => 2000,
                            'description' => 'Biaya air'
                        ]
                    ],
                    'total_bop_per_jam' => 7000,
                    'kapasitas_per_jam' => 1,
                    'bop_per_unit' => 7000,
                    'periode' => date('Y-m'),
                    'keterangan' => 'Test data inserted via debug script',
                    'is_active' => true,
                ];
                
                echo "<h2>Data to Insert:</h2>";
                echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";
                
                $bopProses = \App\Models\BopProses::create($testData);
                
                DB::commit();
                
                echo "<p class='success'>✓ Test data inserted successfully!</p>";
                echo "<p>ID: {$bopProses->id}</p>";
                echo "<p>Nama: {$bopProses->nama_bop_proses}</p>";
                echo "<p>BOP per Unit: Rp " . number_format($bopProses->bop_per_unit, 2, ',', '.') . "</p>";
                
                echo "<h2>Verification Query:</h2>";
                $verify = DB::table('bop_proses')->where('id', $bopProses->id)->first();
                echo "<pre>" . json_encode($verify, JSON_PRETTY_PRINT) . "</pre>";
                
            } catch (\Exception $e) {
                DB::rollBack();
                echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
                echo "<pre>" . $e->getTraceAsString() . "</pre>";
            }
        }
        ?>
        
        <hr>
        <p><a href="debug_bop.php">← Back to Debug Page</a></p>
        <p><a href="/master-data/bop">← Kembali ke halaman BOP</a></p>
    </div>
</body>
</html>
