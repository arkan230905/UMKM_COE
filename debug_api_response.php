<?php
// Debug API response untuk User 13

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Pegawai;
use App\Models\Jabatan;

echo "=== DEBUG API RESPONSE FOR USER 13 ===\n\n";

// Login as User 13
$user = User::find(13);
if (!$user) {
    echo "User 13 not found!\n";
    exit;
}

auth()->login($user);

echo "Logged in as: {$user->name} (ID: {$user->id})\n";
echo "Email: {$user->email}\n";
echo "Perusahaan ID: {$user->perusahaan_id}\n\n";

// Get pegawai for this user
$pegawais = Pegawai::all(['id', 'nama', 'jabatan_id']);

echo "Pegawai count: {$pegawais->count()}\n";
foreach ($pegawais as $pegawai) {
    echo "\nPegawai: {$pegawai->nama} (ID: {$pegawai->id})\n";
    echo "  Jabatan ID: {$pegawai->jabatan_id}\n";
    
    // Simulate API call
    try {
        $pegawaiWithJabatan = Pegawai::with('jabatanRelasi')->findOrFail($pegawai->id);
        
        echo "  Jabatan Loaded: " . ($pegawaiWithJabatan->jabatanRelasi ? 'YES' : 'NO') . "\n";
        
        if ($pegawaiWithJabatan->jabatanRelasi) {
            $jabatan = $pegawaiWithJabatan->jabatanRelasi;
            echo "  Jabatan Name: {$jabatan->nama}\n";
            echo "  Tunjangan Transport: {$jabatan->tunjangan_transport}\n";
            echo "  Tunjangan Konsumsi: {$jabatan->tunjangan_konsumsi}\n";
            
            // Simulate API response
            $apiResponse = [
                'jenis' => strtolower($pegawaiWithJabatan->jenis_pegawai ?? $pegawaiWithJabatan->kategori ?? 'btktl'),
                'gaji_pokok' => $jabatan->gaji_pokok ?? 0,
                'tarif' => $jabatan->tarif_per_jam ?? 0,
                'tunjangan_jabatan' => $jabatan->tunjangan ?? 0,
                'tunjangan_transport' => $jabatan->tunjangan_transport ?? 0,
                'tunjangan_konsumsi' => $jabatan->tunjangan_konsumsi ?? 0,
                'total_tunjangan' => ($jabatan->tunjangan ?? 0) + ($jabatan->tunjangan_transport ?? 0) + ($jabatan->tunjangan_konsumsi ?? 0),
                'asuransi' => $jabatan->asuransi ?? 0,
                'nama' => $pegawaiWithJabatan->nama,
                'jabatan_nama' => $pegawaiWithJabatan->jabatan_nama ?? 'Staff'
            ];
            
            echo "\n  API Response:\n";
            echo "  " . json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        } else {
            echo "  ERROR: Jabatan not loaded!\n";
        }
    } catch (\Exception $e) {
        echo "  ERROR: {$e->getMessage()}\n";
    }
}

echo "\n=== END DEBUG ===\n";
