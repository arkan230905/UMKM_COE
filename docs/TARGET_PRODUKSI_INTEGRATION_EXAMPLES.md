# Target Produksi - Integration Examples

## 📚 Panduan Integrasi untuk Developer

File ini berisi contoh-contoh praktis penggunaan Master Target Produksi dalam modul-modul lain.

---

## 🎯 Method 1: Menggunakan Helper Class (RECOMMENDED)

### Import Helper
```php
use App\Helpers\TargetProduksiHelper;
```

### Example 1: Get Target Bulan
```php
// Di controller/service Kualifikasi Tenaga Kerja
$produkId = 1;
$bulan = 7; // Juli
$tahun = 2027;

$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun);
// Returns: 10000 (unit)
```

### Example 2: Calculate Kebutuhan Tenaga Kerja
```php
// Di Master Kualifikasi Tenaga Kerja
$produkId = 1;
$bulan = 7;
$outputPerOrang = 500; // Satu orang bisa produksi 500 unit per bulan

$jumlahTK = TargetProduksiHelper::calculateKebutuhanTenagaKerja(
    $produkId, 
    $bulan, 
    $outputPerOrang
);

// Output: 20 orang (10000 / 500)
echo "Kebutuhan TK untuk bulan Juli: {$jumlahTK} orang";
```

### Example 3: Calculate Estimasi BTKL
```php
// Di Master BTKL
$produkId = 1;
$bulan = 7;
$jamKerjaPerUnit = 2; // 2 jam per unit
$tarifPerJam = 50000; // Rp 50,000 per jam

$estimasiBTKL = TargetProduksiHelper::calculateEstimasiBTKL(
    $produkId,
    $bulan,
    $jamKerjaPerUnit,
    $tarifPerJam
);

// Output: Rp 1,000,000,000 (10000 * 2 * 50000)
echo "Estimasi BTKL: Rp " . number_format($estimasiBTKL, 0, ',', '.');
```

### Example 4: Calculate Tarif BOP
```php
// Di Master BOP Proses
$totalBopBulanan = 50000000; // Rp 50 juta
$produkId = 1;
$bulan = 7;

$tarifBopPerUnit = TargetProduksiHelper::calculateTarifBOP(
    $totalBopBulanan,
    $produkId,
    $bulan
);

// Output: Rp 5,000 per unit (50000000 / 10000)
echo "Tarif BOP per unit: Rp " . number_format($tarifBopPerUnit, 0, ',', '.');
```

### Example 5: Display Target vs Realisasi (Transaksi Produksi)
```php
// Di form Transaksi Produksi
$produkId = request('produk_id');
$tanggalProduksi = request('tanggal_produksi');
$jumlahProduksi = request('jumlah_produksi');

$bulan = date('n', strtotime($tanggalProduksi));
$tahun = date('Y', strtotime($tanggalProduksi));

// Get target
$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun);

// Get realisasi (dari database)
$realisasi = Produksi::where('produk_id', $produkId)
    ->whereYear('tanggal_produksi', $tahun)
    ->whereMonth('tanggal_produksi', $bulan)
    ->sum('jumlah_produksi') + $jumlahProduksi; // Include current input

// Calculate
$selisih = $realisasi - $target;
$persentase = TargetProduksiHelper::getPersentasePencapaian($produkId, $bulan, $realisasi, $tahun);
$status = TargetProduksiHelper::getStatusPencapaian($persentase);

// Display
return [
    'target' => TargetProduksiHelper::formatNumber($target),
    'realisasi' => TargetProduksiHelper::formatNumber($realisasi),
    'selisih' => TargetProduksiHelper::formatNumber($selisih),
    'persentase' => $persentase . '%',
    'status' => $status['label'],
    'status_color' => $status['color'],
];
```

### Example 6: Check Lock Status
```php
// Sebelum allow edit target
$bulan = 7;
$tahun = 2027;

if (!TargetProduksiHelper::isMonthEditable($bulan, $tahun)) {
    return response()->json([
        'error' => 'Periode ini sudah terkunci dan tidak dapat diubah'
    ], 403);
}

// Proceed with update...
```

