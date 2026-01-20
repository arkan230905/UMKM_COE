# Fix: Biaya Bahan Data Accuracy in BOM

## Problem
Data biaya bahan yang ditampilkan saat tambah/edit BOM tidak sesuai dengan yang ada di halaman biaya bahan. Ini masalah kritis karena data biaya bahan harus akurat dan konsisten.

## Root Cause Analysis

### Masalah di Method `getBiayaBahanData()`
```php
// SALAH: Mengambil semua bahan baku dan bahan pendukung secara umum
$bahanBakus = BahanBaku::with('satuan')
    ->where('harga_rata_rata', '>', 0)
    ->get()
    ->map(function($bahan) {
        return [
            'harga' => $bahan->harga_rata_rata, // SALAH: harga_rata_rata
            'jumlah' => 1, // SALAH: default 1
        ];
    });
```

**Masalah:**
1. **Data tidak spesifik produk**: Mengambil semua bahan, bukan berdasarkan BOM produk tertentu
2. **Harga salah**: Menggunakan `harga_rata_rata` bukan `harga_satuan`
3. **Jumlah salah**: Default 1, bukan jumlah sebenarnya dari BOM
4. **Tidak ada konversi satuan**: Tidak menggunakan `UnitConverter`
5. **Tidak konsisten**: Logika berbeda dengan `BiayaBahanController`

## Solution
Menggunakan logika yang sama persis dengan `BiayaBahanController` untuk konsistensi data.

### 1. Updated `getBiayaBahanData()` Method
```php
private function getBiayaBahanData($produkId = null)
{
    if (!$produkId) {
        return collect([]);
    }
    
    $produk = Produk::find($produkId);
    $converter = new \App\Support\UnitConverter();
    
    // 1. Ambil Bahan Baku dari BOM yang sudah ada
    $bomDetails = \App\Models\BomDetail::with('bahanBaku.satuan')
        ->where('bom_id', function($query) use ($produk) {
            $query->select('id')->from('boms')->where('produk_id', $produk->id);
        })
        ->get();
    
    // 2. Ambil Bahan Pendukung dari BomJobCosting
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
    
    // 3. Gunakan UnitConverter untuk konversi satuan
    // 4. Ambil harga terbaru dari master data
    // 5. Hitung subtotal yang akurat
}
```

### 2. Key Improvements

#### Data Source Accuracy
```php
// Before: Generic data
$bahanBakus = BahanBaku::where('harga_rata_rata', '>', 0)->get();

// After: Product-specific data
$bomDetails = BomDetail::where('bom_id', function($query) use ($produk) {
    $query->select('id')->from('boms')->where('produk_id', $produk->id);
})->get();
```

#### Price Accuracy
```php
// Before: Wrong price field
'harga' => $bahan->harga_rata_rata

// After: Correct price field
'harga' => $detail->bahanBaku->harga_satuan
```

#### Quantity Accuracy
```php
// Before: Default quantity
'jumlah' => 1

// After: Actual quantity from BOM
'jumlah' => (float) $detail->jumlah
```

#### Unit Conversion
```php
// Before: No conversion
'satuan' => $bahan->satuan->nama ?? 'KG'

// After: Proper conversion
$qtyBase = $converter->convert($qty, $satuan, $satuanBase);
$subtotal = $hargaSatuan * $qtyBase;
```

### 3. Updated Controller Methods

#### Create Method
```php
// Before
$biayaBahan = $this->getBiayaBahanData();

// After
$biayaBahan = collect([]);
if ($selectedProduk) {
    $biayaBahan = $this->getBiayaBahanData($selectedProduk->id);
}
```

#### Edit Method
```php
// Before
$biayaBahan = $this->getBiayaBahanData();

// After
$biayaBahan = $this->getBiayaBahanData($bom->produk_id);
```

#### Store & Update Methods
```php
// Before
$biayaBahan = $this->getBiayaBahanData();

// After
$biayaBahan = $this->getBiayaBahanData($request->produk_id);
$biayaBahan = $this->getBiayaBahanData($bom->produk_id);
```

### 4. Enhanced View Display

#### Detailed Information
```php
// Show both original and base quantities/units
<span class="fw-semibold">{{ number_format($bahan['jumlah'], 3) }}</span>
@if(isset($bahan['jumlah_base']) && $bahan['jumlah'] != $bahan['jumlah_base'])
    <br><small class="text-muted">Base: {{ number_format($bahan['jumlah_base'], 3) }}</small>
@endif
```

#### Accurate Subtotal Calculation
```php
// Before: Simple multiplication
Rp {{ number_format($bahan['harga'] * ($bahan['jumlah'] ?? 1), 0, ',', '.') }}

// After: Use calculated subtotal
Rp {{ number_format($bahan['subtotal'] ?? ($bahan['harga'] * $bahan['jumlah']), 0, ',', '.') }}
```

## Data Flow Consistency

### BiayaBahanController → BomController
1. **Same Logic**: Both use identical calculation methods
2. **Same Data Source**: Both read from BOM tables and master data
3. **Same Conversion**: Both use UnitConverter for unit conversion
4. **Same Price Source**: Both use `harga_satuan` from master data

### Data Structure Consistency
```php
// Both controllers return same structure
[
    'id' => $id,
    'nama' => $nama,
    'kode' => $kode,
    'harga' => $hargaSatuan,
    'jumlah' => $qty,
    'jumlah_base' => $qtyBase,
    'satuan' => $satuan,
    'satuan_base' => $satuanBase,
    'subtotal' => $subtotal,
    'kategori' => $kategori,
    'tipe' => $tipe
]
```

## Testing Scenarios
✅ **Create BOM with selected product** → Shows accurate biaya bahan data
✅ **Edit existing BOM** → Shows current biaya bahan data for that product
✅ **Data matches BiayaBahan page** → Same calculations and values
✅ **Unit conversion accuracy** → Proper conversion between units
✅ **Price accuracy** → Uses latest harga_satuan from master data
✅ **Quantity accuracy** → Uses actual quantities from BOM

## Files Modified
1. `app/Http/Controllers/BomController.php` - Fixed getBiayaBahanData() method
2. `resources/views/master-data/bom/create.blade.php` - Enhanced display
3. `resources/views/master-data/bom/edit.blade.php` - Enhanced display

## Status
**COMPLETED** - Biaya bahan data in BOM now accurately matches the data from BiayaBahan page.