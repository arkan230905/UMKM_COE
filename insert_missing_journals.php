<!DOCTYPE html>
<html>
<head>
    <title>Insert Missing Journal Entries</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Insert Missing Journal Entries</h1>
    
    <?php
    // Database connection
    $host = '127.0.0.1';
    $dbname = 'eadt_umkm';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p class='success'>✓ Connected to database</p>";
        
        // Get COA IDs
        $coas = [];
        $stmt = $pdo->query("SELECT id, kode_akun FROM coas WHERE kode_akun IN ('52', '54', '112', '513', '514', '515', '550', '551')");
        while ($row = $stmt->fetch()) {
            $coas[$row['kode_akun']] = $row['id'];
        }
        
        echo "<p class='info'>COA IDs found:</p>";
        echo "<pre>" . print_r($coas, true) . "</pre>";
        
        // Check existing payroll entries
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM journal_entries WHERE ref_type = 'payroll'");
        $existing_payroll = $stmt->fetch()['count'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM journal_entries WHERE ref_type = 'expense_payment'");
        $existing_expense = $stmt->fetch()['count'];
        
        echo "<p class='info'>Existing entries: Payroll = $existing_payroll, Expense Payment = $existing_expense</p>";
        
        if ($existing_payroll == 0) {
            echo "<h2>Creating Payroll Entries...</h2>";
            
            // Penggajian 1: Budi Susanto
            $stmt = $pdo->prepare("INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute(['payroll', 1, '2026-04-24', 'Penggajian']);
            $entry_id = $pdo->lastInsertId();
            
            $lines = [
                [$entry_id, $coas['52'], 140000, 0, 'Gaji Pokok'],
                [$entry_id, $coas['513'], 525000, 0, 'Beban Tunjangan'],
                [$entry_id, $coas['514'], 100000, 0, 'Beban Asuransi'],
                [$entry_id, $coas['112'], 0, 765000, 'Pembayaran Gaji']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            foreach ($lines as $line) {
                $stmt->execute($line);
            }
            echo "<p class='success'>✓ Created Penggajian 1 (Entry ID: $entry_id)</p>";
            
            // Penggajian 3: Ahmad Suryanto
            $stmt = $pdo->prepare("INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute(['payroll', 3, '2026-04-24', 'Penggajian']);
            $entry_id = $pdo->lastInsertId();
            
            $lines = [
                [$entry_id, $coas['52'], 72000, 0, 'Gaji Pokok'],
                [$entry_id, $coas['513'], 495000, 0, 'Beban Tunjangan'],
                [$entry_id, $coas['514'], 80000, 0, 'Beban Asuransi'],
                [$entry_id, $coas['515'], 200000, 0, 'Beban Bonus'],
                [$entry_id, $coas['112'], 0, 847000, 'Pembayaran Gaji']
            ];
            
            foreach ($lines as $line) {
                $stmt->execute($line);
            }
            echo "<p class='success'>✓ Created Penggajian 3 (Entry ID: $entry_id)</p>";
            
            // Penggajian 4: Rina Wijaya
            $stmt = $pdo->prepare("INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute(['payroll', 4, '2026-04-25', 'Penggajian']);
            $entry_id = $pdo->lastInsertId();
            
            $lines = [
                [$entry_id, $coas['52'], 51000, 0, 'Gaji Pokok'],
                [$entry_id, $coas['513'], 475000, 0, 'Beban Tunjangan'],
                [$entry_id, $coas['112'], 0, 526000, 'Pembayaran Gaji']
            ];
            
            foreach ($lines as $line) {
                $stmt->execute($line);
            }
            echo "<p class='success'>✓ Created Penggajian 4 (Entry ID: $entry_id)</p>";
            
            // Penggajian 5: Dedi Gunawan
            $stmt = $pdo->prepare("INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute(['payroll', 5, '2026-04-26', 'Penggajian']);
            $entry_id = $pdo->lastInsertId();
            
            $lines = [
                [$entry_id, $coas['54'], 2500000, 0, 'BOP Tenaga Kerja Tidak Langsung'],
                [$entry_id, $coas['513'], 600000, 0, 'Beban Tunjangan'],
                [$entry_id, $coas['514'], 150000, 0, 'Beban Asuransi'],
                [$entry_id, $coas['112'], 0, 3250000, 'Pembayaran Gaji']
            ];
            
            foreach ($lines as $line) {
                $stmt->execute($line);
            }
            echo "<p class='success'>✓ Created Penggajian 5 (Entry ID: $entry_id)</p>";
        } else {
            echo "<p class='info'>Payroll entries already exist</p>";
        }
        
        if ($existing_expense == 0) {
            echo "<h2>Creating Expense Payment Entries...</h2>";
            
            // Pembayaran Beban 1: Sewa
            $stmt = $pdo->prepare("INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute(['expense_payment', 1, '2026-04-24', 'Pembayaran Beban']);
            $entry_id = $pdo->lastInsertId();
            
            $lines = [
                [$entry_id, $coas['551'], 1500000, 0, 'BOP Sewa Tempat'],
                [$entry_id, $coas['112'], 0, 1500000, 'Pembayaran via Kas']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            foreach ($lines as $line) {
                $stmt->execute($line);
            }
            echo "<p class='success'>✓ Created Pembayaran Beban 1 (Entry ID: $entry_id)</p>";
            
            // Pembayaran Beban 2: Listrik
            $stmt = $pdo->prepare("INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute(['expense_payment', 2, '2026-04-29', 'Pembayaran Beban']);
            $entry_id = $pdo->lastInsertId();
            
            $lines = [
                [$entry_id, $coas['550'], 2030000, 0, 'BOP Listrik'],
                [$entry_id, $coas['112'], 0, 2030000, 'Pembayaran via Kas']
            ];
            
            foreach ($lines as $line) {
                $stmt->execute($line);
            }
            echo "<p class='success'>✓ Created Pembayaran Beban 2 (Entry ID: $entry_id)</p>";
        } else {
            echo "<p class='info'>Expense payment entries already exist</p>";
        }
        
        // Verify totals
        echo "<h2>Verification</h2>";
        $stmt = $pdo->query("
            SELECT 
                SUM(jl.debit) as total_debit,
                SUM(jl.credit) as total_credit
            FROM journal_entries je
            LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
        ");
        $totals = $stmt->fetch();
        
        echo "<p class='info'>Total Debit: Rp " . number_format($totals['total_debit']) . "</p>";
        echo "<p class='info'>Total Credit: Rp " . number_format($totals['total_credit']) . "</p>";
        
        if ($totals['total_debit'] == $totals['total_credit']) {
            echo "<p class='success'>✓ Journal is balanced!</p>";
        } else {
            echo "<p class='error'>✗ Journal is not balanced!</p>";
        }
        
        echo "<h2>Success!</h2>";
        echo "<p class='success'>All missing journal entries have been created. Please refresh your Jurnal Umum page.</p>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Database error: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <hr>
    <p><a href="javascript:history.back()">← Back</a> | <a href="javascript:location.reload()">Refresh</a></p>
</body>
</html>