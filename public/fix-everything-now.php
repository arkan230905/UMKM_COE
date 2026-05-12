<?php
/**
 * FIX EVERYTHING - Both Tasks in One Script
 * 1. Add payment proof columns to penjualans
 * 2. Fix BTKL & BOP journal positions
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
    <title>Fix Everything</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; max-height: 300px; }
        .action-btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; cursor: pointer; border: none; font-size: 14px; }
        .action-btn:hover { background: #0056b3; }
        .action-btn.success { background: #28a745; }
        .action-btn.success:hover { background: #218838; }
        .action-btn.danger { background: #dc3545; }
        .action-btn.danger:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Fix Everything - Both Tasks</h1>
    <p>Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>

    <!-- TASK 1: Penjualan Payment Flow -->
    <div class="section">
        <div class="section-title">📋 TASK 1: Penjualan Payment Flow - Database Migration</div>
        
        <?php
        // Check if payment proof columns exist
        $result = $conn->query("DESCRIBE penjualans");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row['Type'];
        }
        
        $bukti_exists = isset($columns['bukti_pembayaran']);
        $catatan_exists = isset($columns['catatan_pembayaran']);
        $migration_done = $bukti_exists && $catatan_exists;
        
        if (!$migration_done) {
            echo "<p><span class='status-warning'>⚠ PENDING</span> - Missing columns</p>";
            
            // Run migration
            $errors = [];
            
            if (!$bukti_exists) {
                $sql = "ALTER TABLE penjualans ADD COLUMN bukti_pembayaran VARCHAR(255) NULL AFTER total";
                if ($conn->query($sql)) {
                    echo "<p><span class='status-ok'>✓ Added bukti_pembayaran column</span></p>";
                } else {
                    $errors[] = "Error adding bukti_pembayaran: " . $conn->error;
                }
            }
            
            if (!$catatan_exists) {
                $sql = "ALTER TABLE penjualans ADD COLUMN catatan_pembayaran LONGTEXT NULL AFTER bukti_pembayaran";
                if ($conn->query($sql)) {
                    echo "<p><span class='status-ok'>✓ Added catatan_pembayaran column</span></p>";
                } else {
                    $errors[] = "Error adding catatan_pembayaran: " . $conn->error;
                }
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<p><span class='status-error'>✗ " . $error . "</span></p>";
                }
            } else {
                echo "<p><span class='status-ok'>✓ MIGRATION COMPLETE</span></p>";
            }
        } else {
            echo "<p><span class='status-ok'>✓ COMPLETE</span> - All columns exist</p>";
            echo "<table>";
            echo "<tr><th>Column</th><th>Type</th></tr>";
            echo "<tr><td>bukti_pembayaran</td><td>" . $columns['bukti_pembayaran'] . "</td></tr>";
            echo "<tr><td>catatan_pembayaran</td><td>" . $columns['catatan_pembayaran'] . "</td></tr>";
            echo "</table>";
        }
        ?>
    </div>

    <!-- TASK 2: BTKL & BOP Journal Fix -->
    <div class="section">
        <div class="section-title">📊 TASK 2: BTKL & BOP Journal Entry Positions</div>
        
        <?php
        // Check BTKL & BOP entries
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN c.kode_akun IN ('52', '53') AND jl.debit > 0 THEN 1 ELSE 0 END) as issues
                FROM journal_lines jl
                INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                LEFT JOIN coas c ON jl.coa_id = c.id
                WHERE je.ref_type = 'production_labor_overhead'";
        
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $total_entries = $row['total'];
        $issue_count = $row['issues'] ?? 0;
        
        if ($issue_count > 0) {
            echo "<p><span class='status-warning'>⚠ NEEDS FIX</span> - Found " . $issue_count . " incorrect entries</p>";
            
            // Fix BTKL and BOP
            $sql = "UPDATE journal_lines jl
                    INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                    INNER JOIN coas c ON jl.coa_id = c.id
                    SET jl.credit = jl.debit, jl.debit = 0
                    WHERE je.ref_type = 'production_labor_overhead'
                    AND c.kode_akun IN ('52', '53')
                    AND jl.debit > 0";
            
            if ($conn->query($sql)) {
                echo "<p><span class='status-ok'>✓ Fixed BTKL & BOP entries: " . $conn->affected_rows . " rows</span></p>";
            } else {
                echo "<p><span class='status-error'>✗ Error: " . $conn->error . "</span></p>";
            }
            
            // Fix WIP
            $sql = "UPDATE journal_lines jl
                    INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                    INNER JOIN coas c ON jl.coa_id = c.id
                    SET jl.debit = jl.credit, jl.credit = 0
                    WHERE je.ref_type = 'production_labor_overhead'
                    AND c.kode_akun = '117'
                    AND jl.credit > 0";
            
            if ($conn->query($sql)) {
                echo "<p><span class='status-ok'>✓ Fixed WIP entries: " . $conn->affected_rows . " rows</span></p>";
            } else {
                echo "<p><span class='status-error'>✗ Error: " . $conn->error . "</span></p>";
            }
            
            // Clean up jurnal_umum
            $sql = "DELETE FROM jurnal_umum WHERE tipe_referensi = 'production_labor_overhead'";
            if ($conn->query($sql)) {
                echo "<p><span class='status-ok'>✓ Cleaned up jurnal_umum: " . $conn->affected_rows . " rows deleted</span></p>";
            }
        } else {
            echo "<p><span class='status-ok'>✓ COMPLETE</span> - All entries are correct</p>";
        }
        
        // Show current state
        echo "<p><strong>Current State:</strong></p>";
        $sql = "SELECT c.kode_akun, c.nama_akun, jl.debit, jl.credit
                FROM journal_lines jl
                INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                LEFT JOIN coas c ON jl.coa_id = c.id
                WHERE je.ref_type = 'production_labor_overhead'
                ORDER BY je.tanggal DESC, c.kode_akun
                LIMIT 6";
        
        $result = $conn->query($sql);
        echo "<table>";
        echo "<tr><th>Kode</th><th>Nama Akun</th><th>Debit</th><th>Kredit</th><th>Status</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            $status = "✓";
            if (in_array($row['kode_akun'], ['52', '53'])) {
                if (!($row['credit'] > 0 && $row['debit'] == 0)) {
                    $status = "✗";
                }
            } elseif ($row['kode_akun'] == '117') {
                if (!($row['debit'] > 0 && $row['credit'] == 0)) {
                    $status = "✗";
                }
            }
            
            echo "<tr>";
            echo "<td>" . $row['kode_akun'] . "</td>";
            echo "<td>" . $row['nama_akun'] . "</td>";
            echo "<td>" . number_format($row['debit'], 0, ',', '.') . "</td>";
            echo "<td>" . number_format($row['credit'], 0, ',', '.') . "</td>";
            echo "<td>" . $status . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        ?>
    </div>

    <!-- Summary -->
    <div class="section">
        <div class="section-title">📈 Overall Status</div>
        
        <?php
        // Final check
        $result = $conn->query("DESCRIBE penjualans");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row['Type'];
        }
        $migration_done = isset($columns['bukti_pembayaran']) && isset($columns['catatan_pembayaran']);
        
        $sql = "SELECT COUNT(*) as issues FROM journal_lines jl
                INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                LEFT JOIN coas c ON jl.coa_id = c.id
                WHERE je.ref_type = 'production_labor_overhead'
                AND c.kode_akun IN ('52', '53')
                AND jl.debit > 0";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $journal_fixed = $row['issues'] == 0;
        
        $all_complete = $migration_done && $journal_fixed;
        ?>
        
        <p>
            <strong>Overall:</strong> 
            <span class="<?php echo $all_complete ? 'status-ok' : 'status-warning'; ?>">
                <?php echo $all_complete ? '✓ ALL TASKS COMPLETE' : '⚠ SOME TASKS PENDING'; ?>
            </span>
        </p>
        
        <table>
            <tr>
                <th>Task</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Penjualan Payment Flow Migration</td>
                <td class="<?php echo $migration_done ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $migration_done ? '✓ COMPLETE' : '✗ PENDING'; ?>
                </td>
            </tr>
            <tr>
                <td>BTKL & BOP Journal Fix</td>
                <td class="<?php echo $journal_fixed ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $journal_fixed ? '✓ COMPLETE' : '✗ PENDING'; ?>
                </td>
            </tr>
        </table>
        
        <p style="margin-top: 20px;">
            <a href="http://127.0.0.1:8000/akuntansi/jurnal-umum" class="action-btn success">
                ✓ View Journal (Jurnal Umum)
            </a>
            <a href="http://127.0.0.1:8000/transaksi/penjualan/create" class="action-btn success">
                ✓ Test Payment Flow
            </a>
        </p>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>
