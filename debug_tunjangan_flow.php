<?php
// Debug tunjangan flow: Controller → Blade → JavaScript

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Pegawai;
use App\Models\Jabatan;

echo "=== DEBUG TUNJANGAN FLOW ===\n\n";

// Login as User 13
$user = User::find(13);
auth()->login($user);

echo "Logged in as: {$user->name} (ID: {$user->id})\n\n";

// STEP 1: Check controller data
echo "STEP 1: CHECK CONTROLLER DATA\n";
echo "─────────────────────────────────────────\n";

$pegawais = Pegawai::with('jabatanRelasi')
    ->where('user_id', auth()->id())
    ->get();

echo "Pegawai count: {$pegawais->count()}\n\n";

foreach ($pegawais as $pegawai) {
    echo "Pegawai: {$pegawai->nama} (ID: {$pegawai->id})\n";
    
    // Check jabatan relation
    $jabatan = $pegawai->jabatanRelasi;
    
    if (!$jabatan) {
        echo "  ❌ jabatanRelasi is NULL!\n";
        continue;
    }
    
    echo "  ✓ jabatanRelasi loaded: {$jabatan->nama}\n";
    echo "  ├─ tunjangan_transport: {$jabatan->tunjangan_transport}\n";
    echo "  ├─ tunjangan_konsumsi: {$jabatan->tunjangan_konsumsi}\n";
    
    // STEP 2: Check blade rendering
    echo "\nSTEP 2: CHECK BLADE RENDERING\n";
    echo "─────────────────────────────────────────\n";
    
    // Simulate blade logic
    $gajiPokok = $jabatan ? ($jabatan->gaji_pokok ?? 0) : ($pegawai->gaji_pokok ?? 0);
    $tarif = $jabatan ? ($jabatan->tarif_per_jam ?? 0) : ($pegawai->tarif_per_jam ?? 0);
    $tunjanganJabatan = $jabatan ? ($jabatan->tunjangan ?? 0) : ($pegawai->tunjangan ?? 0);
    $tunjanganTransport = $jabatan ? ($jabatan->tunjangan_transport ?? 0) : ($pegawai->tunjangan_transport ?? 0);
    $tunjanganKonsumsi = $jabatan ? ($jabatan->tunjangan_konsumsi ?? 0) : ($pegawai->tunjangan_konsumsi ?? 0);
    $asuransi = $jabatan ? ($jabatan->asuransi ?? 0) : ($pegawai->asuransi ?? 0);
    
    echo "After blade logic:\n";
    echo "  ├─ tunjanganTransport: {$tunjanganTransport}\n";
    echo "  ├─ tunjanganKonsumsi: {$tunjanganKonsumsi}\n";
    
    // STEP 3: Check HTML data attributes
    echo "\nSTEP 3: CHECK HTML DATA ATTRIBUTES\n";
    echo "─────────────────────────────────────────\n";
    
    $html = "<option value=\"{$pegawai->id}\"\n";
    $html .= "        data-jenis=\"" . strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl') . "\"\n";
    $html .= "        data-gaji-pokok=\"{$gajiPokok}\"\n";
    $html .= "        data-tarif=\"{$tarif}\"\n";
    $html .= "        data-tunjangan-jabatan=\"{$tunjanganJabatan}\"\n";
    $html .= "        data-tunjangan-transport=\"{$tunjanganTransport}\"\n";
    $html .= "        data-tunjangan-konsumsi=\"{$tunjanganKonsumsi}\"\n";
    $html .= "        data-asuransi=\"{$asuransi}\">\n";
    $html .= "    {$pegawai->nama}\n";
    $html .= "</option>";
    
    echo "Generated HTML:\n";
    echo $html . "\n";
    
    // STEP 4: Check JavaScript parsing
    echo "\nSTEP 4: CHECK JAVASCRIPT PARSING\n";
    echo "─────────────────────────────────────────\n";
    
    // Simulate JavaScript reading
    $jsData = [
        'jenis' => strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl'),
        'gajiPokok' => (float)$gajiPokok,
        'tarif' => (float)$tarif,
        'tunjanganJabatan' => (float)$tunjanganJabatan,
        'tunjanganTransport' => (float)$tunjanganTransport,
        'tunjanganKonsumsi' => (float)$tunjanganKonsumsi,
        'asuransi' => (float)$asuransi,
    ];
    
    echo "JavaScript would read:\n";
    echo json_encode($jsData, JSON_PRETTY_PRINT) . "\n";
    
    // STEP 5: Check if values are correct
    echo "\nSTEP 5: VERIFICATION\n";
    echo "─────────────────────────────────────────\n";
    
    if ($tunjanganTransport == 0 && $tunjanganKonsumsi == 0) {
        echo "❌ PROBLEM: Tunjangan values are 0!\n";
        echo "   Possible causes:\n";
        echo "   1. jabatanRelasi is NULL\n";
        echo "   2. Blade logic is wrong\n";
        echo "   3. Database values are 0\n";
    } else {
        echo "✓ Tunjangan values are correct\n";
        echo "  ├─ Transport: {$tunjanganTransport}\n";
        echo "  └─ Konsumsi: {$tunjanganKonsumsi}\n";
    }
    
    echo "\n";
}

echo "=== END DEBUG ===\n";
