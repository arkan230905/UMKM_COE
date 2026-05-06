<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Coa;
use App\Models\Satuan;

echo "==============================================\n";
echo "COPY DATA MASTER KE USER YANG SUDAH ADA\n";
echo "==============================================\n\n";

$users = User::whereDoesntHave('coas')->get();

if ($users->isEmpty()) {
    echo "Semua user sudah punya data COA sendiri!\n\n";
    
    $masterCoas = Coa::whereNull('user_id')->count();
    $masterSatuans = Satuan::whereNull('user_id')->count();
    
    if ($masterCoas > 0 || $masterSatuans > 0) {
        echo "DATA MASTER YANG DITEMUKAN:\n";
        echo "COA Master (user_id = NULL): {$masterCoas}\n";
        echo "Satuan Master (user_id = NULL): {$masterSatuans}\n\n";
        echo "Anda bisa hapus dengan: php hapus_data_master_lama.php\n\n";
    }
    
    exit(0);
}

echo "Ditemukan " . $users->count() . " user yang belum punya data COA\n\n";

$totalCoaCopied = 0;
$totalSatuanCopied = 0;

foreach ($users as $user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    echo "Email: {$user->email}\n\n";
    
    echo "Copying COA...\n";
    $masterCoas = Coa::whereNull('user_id')->get();
    
    $coaCopied = 0;
    foreach ($masterCoas as $masterCoa) {
        $exists = Coa::where('user_id', $user->id)
            ->where('kode_akun', $masterCoa->kode_akun)
            ->exists();
        
        if (!$exists) {
            Coa::create([
                'user_id' => $user->id,
                'kode_akun' => $masterCoa->kode_akun,
                'nama_akun' => $masterCoa->nama_akun,
                'tipe_akun' => $masterCoa->tipe_akun,
                'kategori_akun' => $masterCoa->kategori_akun,
                'is_akun_header' => $masterCoa->is_akun_header,
                'saldo_normal' => $masterCoa->saldo_normal,
                'saldo_awal' => 0,
                'posted_saldo_awal' => false,
            ]);
            $coaCopied++;
        }
    }
    
    echo "  {$coaCopied} COA berhasil di-copy\n\n";
    $totalCoaCopied += $coaCopied;
    
    echo "Copying Satuan...\n";
    $masterSatuans = Satuan::whereNull('user_id')->get();
    
    $satuanCopied = 0;
    foreach ($masterSatuans as $masterSatuan) {
        $exists = Satuan::where('user_id', $user->id)
            ->where('kode', $masterSatuan->kode)
            ->exists();
        
        if (!$exists) {
            Satuan::create([
                'user_id' => $user->id,
                'kode' => $masterSatuan->kode,
                'nama' => $masterSatuan->nama,
                'tipe' => $masterSatuan->tipe,
                'kategori' => $masterSatuan->kategori,
                'is_dasar' => $masterSatuan->is_dasar,
                'is_active' => true,
                'nilai_konversi' => $masterSatuan->nilai_konversi,
                'faktor_ke_dasar' => $masterSatuan->faktor_ke_dasar,
            ]);
            $satuanCopied++;
        }
    }
    
    echo "  {$satuanCopied} Satuan berhasil di-copy\n\n";
    $totalSatuanCopied += $satuanCopied;
}

echo "==============================================\n";
echo "RINGKASAN\n";
echo "==============================================\n\n";
echo "Total user diproses: " . $users->count() . "\n";
echo "Total COA di-copy: {$totalCoaCopied}\n";
echo "Total Satuan di-copy: {$totalSatuanCopied}\n\n";

if ($totalCoaCopied > 0 || $totalSatuanCopied > 0) {
    echo "BERHASIL!\n\n";
    echo "Sekarang setiap user punya data COA dan Satuan sendiri.\n";
    echo "Mereka bisa ubah/hapus/tambah data tanpa mempengaruhi user lain.\n\n";
    echo "LANGKAH SELANJUTNYA:\n";
    echo "1. Hapus data master lama: php hapus_data_master_lama.php\n";
    echo "2. Test di browser\n\n";
} else {
    echo "Tidak ada data yang perlu di-copy.\n\n";
}

echo "SELESAI\n";
