<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking all transactions in database...\n\n";

echo "=== PEMBELIAN ===\n";
$pembelians = DB::table('pembelians')
    ->select('user_id', DB::raw('COUNT(*) as count'))
    ->groupBy('user_id')
    ->get();

if ($pembelians->isEmpty()) {
    echo "No pembelian records found in database!\n";
} else {
    foreach($pembelians as $p) {
        echo "User ID {$p->user_id}: {$p->count} pembelian\n";
    }
}

echo "\n=== PENJUALAN ===\n";
$penjualans = DB::table('penjualans')
    ->select('user_id', DB::raw('COUNT(*) as count'))
    ->groupBy('user_id')
    ->get();

if ($penjualans->isEmpty()) {
    echo "No penjualan records found in database!\n";
} else {
    foreach($penjualans as $p) {
        echo "User ID {$p->user_id}: {$p->count} penjualan\n";
    }
}

echo "\n=== JURNAL UMUM ===\n";
$journals = DB::table('jurnal_umum')
    ->select('user_id', 'tipe_referensi', DB::raw('COUNT(*) as count'))
    ->groupBy('user_id', 'tipe_referensi')
    ->get();

if ($journals->isEmpty()) {
    echo "No jurnal_umum records found in database!\n";
} else {
    foreach($journals as $j) {
        echo "User ID {$j->user_id}, Type: {$j->tipe_referensi}: {$j->count} entries\n";
    }
}

echo "\n=== USERS ===\n";
$users = DB::table('users')->get(['id', 'name', 'email']);
foreach($users as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}\n";
}
