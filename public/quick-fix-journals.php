<?php
// Quick fix for BTKL & BOP journal positions

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>BTKL & BOP Journal Fix</h1>";
echo "<pre>";

// First, let's see what columns exist in journal_lines
echo "Checking journal_lines table structure...\n";
$result = $conn->query("DESCRIBE journal_lines");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[$row['Field']] = $row['Type'];
    echo "  Column: {$row['Field']} ({$row['Type']})\n";
}

echo "\n";

// Check for journal_entries structure too
echo "Checking journal_entries table structure...\n";
$result = $conn->query("DESCRIBE journal_entries");
while ($row = $result->fetch_assoc()) {
    echo "  Column: {$row['Field']} ({$row['Type']})\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Checking current BTKL & BOP journal entries...\n";
echo str_repeat("=", 60) . "\n\n";

// Check current status - look for BOP entries
$sql = "SELECT je.id, je.description, je.ref_type, COUNT(*) as line_count
        FROM journal_entries je
        LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
        WHERE je.description LIKE '%BOP%' OR je.description LIKE '%BTKL%'
        GROUP BY je.id
        LIMIT 10";

$result = $conn->query($sql);
if ($result) {
    echo "Found " . $result->num_rows . " related entries:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  ID: {$row['id']}, Type: {$row['ref_type']}, Lines: {$row['line_count']}, Desc: {$row['description']}\n";
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}

echo "\n";

// Now let's look at actual production journals
$sql = "SELECT je.id, je.description, je.ref_type
        FROM journal_entries je
        WHERE je.ref_type LIKE '%production%'
        LIMIT 5";

$result = $conn->query($sql);
if ($result) {
    echo "Sample production journal entries:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  ID: {$row['id']}, Type: {$row['ref_type']}, Desc: {$row['description']}\n";
        
        // Show the lines
        $linesql = "SELECT * FROM journal_lines WHERE journal_entry_id = {$row['id']}";
        $linesresult = $conn->query($linesql);
        while ($line = $linesresult->fetch_assoc()) {
            echo "    - Debit: {$line['debit']}, Credit: {$line['credit']}, COA: {$line['coa_id']}\n";
        }
    }
}

$conn->close();
echo "</pre>";
?>