### Example 7: Get Summary Periode
```php
// Dashboard atau Laporan
$produkId = 1;
$bulanAwal = 1; // Januari
$bulanAkhir = 6; // Juni (Semester 1)
$tahun = 2027;

$summary = TargetProduksiHelper::getSummaryPeriode($produkId, $bulanAwal, $bulanAkhir, $tahun);

/*
Output:
[
    'periode' => 'Januari - Juni 2027',
    'total_target' => 60000,
    'total_realisasi' => 58000,
    'selisih' => -2000,
    'persentase' => 96.67,
    'details' => [
        ['bulan' => 1, 'nama_bulan' => 'Januari', 'target' => 10000, ...],
        ['bulan' => 2, 'nama_bulan' => 'Februari', 'target' => 10000, ...],
        ...
    ]
]
*/

// Display in view
foreach ($summary['details'] as $detail) {
    echo "{$detail['nama_bulan']}: {$detail['realisasi']}/{$detail['target']} ({$detail['persentase']}%)\n";
}
```

---

## 🎯 Method 2: Menggunakan Model Directly

### Example 1: Get Target via Model
```php
use App\Models\TargetProduksi;
use App\Models\TargetProduksiDetail;

// Get target produksi
$target = TargetProduksi::where('user_id', auth()->id())
    ->where('produk_id', 1)
    ->where('tahun', 2027)
    ->first();

if ($target) {
    echo "Total Target Tahunan: {$target->total_target_tahunan}";
    echo "Status: {$target->status}";
    echo "Persentase Pencapaian: {$target->persentase_pencapaian}%";
}
```

### Example 2: Get Target Bulanan via Model
```php
// Get detail target bulan Juli
$detail = TargetProduksiDetail::whereHas('targetProduksi', function($q) {
    $q->where('user_id', auth()->id())
      ->where('produk_id', 1)
      ->where('tahun', 2027);
})
->where('bulan', 7)
->first();

if ($detail) {
    echo "Target Juli: {$detail->target_bulanan}";
    echo "Nama Bulan: {$detail->nama_bulan}";
    echo "Lock Status: {$detail->lock_status}";
    echo "Is Locked: " . ($detail->isLocked() ? 'Yes' : 'No');
}
```

### Example 3: Loop Through All Months
```php
$target = TargetProduksi::with('details')->find($targetId);

foreach ($target->details as $detail) {
    echo "{$detail->nama_bulan}: ";
    echo number_format($detail->target_bulanan, 0, ',', '.');
    echo " ({$detail->lock_status})\n";
}
```

---

## 🎯 Method 3: Menggunakan Service Class

### Example 1: Dashboard Summary
```php
use App\Services\TargetProduksiService;

$service = app(TargetProduksiService::class);
$summary = $service->getDashboardSummary(2027);

return view('dashboard', [
    'total_target' => $summary['total_target'],
    'total_realisasi' => $summary['total_realisasi'],
    'persentase' => $summary['persentase'],
    'jumlah_produk' => $summary['jumlah_produk'],
    'bulan_editable' => $summary['bulan_editable'],
]);
```

### Example 2: Get Comparison Data
```php
$service = app(TargetProduksiService::class);
$target = TargetProduksi::find($id);
$comparison = $service->getComparison($target);

/*
Returns array of months:
[
    [
        'bulan' => 1,
        'nama_bulan' => 'Januari',
        'target' => 10000,
        'realisasi' => 9500,
        'selisih' => -500,
        'persentase' => 95.00,
        'status' => 'Locked'
    ],
    ...
]
*/
```

### Example 3: Generate Auto Target
```php
$service = app(TargetProduksiService::class);

// Merata
$targets = $service->generateAutoTarget(120000, 'merata');

// Persentase
$targets = $service->generateAutoTarget(120000, 'persentase');

// Histori
$targets = $service->generateAutoTarget(120000, 'histori', 2026);

/*
Returns:
[
    ['bulan' => 1, 'target_bulanan' => 10000],
    ['bulan' => 2, 'target_bulanan' => 10000],
    ...
]
*/
```

---

## 💼 Real-World Integration Examples

### Integration 1: Kualifikasi Tenaga Kerja Form

