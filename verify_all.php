<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SEMUA USER PEGAWAI ===\n";
$users = DB::table('users')
    ->whereIn('role', ['pegawai','pegawai_pembelian'])
    ->select('id','email','role','pegawai_id','perusahaan_id')
    ->get();

foreach ($users as $u) {
    $status = [];
    if (!$u->pegawai_id) $status[] = '❌ pegawai_id NULL';
    if (!$u->perusahaan_id) $status[] = '❌ perusahaan_id NULL';
    if (empty($status)) $status[] = '✅ OK';
    
    printf("  User ID=%d | email=%s | pegawai_id=%s | perusahaan_id=%s | %s\n",
        $u->id, $u->email, $u->pegawai_id ?? 'NULL', $u->perusahaan_id ?? 'NULL', implode(' | ', $status));
}

echo "\n=== SEMUA PEGAWAI ===\n";
$pegawais = DB::table('pegawais')
    ->select('id','email','nama','user_id','perusahaan_id')
    ->get();

foreach ($pegawais as $p) {
    $status = [];
    if (!$p->user_id) $status[] = '❌ user_id NULL';
    if (!$p->perusahaan_id) $status[] = '❌ perusahaan_id NULL';
    if (empty($status)) $status[] = '✅ OK';
    
    printf("  Pegawai ID=%d | nama=%s | email=%s | user_id=%s | perusahaan_id=%s | %s\n",
        $p->id, $p->nama, $p->email, $p->user_id ?? 'NULL', $p->perusahaan_id ?? 'NULL', implode(' | ', $status));
}

echo "\n=== CEK ADMIN/OWNER (untuk referensi perusahaan_id) ===\n";
$admins = DB::table('users')
    ->whereIn('role', ['owner','admin'])
    ->whereNotNull('perusahaan_id')
    ->select('id','email','role','perusahaan_id')
    ->get();

foreach ($admins as $a) {
    printf("  Admin/Owner ID=%d | email=%s | role=%s | perusahaan_id=%s\n",
        $a->id, $a->email, $a->role, $a->perusahaan_id);
}
