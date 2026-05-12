<?php
// Simple debug untuk tunjangan issue

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Pegawai;
use App\Models\Jabatan;

echo "=== SIMPLE TUNJANGAN DEBUG ===\n\n";

// Login as User 13
$user = User::find(13);
if (!$user) {
    echo "❌ User 13 not found!\n";
    exit;
}

auth()->login($user);
echo "✓ Logged in as: {$user->name} (ID: {$user->id})\n";
echo "✓ Email: {$user->email}\n\n";

// Check pegawai
echo "CHECKING PEGAWAI DATA:\n";
echo "─────────────────────────────────────────\n";

$pegawai = Pegawai::find(3);
if (!$pegawai) {
    echo "❌ Pegawai ID 3 not found!\n";
    exit;
}

echo "Pegawai: {$pegawai->nama} (ID: {$pegawai->id})\n";
echo "  ├─ User ID: {$pegawai->user_id}\n";
echo "  ├─ Jabatan ID: {$pegawai->jabatan_id}\n";
echo "  └─ Perusahaan ID: {$pegawai->perusahaan_id}\n\n";

// Check jabatan
echo "CHECKING JABATAN DATA:\n";
echo "─────────────────────────────────────────\n";

if (!$pegawai->jabatan_id) {
    echo "❌ Pegawai has NO jabatan_id!\n";
    exit;
}

$jabatan = Jabatan::find($pegawai->jabatan_id);
if (!$jabatan) {
    echo "❌ Jabatan ID {$pegawai->jabatan_id} not found!\n";
    exit;
}

echo "Jabatan: {$jabatan->nama} (ID: {$jabatan->id})\n";
echo "  ├─ User ID: {$jabatan->user_id}\n";
echo "  ├─ Gaji Pokok: " . number_format($jabatan->gaji_pokok, 0, ',', '.') . "\n";
echo "  ├─ Tunjangan Jabatan: " . number_format($jabatan->tunjangan, 0, ',', '.') . "\n";
echo "  ├─ Tunjangan Transport: " . number_format($jabatan->tunjangan_transport, 0, ',', '.') . "\n";
echo "  ├─ Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi, 0, ',', '.') . "\n";
echo "  └─ Asuransi: " . number_format($jabatan->asuransi, 0, ',', '.') . "\n\n";

// Check if values are 0
if ($jabatan->tunjangan_transport == 0 && $jabatan->tunjangan_konsumsi == 0) {
    echo "⚠️  PROBLEM FOUND: Tunjangan values are 0 in database!\n";
    echo "   This is why UI shows 0 - data is not set in database\n";
    echo "   Need to update database with correct values\n\n";
    
    echo "FIX: Update database with correct values:\n";
    echo "php artisan tinker\n";
    echo ">>> \$jabatan = App\\Models\\Jabatan::find({$jabatan->id});\n";
    echo ">>> \$jabatan->update(['tunjangan_transport' => 150000, 'tunjangan_konsumsi' => 375000]);\n";
    exit;
}

echo "\n";

// Test API endpoint
echo "TESTING API ENDPOINT:\n";
echo "─────────────────────────────────────────\n";

try {
    $controller = new \App\Http\Controllers\PenggajianController();
    $response = $controller->getEmployeeData(3);
    $data = json_decode($response->getContent(), true);
    
    echo "API Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    // Check values
    if ($data['tunjangan_transport'] == 0 && $data['tunjangan_konsumsi'] == 0) {
        echo "⚠️  API returns 0 for tunjangan\n";
        echo "   This confirms database data is 0\n";
    } else {
        echo "✓ API returns correct tunjangan values\n";
    }
} catch (\Exception $e) {
    echo "❌ Error calling API: {$e->getMessage()}\n";
}

echo "\n";

// Check with eager loading
echo "CHECKING EAGER LOADING:\n";
echo "─────────────────────────────────────────\n";

$pegawaiWithJabatan = Pegawai::with('jabatanRelasi')->find(3);
echo "Pegawai with jabatanRelasi:\n";
echo "  ├─ Jabatan Loaded: " . ($pegawaiWithJabatan->relationLoaded('jabatanRelasi') ? 'YES' : 'NO') . "\n";

if ($pegawaiWithJabatan->jabatanRelasi) {
    echo "  ├─ Jabatan Name: {$pegawaiWithJabatan->jabatanRelasi->nama}\n";
    echo "  ├─ Tunjangan Transport: " . number_format($pegawaiWithJabatan->jabatanRelasi->tunjangan_transport, 0, ',', '.') . "\n";
    echo "  └─ Tunjangan Konsumsi: " . number_format($pegawaiWithJabatan->jabatanRelasi->tunjangan_konsumsi, 0, ',', '.') . "\n";
} else {
    echo "  └─ ❌ Jabatan NOT loaded (NULL)\n";
}

echo "\n=== END DEBUG ===\n";