```php
// app/Filament/Resources/KualifikasiResource/Schemas/KualifikasiForm.php

use App\Helpers\TargetProduksiHelper;
use Filament\Forms\Components\Placeholder;

Placeholder::make('kebutuhan_tk_info')
    ->label('Info Kebutuhan Tenaga Kerja')
    ->content(function (Get $get) {
        $produkId = $get('produk_id');
        $targetProdukPerBulan = $get('target_produk_per_bulan');
        
        if (!$produkId || !$targetProdukPerBulan) {
            return 'Pilih produk dan masukkan target produk per bulan';
        }
        
        $bulan = now()->month;
        $tahun = now()->year;
        
        $targetBulanan = TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun);
        
        if ($targetBulanan <= 0) {
            return '⚠️ Belum ada target produksi untuk produk ini';
        }
        
        $kebutuhanTK = ceil($targetBulanan / $targetProdukPerBulan);
        
        return "📊 Target Produksi Bulan Ini: " . 
               TargetProduksiHelper::formatNumber($targetBulanan) . " Unit\n" .
               "👥 Kebutuhan Tenaga Kerja: {$kebutuhanTK} Orang";
    }),
```

### Integration 2: BTKL Calculation

```php
// app/Services/BtklService.php

use App\Helpers\TargetProduksiHelper;

class BtklService
{
    public function calculateEstimasiBTKL(int $produkId, int $bulan, int $tahun)
    {
        // Get BTKL data
        $btkl = Btkl::where('produk_id', $produkId)->first();
        
        if (!$btkl) {
            throw new \Exception('BTKL belum di-setup untuk produk ini');
        }
        
        // Get target produksi
        $targetBulanan = TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun);
        
        if ($targetBulanan <= 0) {
            throw new \Exception('Target produksi belum di-setup');
        }
        
        // Calculate
        $estimasi = TargetProduksiHelper::calculateEstimasiBTKL(
            $produkId,
            $bulan,
            $btkl->jam_kerja_per_unit,
            $btkl->tarif_per_jam,
            $tahun
        );
        
        return [
            'target_produksi' => $targetBulanan,
            'jam_kerja_per_unit' => $btkl->jam_kerja_per_unit,
            'tarif_per_jam' => $btkl->tarif_per_jam,
            'total_jam_kerja' => $targetBulanan * $btkl->jam_kerja_per_unit,
            'estimasi_btkl' => $estimasi,
        ];
    }
}
```

### Integration 3: BOP Proses Widget

```php
// app/Filament/Widgets/BopProsesWidget.php

use App\Helpers\TargetProduksiHelper;
use Filament\Widgets\Widget;

class BopProsesWidget extends Widget
{
    protected static string $view = 'filament.widgets.bop-proses-widget';
    
    public function getData(): array
    {
        $produkId = 1; // Or from filter
        $bulan = now()->month;
        $tahun = now()->year;
        
        $targetBulanan = TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun);
        
        if ($targetBulanan <= 0) {
            return [
                'error' => 'Target produksi belum di-setup',
            ];
        }
        
        $bopProses = BopProses::where('produk_id', $produkId)->first();
        $totalBop = $bopProses->total_bop_per_bulan ?? 0;
        
        $tarifBopPerUnit = TargetProduksiHelper::calculateTarifBOP(
            $totalBop,
            $produkId,
            $bulan,
            $tahun
        );
        
        return [
            'target_produksi' => TargetProduksiHelper::formatNumber($targetBulanan),
            'total_bop' => 'Rp ' . TargetProduksiHelper::formatNumber($totalBop),
            'tarif_per_unit' => 'Rp ' . TargetProduksiHelper::formatNumber($tarifBopPerUnit),
        ];
    }
}
```

### Integration 4: Transaksi Produksi Validation

```php
// app/Filament/Resources/ProduksiResource/Pages/CreateProduksi.php

use App\Helpers\TargetProduksiHelper;
use Filament\Notifications\Notification;

protected function mutateFormDataBeforeCreate(array $data): array
{
    $produkId = $data['produk_id'];
    $tanggalProduksi = $data['tanggal_produksi'];
    $jumlahProduksi = $data['jumlah_produksi'];
    
    $bulan = date('n', strtotime($tanggalProduksi));
    $tahun = date('Y', strtotime($tanggalProduksi));
    
    // Get target
    $target = TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun);
    
    // Get current realisasi
    $realisasiSebelumnya = Produksi::where('produk_id', $produkId)
        ->whereYear('tanggal_produksi', $tahun)
        ->whereMonth('tanggal_produksi', $bulan)
        ->sum('jumlah_produksi');
    
    $realisasiTotal = $realisasiSebelumnya + $jumlahProduksi;
    
    // Warning jika over target
    if ($target > 0 && $realisasiTotal > $target) {
        Notification::make()
            ->title('Peringatan')
            ->body("Realisasi ({$realisasiTotal}) melebihi target ({$target}). Lanjutkan dengan hati-hati.")
            ->warning()
            ->persistent()
            ->send();
    }
    
    // Info display
    if ($target > 0) {
        $persentase = TargetProduksiHelper::getPersentasePencapaian($produkId, $bulan, $realisasiTotal, $tahun);
        $status = TargetProduksiHelper::getStatusPencapaian($persentase);
        
        Notification::make()
            ->title('Info Target Produksi')
            ->body("Target: {$target} | Realisasi: {$realisasiTotal} | {$status['label']} ({$persentase}%)")
            ->color($status['color'])
            ->send();
    }
    
    return $data;
}
```

