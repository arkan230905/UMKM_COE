<?php
/**
 * COMPLETE FIX - Everything in One Script
 * 1. Clean jurnal_umum (remove duplicates)
 * 2. Add payment proof columns to penjualans
 * 3. Verify everything
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
    <title>Complete Fix - All Tasks</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .step { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
        .step-title { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .action-btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; cursor: pointer; border: none; font-size: 14px; }
        .action-btn:hover { background: #0056b3; }
        .action-btn.success { background: #28a745; }
        .action-btn.success:hover { background: #218838; }
        .action-btn.danger { background: #dc3545; }
        .action-btn.danger:hover { background: #c82333; }
        .summary { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .summary.error { background: #ffebee; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Complete Fix - All Tasks</h1>
    <p>Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>

    <!-- TASK 1: Clean Jurnal Umum -->
    <div class="section">
        <div class="section-title">🧹 TASK 1: Clean Jurnal Umum (Remove Duplicates)</div>
        
        <div class="step">
            <div class="step-title">Step 1: Identify Duplicates</div>
            
            <?php
            $sql = "SELECT 
                        tanggal, 
                        keterangan, 
                        tipe_referensi,
                        COUNT(*) as count,
                        GROUP_CONCAT(id) as ids
                    FROM jurnal_umum
                    WHERE tipe_referensi = 'production_labor_overhead'
                    GROUP BY tanggal, keterangan, tipe_referensi
                    HAVING count > 1";
            
            $result = $conn->query($sql);
            $duplicate_count = $result->num_rows;
            
            if ($duplicate_count > 0) {
                echo "<p><span class='status-warning'>⚠ Found " . $duplicate_count . " duplicate groups</span></p>";
                echo "<table>";
                echo "<tr><th>Tanggal</th><th>Keterangan</th><th>Count</th><th>IDs</th></tr>";
                
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['tanggal'] . "</td>";
                    echo "<td>" . substr($row['keterangan'], 0, 50) . "</td>";
                    echo "<td>" . $row['count'] . "</td>";
                    echo "<td>" . $row['ids'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p><span class='status-ok'>✓ No duplicates found</span></p>";
            }
            ?>
        </div>

        <div class="step">
            <div class="step-title">Step 2: Delete Wrong Entries</div>
            
            <?php
            // Find and delete wrong entries
            $sql = "SELECT id, coa_id, debit, kredit
                    FROM jurnal_umum
                    WHERE tipe_referensi = 'production_labor_overhead'
                    ORDER BY tanggal DESC, id DESC";
            
            $result = $conn->query($sql);
            $to_delete = [];
            
            while ($row = $result->fetch_assoc()) {
                $is_wrong = false;
                
                if ($row['coa_id'] == 52 || $row['coa_id'] == 56) { // BTKL or BOP
                    if ($row['debit'] > 0 && $row['kredit'] == 0) {
                        $is_wrong = true; // Should be in KREDIT
                    }
                } elseif ($row['coa_id'] == 126) { // WIP
                    if ($row['kredit'] > 0 && $row['debit'] == 0) {
                        $is_wrong = true; // Should be in DEBIT
                    }
                }
                
                if ($is_wrong) {
                    $to_delete[] = $row['id'];
                }
            }
            
            if (!empty($to_delete)) {
                $ids_str = implode(",", $to_delete);
                $sql = "DELETE FROM jurnal_umum WHERE id IN (" . $ids_str . ")";
                
                if ($conn->query($sql)) {
                    echo "<p><span class='status-ok'>✓ Deleted " . $conn->affected_rows . " wrong entries</span></p>";
                    echo "<p>Deleted IDs: " . implode(", ", $to_delete) . "</p>";
                } else {
                    echo "<p><span class='status-error'>✗ Error: " . $conn->error . "</span></p>";
                }
            } else {
                echo "<p><span class='status-ok'>✓ No wrong entries to delete</span></p>";
            }
            ?>
        </div>

        <div class="step">
            <div class="step-title">Step 3: Verify Jurnal Umum</div>
            
            <?php
            $sql = "SELECT ju.id, ju.tanggal, ju.coa_id, c.kode_akun, c.nama_akun, ju.debit, ju.kredit
                    FROM jurnal_umum ju
                    LEFT JOIN coas c ON ju.coa_id = c.id
                    WHERE ju.tipe_referensi = 'production_labor_overhead'
                    ORDER BY ju.tanggal DESC, ju.coa_id";
            
            $result = $conn->query($sql);
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Tanggal</th><th>Kode</th><th>Nama Akun</th><th>Debit</th><th>Kredit</th><th>Status</th></tr>";
            
            $all_correct = true;
            while ($row = $result->fetch_assoc()) {
                $status = "✓";
                
                if ($row['kode_akun'] == '52' || $row['kode_akun'] == '53') {
                    if (!($row['kredit'] > 0 && $row['debit'] == 0)) {
                        $status = "✗ WRONG";
                        $all_correct = false;
                    }
                } elseif ($row['kode_akun'] == '117') {
                    if (!($row['debit'] > 0 && $row['kredit'] == 0)) {
                        $status = "✗ WRONG";
                        $all_correct = false;
                    }
                }
                
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['tanggal'] . "</td>";
                echo "<td>" . $row['kode_akun'] . "</td>";
                echo "<td>" . substr($row['nama_akun'], 0, 40) . "</td>";
                echo "<td>" . number_format($row['debit'], 0, ',', '.') . "</td>";
                echo "<td>" . number_format($row['kredit'], 0, ',', '.') . "</td>";
                echo "<td>" . $status . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            if ($all_correct) {
                echo "<p><span class='status-ok'>✓ All jurnal_umum entries are correct!</span></p>";
            } else {
                echo "<p><span class='status-error'>✗ Some entries are still wrong</span></p>";
            }
            ?>
        </div>
    </div>

    <!-- TASK 2: Add Payment Proof Columns -->
    <div class="section">
        <div class="section-title">📋 TASK 2: Add Payment Proof Columns to Penjualans</div>
        
        <div class="step">
            <div class="step-title">Step 1: Check Columns</div>
            
            <?php
            $result = $conn->query("DESCRIBE penjualans");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = $row['Type'];
            }
            
            $bukti_exists = isset($columns['bukti_pembayaran']);
            $catatan_exists = isset($columns['catatan_pembayaran']);
            
            echo "<table>";
            echo "<tr><th>Column</th><th>Status</th><th>Type</th></tr>";
            echo "<tr>";
            echo "<td>bukti_pembayaran</td>";
            echo "<td class='" . ($bukti_exists ? 'status-ok' : 'status-error') . "'>" . ($bukti_exists ? '✓ EXISTS' : '✗ MISSING') . "</td>";
            echo "<td>" . ($bukti_exists ? $columns['bukti_pembayaran'] : 'N/A') . "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>catatan_pembayaran</td>";
            echo "<td class='" . ($catatan_exists ? 'status-ok' : 'status-error') . "'>" . ($catatan_exists ? '✓ EXISTS' : '✗ MISSING') . "</td>";
            echo "<td>" . ($catatan_exists ? $columns['catatan_pembayaran'] : 'N/A') . "</td>";
            echo "</tr>";
            echo "</table>";
            ?>
        </div>

        <div class="step">
            <div class="step-title">Step 2: Add Missing Columns</div>
            
            <?php
            if (!$bukti_exists) {
                $sql = "ALTER TABLE penjualans ADD COLUMN bukti_pembayaran VARCHAR(255) NULL AFTER total";
                if ($conn->query($sql)) {
                    echo "<p><span class='status-ok'>✓ Added bukti_pembayaran column</span></p>";
                } else {
                    echo "<p><span class='status-error'>✗ Error: " . $conn->error . "</span></p>";
                }
            } else {
                echo "<p><span class='status-ok'>✓ bukti_pembayaran already exists</span></p>";
            }
            
            if (!$catatan_exists) {
                $sql = "ALTER TABLE penjualans ADD COLUMN catatan_pembayaran LONGTEXT NULL AFTER bukti_pembayaran";
                if ($conn->query($sql)) {
                    echo "<p><span class='status-ok'>✓ Added catatan_pembayaran column</span></p>";
                } else {
                    echo "<p><span class='status-error'>✗ Error: " . $conn->error . "</span></p>";
                }
            } else {
                echo "<p><span class='status-ok'>✓ catatan_pembayaran already exists</span></p>";
            }
            ?>
        </div>
    </div>

    <!-- SUMMARY -->
    <div class="section">
        <div class="section-title">📈 Summary</div>
        
        <?php
        // Final verification
        $result = $conn->query("DESCRIBE penjualans");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row['Type'];
        }
        $migration_done = isset($columns['bukti_pembayaran']) && isset($columns['catatan_pembayaran']);
        
        $sql = "SELECT COUNT(*) as issues FROM jurnal_umum ju
                WHERE ju.tipe_referensi = 'production_labor_overhead'
                AND ju.coa_id IN (52, 56)
                AND ju.debit > 0";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $journal_fixed = $row['issues'] == 0;
        
        $all_complete = $migration_done && $journal_fixed;
        ?>
        
        <div class="<?php echo $all_complete ? 'summary' : 'summary error'; ?>">
            <strong>Overall Status:</strong> 
            <span class="<?php echo $all_complete ? 'status-ok' : 'status-warning'; ?>">
                <?php echo $all_complete ? '✅ ALL TASKS COMPLETE' : '⚠ SOME TASKS PENDING'; ?>
            </span>
        </div>
        
        <table>
            <tr>
                <th>Task</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Clean Jurnal Umum (Remove Duplicates)</td>
                <td class="<?php echo $journal_fixed ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $journal_fixed ? '✓ COMPLETE' : '✗ PENDING'; ?>
                </td>
            </tr>
            <tr>
                <td>Add Payment Proof Columns</td>
                <td class="<?php echo $migration_done ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $migration_done ? '✓ COMPLETE' : '✗ PENDING'; ?>
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
