# Fix BTKL Controller dan Model

**Tanggal:** 25 Mei 2026  
**Masalah:** Error saat menyimpan BTKL karena masih menggunakan kolom yang sudah dihapus

---

## 🐛 Masalah yang Ditemukan

### Error Message:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'tarif_btkl' in 'field list'
```

### Root Cause:
1. **BtklController** masih mencoba menyimpan ke kolom `tarif_btkl`, `satuan_btkl`, `kapasitas_per_jam`, `biaya_btkl_per_produk`
2. **Model ProsesProduksi** masih memiliki kolom lama di `$fillable` dan `$casts`
3. **Model ProsesProduksi** memiliki banyak method yang masih menggunakan kolom lama

---

## ✅ Solusi yang Diterapkan

### 1. Fix BtklController.php

#### Method `store()`:
**Sebelum:**
```php
'tarif_btkl'      => $tarifBtkl,
'satuan_btkl'     => $validated['satuan'],
'kapasitas_per_jam' => 0,
'biaya_btkl_per_produk' => 0,
```

**Sesudah:**
```php
'jabatan_id'        => $validated['jabatan_id'],
'tarif_per_produk'  => $tarifPerProduk,
'jumlah_pegawai'    => $jumlahPegawai,
'btkl_id'           => $btkl->id,
```

#### Method `update()`:
**Sebelum:**
```php
'tarif_btkl'       => $tarifBtkl,
'satuan_btkl'      => $btkl->satuan,
'kapasitas_per_jam' => 0,
```

**Sesudah:**
```php
'jabatan_id'        => $btkl->jabatan_id,
'tarif_per_produk'  => $tarifPerProduk,
'jumlah_pegawai'    => $jumlahPegawai,
```

---

### 2. Fix Model ProsesProduksi.php

#### Fillable:
**Sebelum:**
```php
protected $fillable = [
    'user_id',
    'kode_proses',
    'nama_proses',
    'deskripsi',
    'tarif_btkl',           // ❌ Dihapus
    'satuan_btkl',          // ❌ Dihapus
    'kapasitas_per_jam',    // ❌ Dihapus
    'jabatan_id',
    'btkl_id',
    'biaya_btkl_per_produk', // ❌ Dihapus
];
```

**Sesudah:**
```php
protected $fillable = [
    'user_id',
    'kode_proses',
    'nama_proses',
    'deskripsi',
    'jabatan_id',
    'tarif_per_produk',     // ✅ Baru
    'jumlah_pegawai',       // ✅ Baru
    'btkl_id',
];
```

#### Casts:
**Sebelum:**
```php
protected $casts = [
    'tarif_btkl' => 'decimal:2',
    'biaya_btkl_per_produk' => 'decimal:2',
    'kapasitas_per_jam' => 'integer',
];
```

**Sesudah:**
```php
protected $casts = [
    'tarif_per_produk' => 'decimal:2',
    'jumlah_pegawai' => 'integer',
];
```

#### Methods yang Diperbaiki:

1. **scopeActive()** - Menggunakan `tarif_per_produk` bukan `tarif_btkl`
2. **getTotalBtklAttribute()** - Baru, menghitung `tarif_per_produk × jumlah_pegawai`
3. **getBiayaPerProdukAttribute()** - Menggunakan `getTotalBtklAttribute()`
4. **getTarifPerProdukFormattedAttribute()** - Baru
5. **getTarifPerJamFormattedAttribute()** - Backward compatibility, menampilkan `total_btkl`
6. **getSatuanAttribute()** - Return 'Produk' bukan 'Jam'
7. **validateTarifConsistency()** - Menggunakan `tarif_produk` dari jabatan

---

## 📊 Perbandingan Sistem

### Sistem Lama (Per Jam):
```php
// Input
$tarifPerJam = 50000;
$jumlahPegawai = 2;
$kapasitasPerJam = 10;

// Perhitungan
$tarifBtkl = $tarifPerJam * $jumlahPegawai; // 100000
$biayaPerProduk = $tarifBtkl / $kapasitasPerJam; // 10000
```

### Sistem Baru (Per Produk):
```php
// Input
$tarifPerProduk = 375; // Dari jabatan
$jumlahPegawai = 1;

