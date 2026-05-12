<?php
// Comprehensive debug untuk tunjangan issue

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Pegawai;
use App\Models\Jabatan;
use Illuminate\Support\Facades\Route;

echo "=== COMPREHENSIVE TUNJANGAN DEBUG ===\n\n";

// LANGKAH 1: Check routes
echo "LANGKAH 1: CHECK ROUTES\n";
echo "─────────────────────────────────────────\n";

$routes = Route::getRoutes();
$pegawaiRoutes = [];

foreach ($routes as $route) {
    if (strpos($route->uri, 'pegawai') !== false && strpos($route->uri, 'data') !== false) {
        $pegawaiRoutes[] = [
            'method' => implode('|', $route->methods),
            'uri' => $route->uri,
            'name' => $route->name,
            'controller' => $route->getActionName(),
        ];
    }
}

if (empty($pegawaiRoutes)) {
    echo "❌ NO ROUTES FOUND FOR PEGAWAI DATA!\n";
} else {
    echo "✓ Found " . count($pegawaiRoutes) . " route(s):\n";
    foreach ($pegawaiRoutes as $route) {
        echo "  ├─ {$route['method']} {$route['uri']}\n";
        echo "  │  ├─ Name: {$route['name']}\n";
        echo "  │  └─ Controller: {$route['controller']}\n";
    }
}

echo "\n";

// LANGKAH 2: Check data di database
echo "LANGKAH 2: CHECK DATA DI DATABASE\n";
echo "─────────────────────────────────────────\n";

// Login as User 13
$user = User::find(13);
if (!$user) {
    echo "❌ User 13 not found!\n";
    exit;
}

auth()->login($user);
echo "✓ Logged in as: {$user->name} (ID: {$user->id})\n\n";

// Check pegawai
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
if ($pegawai->jabatan_id) {
    $jabatan = Jabatan::find($pegawai->jabatan_id);
    if ($jabatan) {
        echo "Jabatan: {$jabatan->nama} (ID: {$jabatan->id})\n";
        echo "  ├─ User ID: {$jabatan->user_id}\n";
        echo "  ├─ Gaji Pokok: " . number_format($jabatan->gaji_pokok, 0, ',', '.') . "\n";
        echo "  ├─ Tunjangan Jabatan: " . number_format($jabatan->tunjangan, 0, ',', '.') . "\n";
        echo "  ├─ Tunjangan Transport: " . number_format($jabatan->tunjangan_transport, 0, ',', '.') . "\n";
        echo "  ├─ Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi, 0, ',', '.') . "\n";
        echo "  └─ Asuransi: " . number_format($jabatan->asuransi, 0, ',', '.') . "\n\n";
        
        // Check if values are 0
        if ($jabatan->tunjangan_transport == 0 && $jabatan->tunjangan_konsumsi == 0) {
            echo "⚠️  WARNING: Tunjangan values are 0 in database!\n";
            echo "   This is the root cause - data is not set in database\n\n";
        }
    } else {
        echo "❌ Jabatan ID {$pegawai->jabatan_id} not found!\n";
    }
} else {
    echo "❌ Pegawai has no jabatan_id!\n";
}

echo "\n";

// LANGKAH 3: Test API endpoint
echo "LANGKAH 3: TEST API ENDPOINT\n";
echo "─────────────────────────────────────────\n";

try {
    $controller = new \App\Http\Controllers\PenggajianController();
    $response = $controller->getEmployeeData(3);
    $data = json_decode($response->getContent(), true);
    
    echo "API Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    // Check values
    if ($data['tunjangan_transport'] == 0 && $data['tunjangan_konsumsi'] == 0) {
        echo "⚠️  WARNING: API returns 0 for tunjangan!\n";
        echo "   This means either:\n";
        echo "   1. Database data is 0\n";
        echo "   2. Jabatan is not loaded (NULL)\n";
        echo "   3. Fallback to pegawai stored values (which are 0)\n\n";
    } else {
        echo "✓ API returns correct tunjangan values\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Error calling API: {$e->getMessage()}\n\n";
}

// LANGKAH 4: Check with eager loading
echo "LANGKAH 4: CHECK WITH EAGER LOADING\n";
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

echo "\n";

// LANGKAH 5: Summary
echo "LANGKAH 5: SUMMARY & DIAGNOSIS\n";
echo "─────────────────────────────────────────\n";

$issues = [];

// Check 1: Route exists
if (empty($pegawaiRoutes)) {
    $issues[] = "❌ Route /transaksi/penggajian/pegawai/{id}/data not found";
}

// Check 2: Pegawai exists
if (!$pegawai) {
    $issues[] = "❌ Pegawai ID 3 not found";
}

// Check 3: Jabatan exists
if ($pegawai && !$pegawai->jabatan_id) {
    $issues[] = "❌ Pegawai has no jabatan_id";
} elseif ($pegawai && $pegawai->jabatan_id) {
    $jabatan = Jabatan::find($pegawai->jabatan_id);
    if (!$jabatan) {
        $issues[] = "❌ Jabatan ID {$pegawai->jabatan_id} not found";
    } elseif ($jabatan->tunjangan_transport == 0 && $jabatan->tunjangan_konsumsi == 0) {
        $issues[] = "⚠️  Tunjangan values are 0 in database (need to update)";
    }
}

// Check 4: API response
if ($data && $data['tunjangan_transport'] == 0 && $data['tunjangan_konsumsi'] == 0) {
    $issues[] = "⚠️  API returns 0 for tunjangan";
}

if (empty($issues)) {
    echo "✓ All checks passed! No issues found.\n";
    echo "✓ Tunjangan values should appear in UI.\n";
    echo "✓ If still showing 0 in UI, it's a JavaScript/frontend issue.\n";
} else {
    echo "Issues found:\n";
    foreach ($issues as $issue) {
        echo "  $issue\n";
    }
}

echo "\n=== END DEBUG ===\n";
