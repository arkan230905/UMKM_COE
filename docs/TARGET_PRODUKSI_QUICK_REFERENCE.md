# 🚀 Target Produksi - Quick Reference Card

## 📌 Cheat Sheet untuk Developer

---

## 🔑 Key Concepts

| Concept | Description |
|---------|-------------|
| **Header** | target_produksi - Data tahunan per produk |
| **Detail** | target_produksi_detail - 12 bulan |
| **Lock Period** | Bulan lewat & berjalan = LOCKED |
| **Uniqueness** | 1 produk = 1 target per tahun per user |
| **Validation** | ∑ bulanan MUST = tahunan |

---

## 📊 Database Quick View

```
target_produksi
├── id
├── user_id (FK) → users
├── tahun (YEAR)
├── produk_id (FK) → produks
├── total_target_tahunan
├── created_by (FK) → users
└── timestamps + soft_delete

target_produksi_detail
├── id
├── target_produksi_id (FK)
├── bulan (1-12)
├── target_bulanan
└── timestamps

target_produksi_log
├── id
├── target_produksi_id (FK)
├── user_id (FK)
├── action (created/updated/deleted)
├── old_data (JSON)
├── new_data (JSON)
├── description
└── created_at
```

---

## 🎯 Helper Methods - Quick Reference

```php
use App\Helpers\TargetProduksiHelper;

// Get target bulan
$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun);

// Get target tahunan
$total = TargetProduksiHelper::getTargetTahunan($produkId, $tahun);

// Calculate kebutuhan TK
$jumlahTK = TargetProduksiHelper::calculateKebutuhanTenagaKerja($produkId, $bulan, $outputPerOrang);

// Calculate estimasi BTKL
$btkl = TargetProduksiHelper::calculateEstimasiBTKL($produkId, $bulan, $jamPerUnit, $tarifPerJam);

// Calculate tarif BOP
$tarifBOP = TargetProduksiHelper::calculateTarifBOP($totalBOP, $produkId, $bulan);

// Get persentase pencapaian
$persentase = TargetProduksiHelper::getPersentasePencapaian($produkId, $bulan, $realisasi);

// Get status pencapaian
$status = TargetProduksiHelper::getStatusPencapaian($persentase);
// Returns: ['status' => 'achieved', 'color' => 'success', 'label' => 'Tercapai']

// Check editable
$editable = TargetProduksiHelper::isMonthEditable($bulan, $tahun);

// Get nama bulan
$nama = TargetProduksiHelper::getNamaBulan(7); // "Juli"

// Has target?
$has = TargetProduksiHelper::hasTarget($produkId, $tahun);

// Get summary periode
$summary = TargetProduksiHelper::getSummaryPeriode($produkId, 1, 6, 2027);

// Format number
$formatted = TargetProduksiHelper::formatNumber(10000); // "10.000"
```

---

## 🔧 Service Methods - Quick Reference

```php
use App\Services\TargetProduksiService;

$service = app(TargetProduksiService::class);

// Create
$target = $service->create($data);

// Update
$target = $service->update($target, $data);

// Generate auto target
$targets = $service->generateAutoTarget($total, 'merata');
$targets = $service->generateAutoTarget($total, 'persentase');
$targets = $service->generateAutoTarget($total, 'histori', $previousYear);

// Get realisasi bulanan
$realisasi = $service->getRealisasiBulanan($produkId, $tahun);

// Get comparison
$comparison = $service->getComparison($target);

// Get dashboard summary
$summary = $service->getDashboardSummary($tahun);

// Can delete?
$validation = $service->canDelete($target);

// Check unique
$unique = $service->isUnique($produkId, $tahun, $excludeId);
```

---

## 🏗️ Model Attributes - Quick Reference

```php
// TargetProduksi attributes
$target->status                    // "Belum Dimulai", "Aktif", "Selesai"
$target->status_color              // "info", "success", "gray"
$target->total_realisasi           // Sum dari produksi
$target->persentase_pencapaian     // 0-100+
$target->selisih                   // realisasi - target

// TargetProduksiDetail attributes
$detail->nama_bulan                // "Januari" - "Desember"
$detail->lock_status               // "Locked", "Editable"
$detail->realisasi                 // Sum produksi bulan ini
$detail->persentase                // 0-100+
$detail->selisih                   // realisasi - target

// Methods
$target->getTargetBulan($bulan)    // Get target bulan tertentu
$target->hasProductions()          // Boolean
$target->canBeDeleted()            // Boolean
$detail->isLocked()                // Boolean
```

---

## 🎨 Filament Form Components

```php
// Select tahun
Select::make('tahun')
    ->options(self::getYearOptions())
    ->required()
    ->reactive();

// Select produk
Select::make('produk_id')
    ->searchable()
    ->preload()
    ->options(fn() => Produk::pluck('nama_produk', 'id'))
    ->required()
    ->reactive();

// Repeater 12 bulan
Repeater::make('details')
    ->relationship('details')
    ->schema([...])
    ->defaultItems(12)
    ->minItems(12)
    ->maxItems(12)
    ->addable(false)
    ->deletable(false);

// Lock checking
->disabled(fn(?TargetProduksiDetail $record) => 
    $record ? $record->isLocked() : false
)
```

---

## 🔒 Lock Period Logic

```php
// Current: Juli 2027

Jan  → LOCKED (tahun = berjalan, bulan < berjalan)
Feb  → LOCKED
Mar  → LOCKED
Apr  → LOCKED
May  → LOCKED
Jun  → LOCKED
Jul  → LOCKED (bulan = berjalan)
Aug  → EDITABLE (bulan > berjalan)
Sep  → EDITABLE
Oct  → EDITABLE
Nov  → EDITABLE
Dec  → EDITABLE

// Logic
if (target_year < current_year) ALL LOCKED
if (target_year > current_year) ALL EDITABLE
if (target_year == current_year) {
    if (target_month <= current_month) LOCKED
    else EDITABLE
}
```

