<!DOCTYPE html>
<html>
<head>
    <title>Analisis Duplikasi Pembayaran Beban</title>
    <style>
        body { font-family: monospace; background: #f5f5f5; padding: 20px; }
        pre { background: white; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .section { margin: 20px 0; }
        .header { background: #333; color: white; padding: 10px; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

<div class="section">
    <div class="header">ANALISIS DUPLIKASI JOURNAL ENTRIES</div>
    <div class="header">Pembayaran Beban: 28/04/2026 - 29/04/2026</div>
</div>

<?php
use Illuminate\Support\Facades\DB;

// 1. Query entries pembayaran beban
$entries = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
    ->whereBetween(DB::raw('DATE(je.entry_date)'), ['2026-04-28', '2026-04-29'])
    ->select(
        'je.id',
        'je.entry_date',
        'je.ref_type',
        'je.description',
        'je.created_at',
        DB::raw('COUNT(jl.id) as line_count'),
        DB::raw('SUM(jl.debit) as total_debit'),
        DB::raw('SUM(jl.credit) as total_credit')
    )
    ->groupBy('je.id')
    ->orderBy('je.entry_date')
    ->orderBy('je.id')
    ->get();
?>

<div class="section">
    <div class="header">1. JOURNAL ENTRIES (28-29 April 2026)</div>
    <p>Total entries ditemukan: <strong><?php echo count($entries); ?></strong></p>
    
    <table>
        <tr>
            <th>Entry ID</th>
            <th>Tanggal</th>
            <th>Ref Type</th>
            <th>Deskripsi</th>
            <th>Lines</th>
            <th>Total Debit</th>
            <th>Total Credit</th>
            <th>Created</th>
        </tr>
        <?php foreach ($entries as $entry): ?>
        <tr>
            <td><?php echo $entry->id; ?></td>
            <td><?php echo $entry->entry_date; ?></td>
            <td><?php echo $entry->ref_type; ?></td>
            <td><?php echo $entry->description; ?></td>
            <td><?php echo $entry->line_count; ?></td>
            <td><?php echo number_format($entry->total_debit, 2); ?></td>
            <td><?php echo number_format($entry->total_credit, 2); ?></td>
            <td><?php echo $entry->created_at; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php
// 2. Detail lines untuk setiap entry
?>
<div class="section">
    <div class="header">2. DETAIL LINES SETIAP ENTRY</div>
    
    <?php foreach ($entries as $entry): ?>
    <div style="margin: 15px 0; padding: 10px; background: white; border-radius: 5px;">
        <strong>Entry ID: <?php echo $entry->id; ?></strong> - <?php echo $entry->entry_date; ?> - <?php echo $entry->description; ?>
        
        <?php
        $lines = DB::table('journal_lines')
            ->where('journal_entry_id', $entry->id)
            ->orderBy('id')
            ->get();
        ?>
        
        <table style="margin-top: 10px;">
            <tr>
                <th>Line ID</th>
                <th>Account ID</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
            <?php foreach ($lines as $line): ?>
            <tr>
                <td><?php echo $line->id; ?></td>
                <td><?php echo $line->account_id; ?></td>
                <td><?php echo number_format($line->debit, 2); ?></td>
                <td><?php echo number_format($line->credit, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endforeach; ?>
</div>

<?php
// 3. Cari duplikasi
$duplicates = DB::table('journal_entries as je1')
    ->join('journal_entries as je2', function($join) {
        $join->on(DB::raw('DATE(je1.entry_date)'), '=', DB::raw('DATE(je2.entry_date)'))
             ->on('je1.description', '=', 'je2.description')
             ->whereRaw('je1.id < je2.id');
    })
    ->whereBetween(DB::raw('DATE(je1.entry_date)'), ['2026-04-28', '2026-04-29'])
    ->select(
        'je1.id as entry1_id',
        'je2.id as entry2_id',
        'je1.entry_date',
        'je1.description',
        'je1.created_at as created1',
        'je2.created_at as created2'
    )
    ->get();
?>

<div class="section">
    <div class="header">3. ANALISIS DUPLIKASI (Tanggal + Deskripsi)</div>
    <p>Duplikasi ditemukan: <strong><?php echo count($duplicates); ?></strong></p>
    
    <?php if (count($duplicates) > 0): ?>
    <table>
        <tr>
            <th>Entry 1</th>
            <th>Entry 2</th>
            <th>Tanggal</th>
            <th>Deskripsi</th>
            <th>Created 1</th>
            <th>Created 2</th>
        </tr>
        <?php foreach ($duplicates as $dup): ?>
        <tr>
            <td><?php echo $dup->entry1_id; ?></td>
            <td><?php echo $dup->entry2_id; ?></td>
            <td><?php echo $dup->entry_date; ?></td>
            <td><?php echo $dup->description; ?></td>
            <td><?php echo $dup->created1; ?></td>
            <td><?php echo $dup->created2; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <p>Tidak ada duplikasi berdasarkan tanggal dan deskripsi yang sama.</p>
    <?php endif; ?>
</div>

<?php
// 4. Cari entries dengan nominal sama
$sameAmount = DB::table('journal_entries as je1')
    ->join('journal_lines as jl1', 'je1.id', '=', 'jl1.journal_entry_id')
    ->join('journal_entries as je2', function($join) {
        $join->on(DB::raw('DATE(je1.entry_date)'), '=', DB::raw('DATE(je2.entry_date)'))
             ->whereRaw('je1.id < je2.id');
    })
    ->join('journal_lines as jl2', function($join) {
        $join->on('je2.id', '=', 'jl2.journal_entry_id')
             ->on('jl1.account_id', '=', 'jl2.account_id')
             ->on('jl1.debit', '=', 'jl2.debit')
             ->on('jl1.credit', '=', 'jl2.credit');
    })
    ->whereBetween(DB::raw('DATE(je1.entry_date)'), ['2026-04-28', '2026-04-29'])
    ->select(
        'je1.id as entry1_id',
        'je2.id as entry2_id',
        'je1.entry_date',
        'jl1.account_id',
        'jl1.debit',
        'jl1.credit'
    )
    ->distinct()
    ->get();
?>

<div class="section">
    <div class="header">4. ENTRIES DENGAN NOMINAL SAMA PER AKUN</div>
    <p>Entries dengan nominal sama: <strong><?php echo count($sameAmount); ?></strong></p>
    
    <?php if (count($sameAmount) > 0): ?>
    <table>
        <tr>
            <th>Entry 1</th>
            <th>Entry 2</th>
            <th>Tanggal</th>
            <th>Account ID</th>
            <th>Debit</th>
            <th>Credit</th>
        </tr>
        <?php foreach ($sameAmount as $item): ?>
        <tr>
            <td><?php echo $item->entry1_id; ?></td>
            <td><?php echo $item->entry2_id; ?></td>
            <td><?php echo $item->entry_date; ?></td>
            <td><?php echo $item->account_id; ?></td>
            <td><?php echo number_format($item->debit, 2); ?></td>
            <td><?php echo number_format($item->credit, 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <p>Tidak ada entries dengan nominal dan akun yang sama.</p>
    <?php endif; ?>
</div>

<?php
// 5. Summary
$totalEntries = DB::table('journal_entries')
    ->whereBetween(DB::raw('DATE(entry_date)'), ['2026-04-28', '2026-04-29'])
    ->count();

$totalLines = DB::table('journal_entries as je')
    ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
    ->whereBetween(DB::raw('DATE(je.entry_date)'), ['2026-04-28', '2026-04-29'])
    ->count();
?>

<div class="section">
    <div class="header">5. SUMMARY</div>
    <table>
        <tr>
            <th>Metrik</th>
            <th>Nilai</th>
        </tr>
        <tr>
            <td>Total Journal Entries (28-29 April)</td>
            <td><?php echo $totalEntries; ?></td>
        </tr>
        <tr>
            <td>Total Journal Lines</td>
            <td><?php echo $totalLines; ?></td>
        </tr>
        <tr>
            <td>Duplikasi Terdeteksi</td>
            <td><?php echo count($duplicates); ?></td>
        </tr>
        <tr>
            <td>Entries dengan Nominal Sama</td>
            <td><?php echo count($sameAmount); ?></td>
        </tr>
    </table>
</div>

</body>
</html>
