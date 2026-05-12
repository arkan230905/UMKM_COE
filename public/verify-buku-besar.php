<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Buku Besar vs Jurnal Umum</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
        .ok { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 12px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .summary { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .summary.error { background: #ffebee; border-left-color: #dc3545; }
        .mismatch { background: #fff3cd; }
    </style>
</head>
<body>
<div class="container">
    <h1>✓ Verify Buku Besar vs Jurnal Umum</h1>
    <p>Waktu: <?php echo date('Y-m-d H:i:s'); ?></p>

    <?php
    // Get all accounts from COA
    $sql = "SELECT id, kode_akun, nama_akun FROM coas ORDER BY kode_akun";
    $result = $conn->query($sql);
    
    $total_accounts = 0;
    $correct_accounts = 0;
    $error_accounts = 0;
    $mismatches = [];
    
    while ($coa = $result->fetch_assoc()) {
        $total_accounts++;
        
        // Get debit and credit from journal_lines
        $sql_journal = "SELECT 
                            SUM(CASE WHEN debit > 0 THEN debit ELSE 0 END) as total_debit,
                            SUM(CASE WHEN credit > 0 THEN credit ELSE 0 END) as total_credit
                        FROM journal_lines
                        WHERE coa_id = " . $coa['id'];
        
        $result_journal = $conn->query($sql_journal);
        $journal_data = $result_journal->fetch_assoc();
        
        $journal_debit = $journal_data['total_debit'] ?? 0;
        $journal_credit = $journal_data['total_credit'] ?? 0;
        
        // Get debit and credit from buku_besar (if exists)
        $sql_buku = "SELECT 
                        SUM(CASE WHEN debit > 0 THEN debit ELSE 0 END) as total_debit,
                        SUM(CASE WHEN credit > 0 THEN credit ELSE 0 END) as total_credit
                    FROM buku_besars
                    WHERE coa_id = " . $coa['id'];
        
        $result_buku = $conn->query($sql_buku);
        
        if ($result_buku && $result_buku->num_rows > 0) {
            $buku_data = $result_buku->fetch_assoc();
            $buku_debit = $buku_data['total_debit'] ?? 0;
            $buku_credit = $buku_data['total_credit'] ?? 0;
            
            // Compare
            if ($journal_debit == $buku_debit && $journal_credit == $buku_credit) {
                $correct_accounts++;
            } else {
                $error_accounts++;
                $mismatches[] = [
                    'kode' => $coa['kode_akun'],
                    'nama' => $coa['nama_akun'],
                    'journal_debit' => $journal_debit,
                    'journal_credit' => $journal_credit,
                    'buku_debit' => $buku_debit,
                    'buku_credit' => $buku_credit
                ];
            }
        } else {
            // No buku_besar entry
            if ($journal_debit > 0 || $journal_credit > 0) {
                $error_accounts++;
                $mismatches[] = [
                    'kode' => $coa['kode_akun'],
                    'nama' => $coa['nama_akun'],
                    'journal_debit' => $journal_debit,
                    'journal_credit' => $journal_credit,
                    'buku_debit' => 0,
                    'buku_credit' => 0,
                    'status' => 'NO_BUKU_BESAR'
                ];
            }
        }
    }
    ?>

    <div class="card">
        <div class="title">📊 Summary</div>
        
        <div class="summary <?php echo $error_accounts > 0 ? 'error' : ''; ?>">
            <strong><?php echo $error_accounts == 0 ? '✅ SEMUA BENAR!' : '⚠ ADA PERBEDAAN'; ?></strong>
            <p>Total Akun: <?php echo $total_accounts; ?></p>
            <p><span class="ok">✓ Sesuai: <?php echo $correct_accounts; ?></span></p>
            <p><span class="error">✗ Tidak Sesuai: <?php echo $error_accounts; ?></span></p>
        </div>
    </div>

    <?php if (!empty($mismatches)): ?>
    <div class="card">
        <div class="title">❌ Akun dengan Perbedaan</div>
        
        <table>
            <tr>
                <th>Kode</th>
                <th>Nama Akun</th>
                <th colspan="2">Jurnal Umum</th>
                <th colspan="2">Buku Besar</th>
                <th>Status</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th></th>
            </tr>
            
            <?php foreach ($mismatches as $mismatch): ?>
            <tr class="mismatch">
                <td><?php echo $mismatch['kode']; ?></td>
                <td><?php echo substr($mismatch['nama'], 0, 40); ?></td>
                <td><?php echo number_format($mismatch['journal_debit'], 0, ',', '.'); ?></td>
                <td><?php echo number_format($mismatch['journal_credit'], 0, ',', '.'); ?></td>
                <td><?php echo number_format($mismatch['buku_debit'], 0, ',', '.'); ?></td>
                <td><?php echo number_format($mismatch['buku_credit'], 0, ',', '.'); ?></td>
                <td>
                    <?php 
                    if (isset($mismatch['status']) && $mismatch['status'] == 'NO_BUKU_BESAR') {
                        echo '<span class="error">NO ENTRY</span>';
                    } else {
                        echo '<span class="error">MISMATCH</span>';
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="title">📈 Akun yang Sesuai</div>
        
        <?php
        // Show accounts that match
        $sql = "SELECT id, kode_akun, nama_akun FROM coas ORDER BY kode_akun";
        $result = $conn->query($sql);
        
        $matching_accounts = [];
        
        while ($coa = $result->fetch_assoc()) {
            $sql_journal = "SELECT 
                                SUM(CASE WHEN debit > 0 THEN debit ELSE 0 END) as total_debit,
                                SUM(CASE WHEN credit > 0 THEN credit ELSE 0 END) as total_credit
                            FROM journal_lines
                            WHERE coa_id = " . $coa['id'];
            
            $result_journal = $conn->query($sql_journal);
            $journal_data = $result_journal->fetch_assoc();
            
            $journal_debit = $journal_data['total_debit'] ?? 0;
            $journal_credit = $journal_data['total_credit'] ?? 0;
            
            $sql_buku = "SELECT 
                            SUM(CASE WHEN debit > 0 THEN debit ELSE 0 END) as total_debit,
                            SUM(CASE WHEN credit > 0 THEN credit ELSE 0 END) as total_credit
                        FROM buku_besars
                        WHERE coa_id = " . $coa['id'];
            
            $result_buku = $conn->query($sql_buku);
            
            if ($result_buku && $result_buku->num_rows > 0) {
                $buku_data = $result_buku->fetch_assoc();
                $buku_debit = $buku_data['total_debit'] ?? 0;
                $buku_credit = $buku_data['total_credit'] ?? 0;
                
                if ($journal_debit == $buku_debit && $journal_credit == $buku_credit && ($journal_debit > 0 || $journal_credit > 0)) {
                    $matching_accounts[] = [
                        'kode' => $coa['kode_akun'],
                        'nama' => $coa['nama_akun'],
                        'debit' => $journal_debit,
                        'credit' => $journal_credit
                    ];
                }
            }
        }
        ?>
        
        <p>Total akun yang sesuai: <strong><?php echo count($matching_accounts); ?></strong></p>
        
        <table>
            <tr>
                <th>Kode</th>
                <th>Nama Akun</th>
                <th>Debit</th>
                <th>Kredit</th>
            </tr>
            
            <?php foreach (array_slice($matching_accounts, 0, 20) as $account): ?>
            <tr>
                <td><?php echo $account['kode']; ?></td>
                <td><?php echo substr($account['nama'], 0, 50); ?></td>
                <td><?php echo number_format($account['debit'], 0, ',', '.'); ?></td>
                <td><?php echo number_format($account['credit'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <?php if (count($matching_accounts) > 20): ?>
        <p>... dan <?php echo count($matching_accounts) - 20; ?> akun lainnya</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="title">💡 Rekomendasi</div>
        
        <?php if ($error_accounts == 0): ?>
        <p><span class="ok">✅ Buku Besar sudah sesuai dengan Jurnal Umum!</span></p>
        <p>Semua data akuntansi sudah konsisten dan benar.</p>
        <?php else: ?>
        <p><span class="error">⚠ Ada <?php echo $error_accounts; ?> akun yang tidak sesuai</span></p>
        <p>Kemungkinan penyebab:</p>
        <ul>
            <li>Buku Besar belum di-update dari Jurnal Umum</li>
            <li>Ada perubahan di Jurnal Umum yang belum di-sinkronisasi</li>
            <li>Ada entry di Buku Besar yang tidak ada di Jurnal Umum</li>
        </ul>
        <p><strong>Solusi:</strong> Jalankan proses posting dari Jurnal Umum ke Buku Besar</p>
        <?php endif; ?>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>
