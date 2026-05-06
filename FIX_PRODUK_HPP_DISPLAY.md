# ✅ FIX: Kolom Harga Pokok Produksi di Halaman Produk

## 🎯 Problem

Kolom "Harga Pokok Produksi" di halaman `/master-data/produk` menampilkan nilai 0, padahal di halaman `/master-data/harga-pokok-produksi` sudah ada perhitungan Total HPP.

## 🔍 Root Cause

**File**: `app/Http/Controllers/ProdukController.php`

Method `index()` mengambil HPP dari field `harga_pokok` di database:

```php
// ❌ SEBELUM (SALAH)
$totalBiayaHPP = $produk->harga_pokok ?? 0;
$hargaBom[$produk->id] = $totalBiayaHPP;
```

Field `harga_pokok` di database tidak ter-update otomatis dari perhitungan di `/master-data/harga-pokok-produksi`.

## ✅ Solution

Menggunakan method `getActualHPP()` yang sudah mengambil HPP dari perhitungan Harga Pokok Produksi (BBB + BTKL + BOP):

```php
// ✅ SESUDAH (BENAR)
$totalBiayaHPP = $produk->getActualHPP();
$hargaBom[$produk->id] = $totalBiayaHPP;
```

## 📝 Changes Made

**File Modified**: `app/Http/Controllers/ProdukController.php`

```php
public function index()
{
    // Get all products
    $produks = Produk::where('user_id', auth()->id())->get();
    
    // Calculate HPP from Harga Pokok Produksi (BBB + BTKL + BOP)
    $hargaBom = [];
    foreach ($produks as $produk) {
        // Use getActualHPP() method which gets HPP from harga-pokok-produksi
        $totalBiayaHPP = $produk->getActualHPP();
        $hargaBom[$produk->id] = $totalBiayaHPP;
    }
    
    return view('master-data.produk.index', compact('produks', 'hargaBom'));
}
```

## 🔄 How It Works

### Flow Perhitungan HPP

```
1. User input data di /master-data/harga-pokok-produksi
   ↓
   - Pilih BBB (Biaya Bahan Baku)
   - Pilih BTKL (Biaya Tenaga Kerja Langsung)
   - Pilih BOP (Biaya Overhead Pabrik)
   ↓
2. Data tersimpan di tabel:
   - harga_pokok_produksi_biaya_bahan_bakus
   - harga_pokok_produksi_btkls
   - harga_pokok_produksi_bops
   ↓
3. Method Produk::getActualHPP() menghitung:
   Total HPP = BBB + BTKL + BOP
   ↓
4. Nilai ditampilkan di /master-data/produk
   kolom "Harga Pokok Produksi"
```

### Method getActualHPP()

**File**: `app/Models/Produk.php`

```php
public function getActualHPP($tanggalPenjualan = null)
{
    // PRIORITY 1: Get from Harga Pokok Produksi (MAIN SOURCE)
    $hppFromCalculation = $this->getHPPFromHargaPokokProduksi();
    if ($hppFromCalculation > 0) {
        return $hppFromCalculation;
    }
    
    // PRIORITY 2: Fallback to production costs
    // PRIORITY 3: Fallback to harga_bom/hpp/harga_beli
    // ...
}

private function getHPPFromHargaPokokProduksi()
{
    $userId = $this->user_id ?? auth()->id();
    
    // Get BBB
    $totalBbb = sum dari biaya_bahan_baku.subtotal untuk produk ini
    
    // Get BTKL
    $totalBtkl = sum dari (tarif_btkl / kapasitas_per_jam)
    
    // Get BOP
    $totalBop = sum dari total_bop_per_produk
    
    // Total HPP
    return $totalBbb + $totalBtkl + $totalBop;
}
```

## ✅ Result

Sekarang kolom "Harga Pokok Produksi" di `/master-data/produk` akan menampilkan:

| Produk | Harga Pokok Produksi | Harga Jual | Stok |
|--------|---------------------|------------|------|
| Jasuke | **Rp 5.372** ✅ | Rp 10.000 | 360 |

Sebelumnya menampilkan **Rp 0** ❌

## 🧪 Testing

### Test 1: Cek di Browser
1. Buka `/master-data/harga-pokok-produksi`
2. Lihat Total HPP untuk produk (misal: Jasuke = Rp 5.372)
3. Buka `/master-data/produk`
4. Kolom "Harga Pokok Produksi" harus menampilkan **Rp 5.372** ✅

### Test 2: Cek dengan Produk Tanpa HPP
1. Produk yang belum ada data di `/master-data/harga-pokok-produksi`
2. Akan menampilkan **Rp 0** (fallback)
3. Setelah input BBB/BTKL/BOP, nilai akan muncul

### Test 3: Verify Calculation
```bash
php artisan tinker
```

```php
$produk = \App\Models\Produk::find(2);
$hpp = $produk->getActualHPP();
echo "HPP: Rp " . number_format($hpp, 0, ',', '.');
// Expected: HPP dari BBB + BTKL + BOP
```

## 📊 Impact

### Sebelum Fix
- ❌ Kolom HPP selalu 0
- ❌ Tidak sinkron dengan perhitungan di harga-pokok-produksi
- ❌ User bingung kenapa HPP tidak muncul

### Setelah Fix
- ✅ Kolom HPP menampilkan nilai real dari perhitungan
- ✅ Sinkron dengan `/master-data/harga-pokok-produksi`
- ✅ Konsisten dengan jurnal penjualan (sama-sama pakai `getActualHPP()`)

## 🔗 Related Files

1. **`app/Http/Controllers/ProdukController.php`** ✅ Modified
   - Method `index()` - Updated to use `getActualHPP()`

2. **`app/Models/Produk.php`** ✅ Already correct
   - Method `getActualHPP()` - Gets HPP from harga-pokok-produksi
   - Method `getHPPFromHargaPokokProduksi()` - Calculates BBB + BTKL + BOP

3. **`resources/views/master-data/produk/index.blade.php`** ✅ No change needed
   - Already uses `$hargaBom[$produk->id]` correctly

4. **`app/Services/JournalService.php`** ✅ Already uses same method
   - Uses `$product->getActualHPP()` for journal entries

## 📝 Notes

### Konsistensi Data

Sekarang HPP diambil dari sumber yang sama di semua tempat:

1. **Halaman Produk** (`/master-data/produk`) → `getActualHPP()` ✅
2. **Jurnal Penjualan** (`JournalService`) → `getActualHPP()` ✅
3. **Harga Pokok Produksi** (`/master-data/harga-pokok-produksi`) → Source data ✅

### Performance Note

Method `getActualHPP()` melakukan query ke beberapa tabel:
- `harga_pokok_produksi_biaya_bahan_bakus`
- `harga_pokok_produksi_btkls`
- `harga_pokok_produksi_bops`

Untuk halaman index dengan banyak produk, ini bisa menjadi N+1 query problem. Jika perlu optimasi di masa depan, bisa menggunakan eager loading atau caching.

---

**Date**: May 6, 2026  
**Status**: ✅ FIXED & TESTED  
**Impact**: High - Affects product listing display
