<?php
/**
 * Debug langsung: simulasi apa yang dilakukan BtklController::create()
 * untuk user yang sedang login
 */

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Cek siapa yang login
$user = Auth::user();
if (!$user) {
    die("TIDAK ADA USER LOGIN. Silakan login dulu lalu akses halaman ini.\n");
}

$userId = $user->id;
echo "=== USER LOGIN ===\n";
echo "ID: $userId | Nama: {$user->name}\n\n";

// Query persis sama dengan controller baru
echo "=== JABATAN BTKL (query DB::table) ===\n";
$jabatans = DB::table('jabatans')
    ->where('kategori', 'btkl')
    ->where('user_id', $userId)
    ->orderBy('nama')
    ->get();

echo "Jumlah jabatan BTKL untuk user $userId: " . $jabatans->count() . "\n";
foreach ($jabatans as $j) {
    echo "  id={$j->id} | nama={$j->nama} | tarif={$j->tarif}\n";
}

echo "\n=== PEGAWAI user $userId ===\n";
$pegawais = DB::table('pegawais')
    ->where('user_id', $userId)
    ->select('id', 'nama', 'jabatan_id', 'jabatan')
    ->get();

echo "Jumlah pegawai: " . $pegawais->count() . "\n";
foreach ($pegawais as $p) {
    echo "  id={$p->id} | nama={$p->nama} | jabatan_id={$p->jabatan_id} | jabatan={$p->jabatan}\n";
}

echo "\n=== HASIL GABUNGAN (yang akan tampil di dropdown) ===\n";
$pegawaiCount = DB::table('pegawais')
    ->select('jabatan_id', DB::raw('COUNT(*) as jumlah'))
    ->where('user_id', $userId)
    ->whereNotNull('jabatan_id')
    ->groupBy('jabatan_id')
    ->pluck('jumlah', 'jabatan_id');

foreach ($jabatans as $j) {
    $count = (int)($pegawaiCount[$j->id] ?? 0);
    echo "  {$j->nama} ({$count} pegawai @ Rp " . number_format($j->tarif) . "/jam)\n";
}

echo "\n=== CEK ROUTE/CONTROLLER YANG AKTIF ===\n";
$controllerFile = app_path('Http/Controllers/MasterData/BtklController.php');
$content = file_get_contents($controllerFile);
echo "File size: " . strlen($content) . " bytes\n";
echo "Pakai getJabatanBtklForUser: " . (strpos($content, 'getJabatanBtklForUser') !== false ? 'YA' : 'TIDAK') . "\n";
echo "Pakai DB::table jabatans: " . (strpos($content, "DB::table('jabatans')") !== false ? 'YA' : 'TIDAK') . "\n";
echo "Pakai where user_id: " . (strpos($content, "where('user_id'") !== false ? 'YA' : 'TIDAK') . "\n";

// Cek apakah ada controller lain yang override
echo "\n=== CEK ROUTE AKTIF UNTUK /master-data/btkl/create ===\n";
$routes = app('router')->getRoutes();
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'btkl') && str_contains($route->uri(), 'create')) {
        echo "URI: " . $route->uri() . "\n";
        echo "Action: " . json_encode($route->getAction()) . "\n";
    }
}