---

## 🎯 Common Use Cases

### UC1: Display Target di Form Produksi
```php
$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan);
Placeholder::make('target_info')
    ->content("Target bulan ini: " . number_format($target));
```

### UC2: Validation Over Target
```php
$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan);
$realisasi = $currentRealisasi + $input;

if ($realisasi > $target) {
    Notification::make()
        ->warning()
        ->title('Melebihi Target')
        ->send();
}
```

### UC3: Calculate Kebutuhan TK
```php
$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan);
$kebutuhanTK = ceil($target / $outputPerOrang);
```

### UC4: Dashboard Widget
```php
$summary = app(TargetProduksiService::class)->getDashboardSummary(2027);
return view('widget', $summary);
```

### UC5: Check Before Edit
```php
if (!TargetProduksiHelper::isMonthEditable($bulan, $tahun)) {
    abort(403, 'Periode terkunci');
}
```

---

## 📊 Status & Colors

```php
// Persentase → Status → Color
>= 100%  → 'achieved'  → 'success'  → Tercapai
>= 80%   → 'good'      → 'info'     → Baik
>= 60%   → 'warning'   → 'warning'  → Perlu Perhatian
< 60%    → 'critical'  → 'danger'   → Kritis
```

---

## 🎓 Integration Pattern

```php
// Pattern 1: Simple Get
$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan);

// Pattern 2: With Validation
$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan);
if ($target <= 0) {
    throw new Exception('Target belum di-setup');
}

// Pattern 3: With Cache
$target = Cache::remember("target_{$produkId}_{$bulan}_{$tahun}", 3600, 
    fn() => TargetProduksiHelper::getTargetBulan($produkId, $bulan, $tahun)
);

// Pattern 4: Batch Processing
$targets = [];
foreach ($produkIds as $produkId) {
    $targets[$produkId] = TargetProduksiHelper::getTargetBulan($produkId, $bulan);
}

// Pattern 5: Full Summary
$summary = TargetProduksiHelper::getSummaryPeriode($produkId, 1, 12, $tahun);
```

---

## 🚨 Common Errors & Solutions

| Error | Solution |
|-------|----------|
| Total tidak sesuai | ∑ bulanan harus = tahunan |
| Tidak bisa edit | Check lock period |
| Tidak bisa hapus | Sudah ada transaksi produksi |
| Duplikat error | 1 produk 1 target per tahun |
| Target = 0 | Target belum di-setup |

---

## 📝 Validation Rules Summary

```php
✓ Tahun: required
✓ Produk: required
✓ Total Target: required, numeric, min:1
✓ Uniqueness: (user_id, produk_id, tahun)
✓ Target Bulanan: required, numeric, min:0
✓ ∑ Bulanan = Tahunan
✓ 12 Bulan: wajib semua diisi
✓ Lock Period: enforced
✓ Delete: no production transactions
```

---

## 🔗 URLs & Routes

```php
// Filament Routes (auto-generated)
/admin/target-produksis              // List
/admin/target-produksis/create       // Create
/admin/target-produksis/{id}         // View
/admin/target-produksis/{id}/edit    // Edit
```

---

## 🎯 Performance Tips

```php
// ✓ Good: Eager loading
$targets = TargetProduksi::with(['produk', 'details'])->get();

// ✗ Bad: N+1 query
$targets = TargetProduksi::all();
foreach ($targets as $target) {
    echo $target->produk->nama; // N+1 query!
}

// ✓ Good: Use helper for calculations
$target = TargetProduksiHelper::getTargetBulan($produkId, $bulan);

// ✓ Good: Cache repetitive queries
Cache::remember('dashboard_summary_2027', 3600, 
    fn() => $service->getDashboardSummary(2027)
);
```

---

## 📚 Documentation Files

```
README_TARGET_PRODUKSI.md               // Quick start guide
docs/TARGET_PRODUKSI_README.md          // Comprehensive docs
docs/TARGET_PRODUKSI_DIAGRAM.md         // Flow diagrams
docs/TARGET_PRODUKSI_SUMMARY.md         // Implementation summary
docs/TARGET_PRODUKSI_INTEGRATION_EXAMPLES.md  // Integration examples
docs/TARGET_PRODUKSI_QUICK_REFERENCE.md // This file
CHECKLIST_TARGET_PRODUKSI.md            // Implementation checklist
```

---

## 🆘 Need Help?

1. **Concept unclear?** → Read `TARGET_PRODUKSI_README.md`
2. **Need examples?** → Check `TARGET_PRODUKSI_INTEGRATION_EXAMPLES.md`
3. **Want flow diagrams?** → See `TARGET_PRODUKSI_DIAGRAM.md`
4. **Quick implementation?** → Follow `README_TARGET_PRODUKSI.md`

---

## ✅ Quick Test

```php
// Test 1: Get target
$target = TargetProduksiHelper::getTargetBulan(1, 7, 2027);
dump($target); // Should return integer

// Test 2: Check lock
$locked = TargetProduksiHelper::isMonthEditable(7, 2027);
dump($locked); // Should return boolean

// Test 3: Get status
$status = TargetProduksiHelper::getStatusPencapaian(95.5);
dump($status); // Should return array with status, color, label

// Test 4: Format
$formatted = TargetProduksiHelper::formatNumber(10000);
dump($formatted); // Should return "10.000"
```

---

**Version:** 1.0.0  
**Format:** Quick Reference Card  
**Last Updated:** {{ now()->format('d F Y') }}

**Keep this file handy for quick lookups! 📌**