### Integration 5: Penggajian Validation

```php
// app/Services/PenggajianService.php

use App\Helpers\TargetProduksiHelper;

class PenggajianService
{
    public function validatePenggajian(int $bulan, int $tahun, array $pegawais)
    {
        $warnings = [];
        
        // Get all targets for this month
        $targets = TargetProduksiHelper::getAllTargetsBulan($bulan, $tahun);
        
        foreach ($targets as $target) {
            $produkId = $target['produk_id'];
            $targetProduksi = $target['target'];
            
            // Get kualifikasi
            $kualifikasi = Kualifikasi::whereHas('produk', function($q) use ($produkId) {
                $q->where('id', $produkId);
            })->first();
            
            if (!$kualifikasi) {
                continue;
            }
            
            // Calculate kebutuhan TK
            $kebutuhanTK = TargetProduksiHelper::calculateKebutuhanTenagaKerja(
                $produkId,
                $bulan,
                $kualifikasi->target_produk_per_bulan,
                $tahun
            );
            
            // Count actual pegawai
            $actualTK = count($pegawais); // Simplifikasi
            
            if ($actualTK < $kebutuhanTK) {
                $warnings[] = [
                    'produk' => $target['produk_nama'],
                    'type' => 'understaffed',
                    'message' => "Kekurangan TK: Butuh {$kebutuhanTK}, ada {$actualTK}",
                ];
            } elseif ($actualTK > $kebutuhanTK * 1.2) { // Over 20%
                $warnings[] = [
                    'produk' => $target['produk_nama'],
                    'type' => 'overstaffed',
                    'message' => "Kelebihan TK: Butuh {$kebutuhanTK}, ada {$actualTK}",
                ];
            }
        }
        
        return $warnings;
    }
}
```

---

## 📊 Blade View Examples

### Example 1: Display Target Info Card
```blade
<!-- resources/views/components/target-info-card.blade.php -->

@php
use App\Helpers\TargetProduksiHelper;

$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun);
$realisasi = $realisasiProduksi; // From controller
$persentase = TargetProduksiHelper::getPersentasePencapaian($produkId, $bulan, $realisasi, $tahun);
$status = TargetProduksiHelper::getStatusPencapaian($persentase);
@endphp

<div class="p-4 rounded-lg border" style="border-color: {{ $status['color'] }}">
    <h3 class="text-lg font-semibold">Target Produksi {{ TargetProduksiHelper::getNamaBulan($bulan) }}</h3>
    
    <div class="mt-2">
        <div class="flex justify-between">
            <span>Target:</span>
            <strong>{{ TargetProduksiHelper::formatNumber($target) }} Unit</strong>
        </div>
        <div class="flex justify-between">
            <span>Realisasi:</span>
            <strong>{{ TargetProduksiHelper::formatNumber($realisasi) }} Unit</strong>
        </div>
        <div class="flex justify-between">
            <span>Status:</span>
            <span class="badge badge-{{ $status['color'] }}">{{ $status['label'] }}</span>
        </div>
    </div>
    
    <div class="mt-3">
        <div class="progress">
            <div class="progress-bar bg-{{ $status['color'] }}" style="width: {{ min($persentase, 100) }}%">
                {{ number_format($persentase, 1) }}%
            </div>
        </div>
    </div>
</div>
```

