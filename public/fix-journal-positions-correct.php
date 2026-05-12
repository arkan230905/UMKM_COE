<?php
/**
 * FIX JOURNAL POSITIONS - CORRECT VERSION
 * Swap debit and credit for BTKL, BOP, and WIP
 */

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'simcost_sistem_manufaktur_process_costing';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Journal Positions - CORRECT</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
        .ok { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .step { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
        .step-title { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .summary { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .summary.error { background: #ffebee; border-left-color: #dc3545; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Fix Journal Positions - CORRECT VERSION</h1>
    <p>Waktu: <?php echo date('Y-m-d H:i:s'); ?></p>

    <div class="card">
        <div class="title">📊 Step 1: Check Current State</div>
        
        <div class="step">
            <div class="step-title">Current Data in journal_lines:</div>
            
            <?php
            $sql = "SELECT jl.id, je.tanggal, c.kode_akun, c.nama_akun, jl.debit, jl.credit
                    FROM journal_lines jl
                    INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                    LEFT JOIN coas c ON jl.coa_id = c.id
                    WHERE je.ref_type = 'production_labor_overhead'
                    ORDER BY je.tanggal DESC, c.kode_akun";
            
            $result = $conn->query($sql);
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Tanggal</th><th>Kode</th><th>Nama Akun</th><th>Debit</th><th>Kredit</th><th>Status</th></tr>";
            
            $wrong_count = 0;
            while ($row = $result->fetch_assoc()) {
                $status = "✓";
                
                // Check if correct
                if ($row['kode_akun'] == '52' || $row['kode_akun'] == '53') {
                    // BTKL & BOP should be in KREDIT
                    if ($row['debit'] > 0 && $row['kredit'] == 0) {
                        $status = "❌ WRONG - Should be in KREDIT";
                        $wrong_count++;
                    }
                } elseif ($row['kode_akun'] == '117') {
                    // WIP should be in DEBIT
                    if ($row['kredit'] > 0 && $row['debit'] == 0) {
                        $status = "❌ WRONG - Should be in DEBIT";
                        $wrong_count++;
                    }
                }
                
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['tanggal'] . "</td>";
                echo "<td>" . $row['kode_akun'] . "</td>";
                echo "<td>" . $row['nama_akun'] . "</td>";
                echo "<td>" . number_format($row['debit'], 0, ',', '.') . "</td>";
                echo "<td>" . number_format($row['kredit'], 0, ',', '.') . "</td>";
                echo "<td>" . $status . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<p><span class='warning'>Found " . $wrong_count . " wrong entries</span></p>";
            ?>
        </div>
    </div>

    <div class="card">
        <div class="title">🔧 Step 2: Fix Positions</div>
        
        <div class="step">
            <div class="step-title">Fixing BTKL & BOP (52, 53)...</div>
            
            <?php
            // Fix BTKL and BOP - swap debit and credit
            $sql = "UPDATE journal_lines jl
                    INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                    INNER JOIN coas c ON jl.coa_id = c.id
                    SET jl.credit = jl.debit, jl.debit = 0
                    WHERE je.ref_type = 'production_labor_overhead'
                    AND c.kode_akun IN ('52', '53')
                    AND jl.debit > 0";
            
            if ($conn->query($sql)) {
                echo "<p><span class='ok'>✓ Fixed BTKL & BOP: " . $conn->affected_rows . " rows</span></p>";
            } else {
                echo "<p><span class='error'>✗ Error: " . $conn->error . "</span></p>";
            }
            ?>
        </div>

        <div class="step">
            <div class="step-title">Fixing WIP (117)...</div>
            
            <?php
            // Fix WIP - swap debit and credit
            $sql = "UPDATE journal_lines jl
                    INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                    INNER JOIN coas c ON jl.coa_id = c.id
                    SET jl.debit = jl.credit, jl.credit = 0
                    WHERE je.ref_type = 'production_labor_overhead'
                    AND c.kode_akun = '117'
                    AND jl.kredit > 0";
            
            if ($conn->query($sql)) {
                echo "<p><span class='ok'>✓ Fixed WIP: " . $conn->affected_rows . " rows</span></p>";
            } else {
                echo "<p><span class='error'>✗ Error: " . $conn->error . "</span></p>";
            }
            ?>
        </div>

        <div class="step">
            <div class="step-title">Cleaning up jurnal_umum...</div>
            
            <?php
            // Delete all production_labor_overhead from jurnal_umum
            $sql = "DELETE FROM jurnal_umum WHERE tipe_referensi = 'production_labor_overhead'";
            
            if ($conn->query($sql)) {
                echo "<p><span class='ok'>✓ Deleted " . $conn->affected_rows . " entries from jurnal_umum</span></p>";
            } else {
                echo "<p><span class='error'>✗ Error: " . $conn->error . "</span></p>";
            }
            ?>
        </div>
    </div>

    <div class="card">
        <div class="title">✅ Step 3: Verify Fix</div>
        
        <div class="step">
            <div class="step-title">Final State:</div>
            
            <?php
            $sql = "SELECT jl.id, je.tanggal, c.kode_akun, c.nama_akun, jl.debit, jl.credit
                    FROM journal_lines jl
                    INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                    LEFT JOIN coas c ON jl.coa_id = c.id
                    WHERE je.ref_type = 'production_labor_overhead'
                    ORDER BY je.tanggal DESC, c.kode_akun";
            
            $result = $conn->query($sql);
            
            echo "<table>";
            echo "<tr><th>Tanggal</th><th>Kode</th><th>Nama Akun</th><th>Debit</th><th>Kredit</th><th>Status</th></tr>";
            
            $all_correct = true;
            while ($row = $result->fetch_assoc()) {
                $status = "✓ CORRECT";
                
                // Verify correct
                if ($row['kode_akun'] == '52' || $row['kode_akun'] == '53') {
                    if (!($row['kredit'] > 0 && $row['debit'] == 0)) {
                        $status = "❌ STILL WRONG";
                        $all_correct = false;
                    }
                } elseif ($row['kode_akun'] == '117') {
                    if (!($row['debit'] > 0 && $row['kredit'] == 0)) {
                        $status = "❌ STILL WRONG";
                        $all_correct = false;
                    }
                }
                
                echo "<tr>";
                echo "<td>" . $row['tanggal'] . "</td>";
                echo "<td>" . $row['kode_akun'] . "</td>";
                echo "<td>" . $row['nama_akun'] . "</td>";
                echo "<td>" . number_format($row['debit'], 0, ',', '.') . "</td>";
                echo "<td>" . number_format($row['kredit'], 0, ',', '.') . "</td>";
                echo "<td>" . $status . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            ?>
        </div>
    </div>

    <div class="card">
        <div class="title">📈 Summary</div>
        
        <?php
        if ($all_correct) {
            echo "<div class='summary'>";
            echo "<strong>✅ ALL FIXED!</strong>";
            echo "<p>Journal positions are now correct:</p>";
            echo "<ul>";
            echo "<li>✓ BTKL (52) in KREDIT</li>";
            echo "<li>✓ BOP (53) in KREDIT</li>";
            echo "<li>✓ Barang Dalam Proses (117) in DEBIT</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div class='summary error'>";
            echo "<strong>⚠ SOME ENTRIES STILL WRONG</strong>";
            echo "<p>Please check the table above.</p>";
            echo "</div>";
        }
        ?>
        
        <p style="margin-top: 20px;">
            <a href="http://127.0.0.1:8000/akuntansi/jurnal-umum" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 3px;">
                ✓ Refresh Jurnal Umum
            </a>
        </p>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>
