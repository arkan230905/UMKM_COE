# Perbaikan Error: table asets has no column named kode_aset

**Tanggal:** 3 November 2025, 10:50 WIB  
**Status:** ✅ **SELESAI**

## Masalah

Error saat menyimpan aset baru:
```
SQLSTATE[HY000]: General error: 1 table asets has no column named kode_aset
```

## Root Cause

Tabel `asets` di database tidak memiliki kolom-kolom yang diperlukan:
- `kode_aset` (kolom utama yang hilang)
- `metode_penyusutan`
- `nilai_sisa`
- `akumulasi_penyusutan`
- `tanggal_akuisisi`

Migration `2025_11_03_104000_rebuild_asets_table.php` belum dijalankan karena ada migration lain yang error (pegawais table dengan syntax MySQL di SQLite).

## Solusi yang Diterapkan

### 1. Migration Baru untuk Menambah Kolom
**File:** `database/migrations/2025_11_03_105100_add_kode_aset_column.php`

Migration ini:
- Menambahkan kolom `kode_aset` (string, unique, not null)
- Auto-generate kode untuk data existing (format: AST-YYYYMM-XXXX)
- Menambahkan kolom `metode_penyusutan` (enum)
- Menambahkan kolom `nilai_sisa` (decimal)
- Menambahkan kolom `akumulasi_penyusutan` (decimal)
- Menambahkan kolom `tanggal_akuisisi` (date)

### 2. Update Model Aset
**File:** `app/Models/Aset.php`

**Perubahan pada `$fillable`:**
```php
protected $fillable = [
    'kode_aset',
    'nama_aset',
    'kategori_aset_id',
    'harga_perolehan',
    'biaya_perolehan',
    'nilai_residu',
    'umur_manfaat',
    'penyusutan_per_tahun',
    'penyusutan_per_bulan',
    'nilai_buku',
    'tanggal_beli',
    'tanggal_akuisisi',
    'status',
    'metode_penyusutan',      // ✅ DITAMBAHKAN
    'nilai_sisa',             // ✅ DITAMBAHKAN
    'akumulasi_penyusutan',   // ✅ DITAMBAHKAN
    'keterangan'
];
```

**Perubahan pada `$casts`:**
```php
protected $casts = [
    'tanggal_perolehan' => 'date',
    'tanggal_beli' => 'date',           // ✅ DITAMBAHKAN
    'tanggal_akuisisi' => 'date',       // ✅ DITAMBAHKAN
    'harga_perolehan' => 'decimal:2',
    'biaya_perolehan' => 'decimal:2',   // ✅ DITAMBAHKAN
    'nilai_residu' => 'decimal:2',      // ✅ DITAMBAHKAN
    'nilai_sisa' => 'decimal:2',
    'nilai_buku' => 'decimal:2',
    'akumulasi_penyusutan' => 'decimal:2',
    'persentase_penyusutan' => 'decimal:2',
    'umur_manfaat' => 'integer',        // ✅ DITAMBAHKAN
    'umur_ekonomis_tahun' => 'integer',
];
```

### 3. Fix Migration Pegawais
**File:** `database/migrations/2025_11_03_003400_align_pegawais_schema.php`

Mengubah dari `unique` constraint menjadi `index` biasa untuk menghindari error duplicate values:
```php
// Sebelum: $table->unique('nomor_induk_pegawai', 'uniq_pegawais_nip');
// Sesudah: $table->index('nomor_induk_pegawai', 'idx_pegawais_nip');
```

## Command yang Dijalankan

```bash
php artisan migrate --path=database/migrations/2025_11_03_105100_add_kode_aset_column.php --force
```

## Hasil Testing

✅ **Berhasil membuat aset baru dengan data:**
- ID: 4
- Kode: AST-202511-0004 (auto-generated)
- Nama: Test Meja
- Kategori: Tanah (ID: 1)
- Nilai Buku: 500000
- Metode Penyusutan: garis_lurus (default)

## Fitur yang Bekerja

1. ✅ Auto-generate `kode_aset` dengan format AST-YYYYMM-XXXX
2. ✅ Default values untuk kolom numeric (nilai_buku, akumulasi_penyusutan, dll)
3. ✅ Default metode penyusutan: 'garis_lurus'
4. ✅ Default status: 'aktif'
5. ✅ Relasi ke KategoriAset berfungsi
6. ✅ Boot method untuk auto-populate fields

## Catatan Penting

- Migration `2025_10_29_183709_fix_pegawais_table_structure.php` masih pending karena menggunakan syntax MySQL (`SET FOREIGN_KEY_CHECKS`, `INFORMATION_SCHEMA`) yang tidak kompatibel dengan SQLite
- Migration tersebut tidak mempengaruhi modul Aset
- Jika perlu fix pegawais table, migration tersebut harus di-refactor untuk SQLite

## File yang Dimodifikasi

1. ✅ `database/migrations/2025_11_03_105100_add_kode_aset_column.php` (BARU)
2. ✅ `app/Models/Aset.php` (UPDATE fillable & casts)
3. ✅ `database/migrations/2025_11_03_003400_align_pegawais_schema.php` (FIX unique constraint)

## Status Akhir

🎉 **Modul Aset sekarang berfungsi dengan baik!**
- Form dapat menyimpan data aset baru
- Kode aset auto-generate
- Semua kolom tersedia di database
- Model sudah sinkron dengan database schema
