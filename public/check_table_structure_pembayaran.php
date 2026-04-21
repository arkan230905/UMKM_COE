<?php
/**
 * Script untuk melihat struktur tabel journal_entries dan journal_lines
 * Akses: http://localhost/check_table_structure_pembayaran.php
 */

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
    <title>Struktur Tabel Journal</title>
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
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>

<div class="container">
    <div class="section">
        <div class="header">STRUKTUR TABEL JOURNAL ENTRIES & JOURNAL LINES</div>
    </div>

    <!-- JOURNAL_ENTRIES -->
    <div class="section">
        <div class="header">1. TABEL: journal_entries</div>
        
        <?php
        $sql = "DESCRIBE journal_entries";
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><code>" . $col['Field'] . "</code></td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? '-') . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        ?>
        
        <div class="info">
            <strong>Penjelasan:</strong>
            <ul>
                <li><code>id</code>: Primary key, auto increment</li>
                <li><code>entry_date</code>: Tanggal entry jurnal</li>
                <li><code>ref_type</code>: Tipe referensi (expense_payment, purchase, sales, dll)</li>
                <li><code>description</code>: Deskripsi entry</li>
                <li><code>created_at</code>: Waktu entry dibuat</li>
                <li><code>updated_at</code>: Waktu entry terakhir diupdate</li>
            </ul>
        </div>
    </div>

    <!-- JOURNAL_LINES -->
    <div class="section">
        <div class="header">2. TABEL: journal_lines</div>
        
        <?php
        $sql = "DESCRIBE journal_lines";
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><code>" . $col['Field'] . "</code></td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? '-') . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        ?>
        
        <div class="info">
            <strong>Penjelasan:</strong>
            <ul>
                <li><code>id</code>: Primary key, auto increment</li>
                <li><code>journal_entry_id</code>: Foreign key ke journal_entries</li>
                <li><code>account_id</code>: ID akun yang digunakan</li>
                <li><code>debit</code>: Nominal debit</li>
                <li><code>credit</code>: Nominal credit</li>
                <li><code>created_at</code>: Waktu line dibuat</li>
                <li><code>updated_at</code>: Waktu line terakhir diupdate</li>
            </ul>
        </div>
    </div>

    <!-- SAMPLE DATA -->
    <div class="section">
        <div class="header">3. SAMPLE DATA JOURNAL ENTRIES (28-29 April 2026)</div>
        
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
        ORDER BY je.entry_date DESC
        LIMIT 10
        ";
        
        $stmt = $pdo->query($sql);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($entries) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Tanggal</th><th>Ref Type</th><th>Deskripsi</th><th>Lines</th><th>Total Debit</th><th>Total Credit</th><th>Created</th></tr>";
            
            foreach ($entries as $entry) {
                echo "<tr>";
                echo "<td>" . $entry['id'] . "</td>";
                echo "<td>" . $entry['entry_date'] . "</td>";
                echo "<td>" . $entry['ref_type'] . "</td>";
                echo "<td>" . substr($entry['description'], 0, 50) . (strlen($entry['description']) > 50 ? '...' : '') . "</td>";
                echo "<td>" . $entry['line_count'] . "</td>";
                echo "<td>" . number_format($entry['total_debit'], 2) . "</td>";
                echo "<td>" . number_format($entry['total_credit'], 2) . "</td>";
                echo "<td>" . substr($entry['created_at'], 0, 19) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<div class='info'>Tidak ada data pada tanggal 28-29 April 2026</div>";
        }
        ?>
    </div>

    <!-- SAMPLE LINES -->
    <div class="section">
        <div class="header">4. SAMPLE DATA JOURNAL LINES</div>
        
        <?php
        $sql = "
        SELECT 
            jl.id,
            jl.journal_entry_id,
            jl.account_id,
            jl.debit,
            jl.credit,
            jl.created_at
        FROM journal_entries je
        JOIN journal_lines jl ON je.id = jl.journal_entry_id
        WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
        ORDER BY je.entry_date DESC, jl.id
        LIMIT 20
        ";
        
        $stmt = $pdo->query($sql);
        $lines = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($lines) > 0) {
            echo "<table>";
            echo "<tr><th>Line ID</th><th>Entry ID</th><th>Account ID</th><th>Debit</th><th>Credit</th><th>Created</th></tr>";
            
            foreach ($lines as $line) {
                echo "<tr>";
                echo "<td>" . $line['id'] . "</td>";
                echo "<td>" . $line['journal_entry_id'] . "</td>";
                echo "<td>" . $line['account_id'] . "</td>";
                echo "<td>" . number_format($line['debit'], 2) . "</td>";
                echo "<td>" . number_format($line['credit'], 2) . "</td>";
                echo "<td>" . substr($line['created_at'], 0, 19) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<div class='info'>Tidak ada lines pada tanggal 28-29 April 2026</div>";
        }
        ?>
    </div>

    <!-- STATISTICS -->
    <div class="section">
        <div class="header">5. STATISTIK DATABASE</div>
        
        <?php
        $stats = [];
        
        // Total entries
        $sql = "SELECT COUNT(*) as total FROM journal_entries";
        $stmt = $pdo->query($sql);
        $stats['Total Entries'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total lines
        $sql = "SELECT COUNT(*) as total FROM journal_lines";
        $stmt = $pdo->query($sql);
        $stats['Total Lines'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Entries 28-29 April
        $sql = "SELECT COUNT(*) as total FROM journal_entries WHERE DATE(entry_date) BETWEEN '2026-04-28' AND '2026-04-29'";
        $stmt = $pdo->query($sql);
        $stats['Entries 28-29 April'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Lines 28-29 April
        $sql = "SELECT COUNT(*) as total FROM journal_entries je JOIN journal_lines jl ON je.id = jl.journal_entry_id WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'";
        $stmt = $pdo->query($sql);
        $stats['Lines 28-29 April'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ref types
        $sql = "SELECT ref_type, COUNT(*) as total FROM journal_entries GROUP BY ref_type ORDER BY total DESC";
        $stmt = $pdo->query($sql);
        $refTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Metrik</th><th>Nilai</th></tr>";
        
        foreach ($stats as $key => $value) {
            echo "<tr><td>" . $key . "</td><td>" . $value . "</td></tr>";
        }
        
        echo "</table>";
        
        echo "<h3>Ref Types Distribution:</h3>";
        echo "<table>";
        echo "<tr><th>Ref Type</th><th>Total</th></tr>";
        
        foreach ($refTypes as $type) {
            echo "<tr>";
            echo "<td>" . ($type['ref_type'] ?? 'NULL') . "</td>";
            echo "<td>" . $type['total'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        ?>
    </div>

</div>

</body>
</html>
