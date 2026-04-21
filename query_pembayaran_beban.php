<?php
require 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

echo "=== QUERY 1: journal_entries (28-29 April 2026) ===\n";
$result1 = DB::table('journal_entries')
    ->where('tanggal', '>=', '2026-04-28')
    ->where('tanggal', '<=', '2026-04-29')
    ->get(['id', 'tanggal', 'ref_type', 'ref_id', 'memo']);

echo "Total records: " . count($result1) . "\n";
echo json_encode($result1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== QUERY 2: jurnal_umum (28-29 April 2026) ===\n";
$result2 = DB::table('jurnal_umum')
    ->where('tanggal', '>=', '2026-04-28')
    ->where('tanggal', '<=', '2026-04-29')
    ->get(['id', 'tanggal', 'tipe_referensi', 'keterangan']);

echo "Total records: " . count($result2) . "\n";
echo json_encode($result2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== SUMMARY ===\n";
echo "journal_entries records: " . count($result1) . "\n";
echo "jurnal_umum records: " . count($result2) . "\n";
