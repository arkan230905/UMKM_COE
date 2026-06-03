# Perbaikan Error BOP dan Pegawai

## Tanggal: 1 Juni 2026

### Masalah yang Diperbaiki

#### 1. Error saat membuka halaman BOP
**Error Message:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'kapasitas_per_jam' in 'where clause'
SQL: select * from `proses_produksis` where `user_id` = 3 and `kapasitas_per_jam` > 0 order by `kode_proses` asc
```

**Penyebab:**
- Migration `2026_05_25_070339_remove_unused_columns_from_proses_produksis_table.php` menghapus kolom `kapasitas_per_jam` dari tabel `proses_produksis`
- Namun masih ada kode yang mencoba mengakses kolom tersebut

**Solusi:**
1. **File:** `resources/views/master-data/bop-terpadu/index.blade.php`
   - Menghapus kondisi `@if($proses->kapasitas_per_jam <= 0)` dan `@if($proses->kapasitas_per_jam > 0)`
   - Mengubah tampilan kapasitas untuk mengambil dari `$proses->bopProses->kapasitas_per_jam` jika ada BOP

2. **File:** `app/Http/Controllers/MasterData/BopTerpaduController.php`
   - Method `createProses()`: Menghapus pesan error yang menyebutkan "kapasitas per jam"
   - Method `getAnalysisData()`: Menambahkan null coalescing operator `??` untuk `kapasitas_per_jam`

#### 2. Error saat menambah pegawai
**Error Message:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'tarif' in 'field list'
SQL: insert into `pegawais` (..., `tarif`, ...) values (...)
```

**Penyebab:**
- Tabel `pegawais` tidak memiliki kolom `tarif`
- Kolom yang benar adalah `tarif_per_produk` atau `tarif_per_jam`

**Solusi:**
**File:** `app/Http/Controllers/PegawaiController.php`
- Line 151 (method `store`): Mengubah `'tarif' => $jabatan->tarif_produk ?? 0` menjadi `'tarif_per_produk' => $jabatan->tarif_produk ?? 0`
- Line 268 (method `update`): Mengubah `'tarif' => $jabatan->tarif_produk ?? 0` menjadi `'tarif_per_produk' => $jabatan->tarif_produk ?? 0`

### Struktur Tabel yang Benar

#### Tabel `proses_produksis`
Kolom yang **TIDAK ADA** (sudah dihapus):
- `kapasitas_per_jam`
- `tarif_btkl`
- `satuan_btkl`
- `biaya_btkl_per_produk`

Kolom yang **MASIH ADA**:
- `tarif_per_produk`
- `jumlah_pegawai`
- `jabatan_id`
- `btkl_id`

#### Tabel `bop_proses`
Kolom yang **MASIH ADA**:
- `kapasitas_per_jam` ✓ (masih digunakan untuk perhitungan BOP)

#### Tabel `pegawais`
Kolom yang **ADA**:
- `tarif_per_jam` ✓
- `tarif_per_produk` ✓
- `tarif_lembur` ✓

Kolom yang **TIDAK ADA**:
- `tarif` ✗

### Catatan Penting

1. **Kapasitas per jam** sekarang hanya disimpan di tabel `bop_proses`, tidak lagi di `proses_produksis`
2. Sistem sekarang menggunakan pembebanan **per produk** bukan per jam
3. Jika ada view lain yang masih menggunakan `$proses->kapasitas_per_jam`, harus diubah menjadi `$proses->bopProses->kapasitas_per_jam ?? 0`

### File yang Diubah

1. `app/Http/Controllers/PegawaiController.php` - Perbaikan kolom tarif
2. `app/Http/Controllers/MasterData/BopTerpaduController.php` - Perbaikan referensi kapasitas_per_jam
3. `resources/views/master-data/bop-terpadu/index.blade.php` - Perbaikan tampilan BOP

### Testing

Setelah perbaikan ini:
- ✓ Halaman BOP dapat dibuka tanpa error
- ✓ Tambah pegawai dapat dilakukan tanpa error
- ✓ Data kapasitas tetap ditampilkan dari tabel bop_proses