### Example 2: Monthly Progress Table
```blade
<!-- resources/views/reports/monthly-progress.blade.php -->

@php
use App\Helpers\TargetProduksiHelper;

$produkId = 1;
$tahun = 2027;
$summary = TargetProduksiHelper::getSummaryPeriode($produkId, 1, 12, $tahun);
@endphp

<table class="table">
    <thead>
        <tr>
            <th>Bulan</th>
            <th>Target</th>
            <th>Realisasi</th>
            <th>Selisih</th>
            <th>%</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summary['details'] as $detail)
        @php
            $status = TargetProduksiHelper::getStatusPencapaian($detail['persentase']);
        @endphp
        <tr>
            <td>{{ $detail['nama_bulan'] }}</td>
            <td>{{ TargetProduksiHelper::formatNumber($detail['target']) }}</td>
            <td>{{ TargetProduksiHelper::formatNumber($detail['realisasi']) }}</td>
            <td class="{{ $detail['selisih'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ TargetProduksiHelper::formatNumber($detail['selisih']) }}
            </td>
            <td>{{ number_format($detail['persentase'], 1) }}%</td>
            <td>
                <span class="badge badge-{{ $status['color'] }}">
                    {{ $status['label'] }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="font-bold">
            <td>TOTAL</td>
            <td>{{ TargetProduksiHelper::formatNumber($summary['total_target']) }}</td>
            <td>{{ TargetProduksiHelper::formatNumber($summary['total_realisasi']) }}</td>
            <td class="{{ $summary['selisih'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ TargetProduksiHelper::formatNumber($summary['selisih']) }}
            </td>
            <td>{{ number_format($summary['persentase'], 1) }}%</td>
            <td></td>
        </tr>
    </tfoot>
</table>
```

---

## 🔍 Testing Examples

### Unit Test Example
```php
// tests/Unit/TargetProduksiHelperTest.php

use Tests\TestCase;
use App\Helpers\TargetProduksiHelper;
use App\Models\User;
use App\Models\Produk;
use App\Models\TargetProduksi;
use App\Models\TargetProduksiDetail;

class TargetProduksiHelperTest extends TestCase
{
    public function test_get_target_bulan()
    {
        $user = User::factory()->create();
        $produk = Produk::factory()->create(['user_id' => $user->id]);
        
        $target = TargetProduksi::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'tahun' => 2027,
            'total_target_tahunan' => 120000,
        ]);
        
        TargetProduksiDetail::create([
            'target_produksi_id' => $target->id,
            'bulan' => 7,
            'target_bulanan' => 10000,
        ]);
        
        $this->actingAs($user);
        
        $result = TargetProduksiHelper::getTargetBulan($produk->id, 7, 2027);
        
        $this->assertEquals(10000, $result);
    }
    
    public function test_calculate_kebutuhan_tenaga_kerja()
    {
        // Similar setup...
        
        $result = TargetProduksiHelper::calculateKebutuhanTenagaKerja(
            $produk->id,
            7,
            500, // output per orang
            2027
        );
        
        $this->assertEquals(20, $result); // 10000 / 500 = 20
    }
}
```

---

## 📝 Best Practices

### 1. Always Check Target Exists
```php
$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan);

if ($target <= 0) {
    // Handle: target belum di-setup
    return back()->with('error', 'Target produksi belum di-setup');
}

// Proceed...
```

### 2. Cache untuk Performance (Optional)
```php
use Illuminate\Support\Facades\Cache;

$target = Cache::remember("target_{$produkId}_{$bulan}_{$tahun}", 3600, function() use ($produkId, $bulan, $tahun) {
    return TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun);
});
```

### 3. Handle Multi-Produk dengan Loop
```php
$produkIds = [1, 2, 3];
$targets = [];

foreach ($produkIds as $produkId) {
    $targets[$produkId] = TargetProduksiHelper::getTargetBulan($produkId, $bulan);
}
```

### 4. Error Handling
```php
try {
    $summary = TargetProduksiHelper::getSummaryPeriode($produkId, 1, 12);
} catch (\Exception $e) {
    Log::error('Error getting target summary: ' . $e->getMessage());
    return response()->json(['error' => 'Gagal mengambil data target'], 500);
}
```

---

## 🎓 Conclusion

Helper class **TargetProduksiHelper** menyediakan interface yang clean dan mudah untuk integrasi dengan modul lain. Gunakan method yang sesuai dengan kebutuhan Anda.

**Recommended Approach:**
1. Gunakan **Helper Class** untuk kemudahan dan konsistensi
2. Gunakan **Model** untuk query kompleks
3. Gunakan **Service Class** untuk business logic yang lebih advanced

**Happy Coding! 🚀**

---

**Version:** 1.0.0  
**Last Updated:** {{ now()->format('d F Y') }}