// Perhitungan
$totalBtkl = $tarifPerProduk * $jumlahPegawai; // 375
```

**Keuntungan:**
- ✅ Lebih sederhana
- ✅ Tidak perlu hitung kapasitas per jam
- ✅ Langsung dari tarif jabatan
- ✅ Lebih akurat per produk

---

## 🧪 Testing

### Test 1: Simpan BTKL Baru
```php
// Data input
$data = [
    'kode_proses' => 'PRO-004',
    'nama_btkl' => 'Pengemasan',
    'jabatan_id' => 3,
    'deskripsi_proses' => 'Proses pengemasan produk',
];

// Expected result
$prosesProduksi = ProsesProduksi::where('kode_proses', 'PRO-004')->first();
$prosesProduksi->tarif_per_produk; // 266 (dari jabatan)
$prosesProduksi->jumlah_pegawai; // 1
$prosesProduksi->total_btkl; // 266
```

### Test 2: Update BTKL
```php
// Update jabatan
$prosesProduksi->update([
    'jabatan_id' => 2,
]);

// Expected result
$prosesProduksi->tarif_per_produk; // Tarif dari jabatan baru
$prosesProduksi->total_btkl; // tarif_per_produk × jumlah_pegawai
```

### Test 3: Accessor
```php
$prosesProduksi->total_btkl; // 266
$prosesProduksi->biaya_per_produk; // 266 (alias)
$prosesProduksi->tarif_per_produk_formatted; // "Rp 266"
$prosesProduksi->satuan; // "Produk"
```

---

## 📁 File yang Diubah

1. ✅ `app/Http/Controllers/MasterData/BtklController.php`
   - Method `store()` - Menggunakan kolom baru
   - Method `update()` - Menggunakan kolom baru

2. ✅ `app/Models/ProsesProduksi.php`
   - `$fillable` - Update kolom
   - `$casts` - Update kolom
   - `scopeActive()` - Fix query
   - `getTotalBtklAttribute()` - Baru
   - `getBiayaPerProdukAttribute()` - Fix perhitungan
   - `getTarifPerProdukFormattedAttribute()` - Baru
   - `getTarifPerJamFormattedAttribute()` - Backward compatibility
   - `getSatuanAttribute()` - Return 'Produk'
   - `validateTarifConsistency()` - Fix validasi

---

## ⚠️ Breaking Changes

### Accessor yang Berubah:
- `$prosesProduksi->satuan` - Sekarang return 'Produk' bukan 'Jam'
- `$prosesProduksi->biaya_per_produk` - Sekarang `tarif_per_produk × jumlah_pegawai`
- `$prosesProduksi->efisiensi_produksi` - Sekarang return 0 (tidak digunakan)

### Method yang Deprecated:
- Tidak ada method yang dihapus, hanya diubah implementasinya

---

## 🚀 Deployment

### Langkah untuk Tim:

```bash
# 1. Pull perubahan
git pull origin main

# 2. Tidak perlu migrasi baru (sudah dijalankan sebelumnya)
# Migrasi yang sudah ada:
# - 2026_05_25_050411_remove_unused_columns_from_btkls_table.php
# - 2026_05_25_070339_remove_unused_columns_from_proses_produksis_table.php

# 3. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 4. Test BTKL
# - Buat BTKL baru
# - Update BTKL existing
# - Cek data di database
```

---

## ✅ Verifikasi

### Cek Database:
```sql
-- Cek struktur tabel
DESCRIBE proses_produksis;

-- Pastikan kolom ini ADA:
-- - tarif_per_produk
-- - jumlah_pegawai

-- Pastikan kolom ini TIDAK ADA:
-- - tarif_btkl
-- - satuan_btkl
-- - kapasitas_per_jam
-- - biaya_btkl_per_produk

-- Cek data
SELECT 
    kode_proses,
    nama_proses,
    tarif_per_produk,
    jumlah_pegawai,
    (tarif_per_produk * jumlah_pegawai) AS total_btkl
FROM proses_produksis;
```

### Test di Aplikasi:
1. ✅ Buka halaman Master Data > BTKL
2. ✅ Klik "Tambah BTKL"
3. ✅ Isi form dan simpan
4. ✅ Pastikan tidak ada error
5. ✅ Cek data tersimpan dengan benar di database

---

**Status:** ✅ FIXED  
**Tested:** ✅ YES  
**Ready to Deploy:** ✅ YES
