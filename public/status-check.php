<?php
// Comprehensive status check for all pending tasks

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
    <title>System Status Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .status-card { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-header { font-size: 18px; font-weight: bold; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .action-btn { display: inline-block; padding: 8px 15px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; cursor: pointer; border: none; }
        .action-btn:hover { background: #0056b3; }
        .action-btn.danger { background: #dc3545; }
        .action-btn.danger:hover { background: #c82333; }
        .action-btn.success { background: #28a745; }
        .action-btn.success:hover { background: #218838; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 System Status Check</h1>
    <p>Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>

    <!-- TASK 3: Penjualan Payment Flow -->
    <div class="status-card">
        <div class="status-header">
            📋 TASK 3: Penjualan Payment Flow - Database Migration
        </div>
        
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
        ?>
        
        <p>
            <strong>Status:</strong> 
            <span class="<?php echo $migration_done ? 'status-ok' : 'status-error'; ?>">
                <?php echo $migration_done ? '✓ COMPLETE' : '✗ PENDING'; ?>
            </span>
        </p>
        
        <table>
            <tr>
                <th>Column</th>
                <th>Status</th>
                <th>Type</th>
            </tr>
            <tr>
                <td>bukti_pembayaran</td>
                <td class="<?php echo $bukti_exists ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $bukti_exists ? '✓ EXISTS' : '✗ MISSING'; ?>
                </td>
                <td><?php echo $bukti_exists ? $columns['bukti_pembayaran'] : 'N/A'; ?></td>
            </tr>
            <tr>
                <td>catatan_pembayaran</td>
                <td class="<?php echo $catatan_exists ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $catatan_exists ? '✓ EXISTS' : '✗ MISSING'; ?>
                </td>
                <td><?php echo $catatan_exists ? $columns['catatan_pembayaran'] : 'N/A'; ?></td>
            </tr>
        </table>
        
        <?php if (!$migration_done): ?>
        <p><strong>Action Required:</strong> Run the migration to add payment proof columns</p>
        <button class="action-btn danger" onclick="runMigration()">Run Migration Now</button>
        <?php else: ?>
        <p class="status-ok">✓ All payment proof columns are in place. Payment flow is ready to use.</p>
        <?php endif; ?>
    </div>

    <!-- TASK 4: BTKL & BOP Journal Fix -->
    <div class="status-card">
        <div class="status-header">
            📊 TASK 4: BTKL & BOP Journal Entry Positions
        </div>
        
        <?php
        // Check BTKL & BOP entries
        $sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN coa_code IN ('52', '53') AND debit > 0 THEN 1 ELSE 0 END) as issues
        FROM journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        WHERE je.ref_type = 'production_labor_overhead'
        ";
        
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $total_entries = $row['total'];
        $issue_count = $row['issues'] ?? 0;
        $journal_fixed = $issue_count == 0;
        ?>
        
        <p>
            <strong>Status:</strong> 
            <span class="<?php echo $journal_fixed ? 'status-ok' : 'status-error'; ?>">
                <?php echo $journal_fixed ? '✓ COMPLETE' : '✗ NEEDS FIX'; ?>
            </span>
        </p>
        
        <p>Total BTKL & BOP entries: <strong><?php echo $total_entries; ?></strong></p>
        
        <?php if ($issue_count > 0): ?>
        <p class="status-error">
            ⚠ Found <strong><?php echo $issue_count; ?></strong> entries with incorrect debit values
        </p>
        <button class="action-btn danger" onclick="fixBTKLBOP()">Fix BTKL & BOP Positions Now</button>
        <?php else: ?>
        <p class="status-ok">✓ All BTKL & BOP entries are correctly positioned in CREDIT column</p>
        <?php endif; ?>
        
        <p style="margin-top: 15px;">
            <button class="action-btn" onclick="showBTKLBOPDetails()">View Details</button>
        </p>
    </div>

    <!-- Summary -->
    <div class="status-card">
        <div class="status-header">📈 Overall Status</div>
        
        <?php
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
    </div>
</div>

<script>
function runMigration() {
    if (confirm('Run migration to add payment proof columns?')) {
        window.location.href = '/check-migration.php';
    }
}

function fixBTKLBOP() {
    if (confirm('Fix BTKL & BOP journal entry positions?')) {
        window.location.href = '/check-btkl-bop.php';
    }
}

function showBTKLBOPDetails() {
    window.location.href = '/check-btkl-bop.php';
}
</script>
</body>
</html>
<?php
$conn->close();
?>
