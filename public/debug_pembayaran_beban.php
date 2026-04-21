<?php
/**
 * Script debug untuk analisis duplikasi pembayaran beban
 * Akses: http://localhost/debug_pembayaran_beban.php
 */

// Koneksi database
$host = '127.0.0.1';
$db = 'eadt_umkm';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Analisis Duplikasi Pembayaran Beban</title>
    <style>
        body { font-family: monospace; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { background: #333; color: white; padding: 15px; border-radius: 5px; margin-bottom: 15px; font-size: 18px; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
        .warning { background: #fff3e0; padding: 10px; border-left: 4px solid #ff9800; margin: 10px 0; }
        .success { background: #e8f5e9; padding: 10px; border-left: 4px solid #4caf50; margin: 10px 0; }
        .error { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
    </style>
</head>
<body>

<div class="container">
    <div class="section">
        <div class="header">ANALISIS DUPLIKASI JOURNAL ENTRIES</div>
        <div class="header" style="background: #666; font-size: 14px;">Pembayaran Beban: 28/04/2026 - 29/04/2026</div>
    </div>

    <!-- 1. JOURNAL ENTRIES -->
    <div class="section">
        <div class="header">1. JOURNAL ENTRIES (28-29 April 2026)</div>
        
        <?php
        $sql = "
        SELECT 
            je.id,
            je.entry_date,
            je.ref_type,
            je.description,
            je.created_at,
            COUNT(jl.id) as line_count,
            SUM(jl.debit) as total_debit,
            SUM(jl.credit) as total_credit
        FROM journal_entries je
        LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
        WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
        GROUP BY je.id
        ORDER BY je.entry_date, je.id
        ";
        
        $stmt = $pdo->query($sql);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='info'>Total entries ditemukan: <strong>" . count($entries) . "</strong></div>";
        
        if (count($entries) > 0) {
            echo "<table>";
            echo "<tr><th>Entry ID</th><th>Tanggal</th><th>Ref Type</th><th>Deskripsi</th><th>Lines</th><th>Total Debit</th><th>Total Credit</th><th>Created</th></tr>";
            
            foreach ($entries as $entry) {
                echo "<tr>";
                echo "<td>" . $entry['id'] . "</td>";
                echo "<td>" . $entry['entry_date'] . "</td>";
                echo "<td>" . $entry['ref_type'] . "</td>";
                echo "<td>" . $entry['description'] . "</td>";
                echo "<td>" . $entry['line_count'] . "</td>";
                echo "<td>" . number_format($entry['total_debit'], 2) . "</td>";
                echo "<td>" . number_format($entry['total_credit'], 2) . "</td>";
                echo "<td>" . $entry['created_at'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<div class='warning'>Tidak ada entries pada tanggal 28-29 April 2026</div>";
        }
        ?>
    </div>

    <!-- 2. DETAIL LINES -->
    <div class="section">
        <div class="header">2. DETAIL LINES SETIAP ENTRY</div>
        
        <?php
        if (count($entries) > 0) {
            foreach ($entries as $entry) {
                echo "<div style='margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 5px;'>";
                echo "<strong>Entry ID: " . $entry['id'] . "</strong> - " . $entry['entry_date'] . " - " . $entry['description'];
                
                $lineSql = "
                SELECT 
                    jl.id,
                    jl.account_id,
                    jl.debit,
                    jl.credit
                FROM journal_lines jl
                WHERE jl.journal_entry_id = ?
                ORDER BY jl.id
                ";
                
                $lineStmt = $pdo->prepare($lineSql);
                $lineStmt->execute([$entry['id']]);
                $lines = $lineStmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($lines) > 0) {
                    echo "<table style='margin-top: 10px;'>";
                    echo "<tr><th>Line ID</th><th>Account ID</th><th>Debit</th><th>Credit</th></tr>";
                    
                    foreach ($lines as $line) {
                        echo "<tr>";
                        echo "<td>" . $line['id'] . "</td>";
                        echo "<td>" . $line['account_id'] . "</td>";
                        echo "<td>" . number_format($line['debit'], 2) . "</td>";
                        echo "<td>" . number_format($line['credit'], 2) . "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                }
                
                echo "</div>";
            }
        }
        ?>
    </div>

    <!-- 3. DUPLIKASI ANALYSIS -->
    <div class="section">
        <div class="header">3. ANALISIS DUPLIKASI (Tanggal + Deskripsi)</div>
        
        <?php
        $dupSql = "
        SELECT 
            je1.id as entry1_id,
            je2.id as entry2_id,
            je1.entry_date,
            je1.description,
            je1.created_at as created1,
            je2.created_at as created2
        FROM journal_entries je1
        JOIN journal_entries je2 ON 
            DATE(je1.entry_date) = DATE(je2.entry_date) AND
            je1.description = je2.description AND
            je1.id < je2.id
        WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
        ";
        
        $stmt = $pdo->query($dupSql);
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($duplicates) > 0) {
            echo "<div class='warning'>Duplikasi ditemukan: <strong>" . count($duplicates) . "</strong></div>";
            echo "<table>";
            echo "<tr><th>Entry 1</th><th>Entry 2</th><th>Tanggal</th><th>Deskripsi</th><th>Created 1</th><th>Created 2</th></tr>";
            
            foreach ($duplicates as $dup) {
                echo "<tr>";
                echo "<td>" . $dup['entry1_id'] . "</td>";
                echo "<td>" . $dup['entry2_id'] . "</td>";
                echo "<td>" . $dup['entry_date'] . "</td>";
                echo "<td>" . $dup['description'] . "</td>";
                echo "<td>" . $dup['created1'] . "</td>";
                echo "<td>" . $dup['created2'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<div class='success'>Tidak ada duplikasi berdasarkan tanggal dan deskripsi yang sama.</div>";
        }
        ?>
    </div>

    <!-- 4. NOMINAL SAMA -->
    <div class="section">
        <div class="header">4. ENTRIES DENGAN NOMINAL SAMA PER AKUN</div>
        
        <?php
        $sameSql = "
        SELECT 
            je1.id as entry1_id,
            je2.id as entry2_id,
            je1.entry_date,
            jl1.account_id,
            jl1.debit,
            jl1.credit
        FROM journal_entries je1
        JOIN journal_lines jl1 ON je1.id = jl1.journal_entry_id
        JOIN journal_entries je2 ON DATE(je1.entry_date) = DATE(je2.entry_date) AND je1.id < je2.id
        JOIN journal_lines jl2 ON je2.id = jl2.journal_entry_id
        WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
          AND jl1.account_id = jl2.account_id
          AND jl1.debit = jl2.debit
          AND jl1.credit = jl2.credit
        GROUP BY je1.id, je2.id, jl1.account_id
        ";
        
        $stmt = $pdo->query($sameSql);
        $sameAmount = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($sameAmount) > 0) {
            echo "<div class='warning'>Entries dengan nominal sama: <strong>" . count($sameAmount) . "</strong></div>";
            echo "<table>";
            echo "<tr><th>Entry 1</th><th>Entry 2</th><th>Tanggal</th><th>Account ID</th><th>Debit</th><th>Credit</th></tr>";
            
            foreach ($sameAmount as $item) {
                echo "<tr>";
                echo "<td>" . $item['entry1_id'] . "</td>";
                echo "<td>" . $item['entry2_id'] . "</td>";
                echo "<td>" . $item['entry_date'] . "</td>";
                echo "<td>" . $item['account_id'] . "</td>";
                echo "<td>" . number_format($item['debit'], 2) . "</td>";
                echo "<td>" . number_format($item['credit'], 2) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<div class='success'>Tidak ada entries dengan nominal dan akun yang sama.</div>";
        }
        ?>
    </div>

    <!-- 5. SUMMARY -->
    <div class="section">
        <div class="header">5. SUMMARY</div>
        
        <?php
        $countSql = "SELECT COUNT(*) as total FROM journal_entries WHERE DATE(entry_date) BETWEEN '2026-04-28' AND '2026-04-29'";
        $stmt = $pdo->query($countSql);
        $totalEntries = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $lineSql = "
        SELECT COUNT(*) as total FROM journal_entries je
        JOIN journal_lines jl ON je.id = jl.journal_entry_id
        WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
        ";
        $stmt = $pdo->query($lineSql);
        $totalLines = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        ?>
        
        <table>
            <tr><th>Metrik</th><th>Nilai</th></tr>
            <tr><td>Total Journal Entries (28-29 April)</td><td><?php echo $totalEntries; ?></td></tr>
            <tr><td>Total Journal Lines</td><td><?php echo $totalLines; ?></td></tr>
            <tr><td>Duplikasi Terdeteksi</td><td><?php echo count($duplicates); ?></td></tr>
            <tr><td>Entries dengan Nominal Sama</td><td><?php echo count($sameAmount); ?></td></tr>
        </table>
    </div>

</div>

</body>
</html>
