# 📊 FINAL: BOM HPP - Penjelasan Lengkap

## ✅ STATUS: COMPLETE & DOCUMENTED

## 🎯 UNDERSTANDING: 2 Sistem HPP

### Sistem 1: Bom (Tabel `boms`) - LEGACY
```
Kolom:
- total_bbb (BBB saja)
- total_hpp (HPP = BBB + BTKL + BOP)
- total_biaya (sama dengan total_hpp)

Perhitungan:
- BTKL = 60% dari BBB (fallback)
- BOP = 40% dari BBB (fallback)
- HPP = BBB + BTKL + BOP
```

**Status**: Legacy system, kurang akurat

### Sistem 2: BomJobCosting (Tabel `bom_job_costings`) - CURRENT
```
Kolom:
- total_bbb (BBB)
- total_bahan_pendukung (Bahan Pendukung)
- total_btkl (BTKL)
- total_bop (BOP)
- total_hpp (HPP lengkap)
- hpp_per_unit (HPP per unit)

Perhitungan:
- BBB = sum(BomJobBBB.subtotal)
- Bahan Pendukung = sum(BomJobBahanPendukung.subtotal)
- BTKL = sum(BomJobBTKL.subtotal)
- BOP = sum(BomJobBOP.subtotal)
- HPP = BBB + Bahan Pendukung + BTKL + BOP
```

**Status**: Current system, LEBIH AKURAT ✅

## 📊 PRIORITY UNTUK DISPLAY

### Di Halaman BOM Index:
```php
// Priority 1: BomJobCosting (paling akurat)
if ($bomJobCosting && $bomJobCosting->total_hpp > 0) {
    $totalBiaya = $bomJobCosting->total_hpp;  // ✅ GUNAKAN INI
}
// Priority 2: Bom (fallback)
elseif ($bom) {
    $totalBiaya = $bom->total_hpp;
}
```

### Di Halaman BOM Detail (Show):
```php
// Hitung real-time dari data terbaru
$hpp = $totalBBB + $totalBahanPendukung + $totalBTKL + $totalBOP;
```

**Note**: Di halaman detail, HPP dihitung real-time untuk menampilkan data terbaru.

## 🔄 ALUR UPDATE HPP

### Flow Lengkap:
```
1. Pembelian Bahan
   ↓
2. Observer Triggered (BahanBakuObserver / BahanPendukungObserver)
   ↓
3. Update Harga di BomJobBBB / BomJobBahanPendukung
   ↓
4. BomJobCosting->recalculate()
   - Hitung total_bbb
   - Hitung total_bahan_pendukung
   - Hitung total_btkl
   - Hitung total_bop
   - Hitung total_hpp = BBB + BP + BTKL + BOP
   ↓
5. Update Produk
   - biaya_bahan = BBB + Bahan Pendukung
   - harga_bom = BomJobCosting->total_hpp ✅
   ↓
6. Display di BOM Index
   - Total Biaya BOM = harga_bom (dari BomJobCosting)
```

## 📁 STRUKTUR DATA

### Tabel `boms`:
```sql
- id
- kode_bom
- produk_id
- total_biaya  (legacy)
- total_bbb    (BBB saja)
- total_hpp    (HPP = BBB + BTKL + BOP, kurang akurat)
```

### Tabel `bom_job_costings`:
```sql
- id
- produk_id
- jumlah_produk
- total_bbb
- total_bahan_pendukung
- total_btkl
- total_bop
- total_hpp  ← GUNAKAN INI! ✅
- hpp_per_unit
```

### Tabel `produks`:
```sql
- id
- nama_produk
- biaya_bahan  (BBB + Bahan Pendukung)
- harga_bom    (HPP lengkap dari BomJobCosting) ✅
- harga_jual   (HPP + margin)
```

## ✅ VALIDATION

### Cek Data Benar:
```php
// 1. Cek BomJobCosting
$bomJobCosting = BomJobCosting::where('produk_id', $produkId)->first();
echo "BomJobCosting->total_hpp: " . $bomJobCosting->total_hpp;

// 2. Cek Produk
$produk = Produk::find($produkId);
echo "Produk->harga_bom: " . $produk->harga_bom;

// 3. Validasi
if ($produk->harga_bom == $bomJobCosting->total_hpp) {
    echo "✅ MATCH!";
} else {
    echo "❌ MISMATCH! Need recalculate";
}
```

### Recalculate Jika Perlu:
```php
$bomJobCosting->recalculate();
$produk->update(['harga_bom' => $bomJobCosting->total_hpp]);
```

## 🎯 KESIMPULAN

### Yang Benar:
1. ✅ **BomJobCosting.total_hpp** adalah sumber HPP yang paling akurat
2. ✅ **Produk.harga_bom** harus = BomJobCosting.total_hpp
3. ✅ **BOM Index** menampilkan harga_bom (yang sudah = HPP dari BomJobCosting)
4. ✅ **BOM Detail** menghitung HPP real-time untuk tampilan

### Yang Salah:
1. ❌ Jangan gunakan Bom.total_hpp (kurang akurat, legacy)
2. ❌ Jangan recalculate Bom (bisa salah, sistem lama)
3. ❌ Jangan update harga_bom dengan biaya_bahan (bukan HPP lengkap)

## 📝 MAINTENANCE

### Jika Data Tidak Sync:
```bash
# Recalculate BomJobCosting
php recalculate_all_bom_hpp.php

# Script akan:
# 1. Recalculate semua BomJobCosting
# 2. Update produk.harga_bom dengan BomJobCosting.total_hpp
```

### Manual Fix:
```php
$bomJobCosting = BomJobCosting::find($id);
$bomJobCosting->recalculate();

$produk = $bomJobCosting->produk;
$produk->update(['harga_bom' => $bomJobCosting->total_hpp]);
```

---
**Created**: 2025-01-15
**System**: BomJobCosting (Current & Accurate)
**Legacy**: Bom (Old & Less Accurate)
**Recommendation**: Always use BomJobCosting.total_hpp ✅
