# Integrasi COA pada Master Data Aset

## Ringkasan
Telah berhasil menambahkan tiga kolom COA pada halaman master-data/aset/create untuk memastikan struktur jurnal umum yang rapi:

1. **Akun COA Aset** - untuk mencatat nilai perolehan aset
2. **Akun COA Akumulasi Penyusutan** - untuk mencatat akumulasi penyusutan aset
3. **Akun COA Beban Penyusutan** - untuk mencatat biaya penyusutan aset

## Perubahan yang Dilakukan

### 1. Database Migration
- **File**: `database/migrations/2026_03_31_add_asset_coa_to_assets_table.php`
- **Tabel**: `asets`
- **Kolom baru**:
  - `asset_coa_id` (foreign key ke tabel `coas`)
  - `accum_depr_coa_id` (foreign key ke tabel `coas`)
  - `expense_coa_id` (foreign key ke tabel `coas`)

### 2. Model Aset
- **File**: `app/Models/Aset.php`
- **Perubahan**:
  - Menambahkan kolom baru ke `$fillable`
  - Menambahkan relationship methods:
    - `assetCoa()` - untuk akun aset
    - `accumDeprCoa()` - untuk akun akumulasi penyusutan
    - `expenseCoa()` - untuk akun beban penyusutan

### 3. AssetResource (Filament)
- **File**: `app/Filament/Resources/AssetResource.php`
- **Perubahan**:
  - Menggunakan model `Aset` yang benar (bukan `Asset`)
  - Form dengan section "Akun COA" yang berisi:
    - Select field untuk COA Aset (filter: tipe Asset/Aset)
    - Select field untuk COA Akumulasi (filter: tipe Asset/Aset + nama mengandung "akumulasi")
    - Select field untuk COA Beban (filter: tipe Biaya/Beban/Expense + nama mengandung "penyusutan")
  - Tabel dengan kolom COA (tersembunyi secara default, bisa ditampilkan)

## Fitur Form

### Section Akun COA
Form sekarang memiliki section khusus untuk pemilihan akun COA dengan:

1. **Akun COA Aset**
   - Filter: `tipe_akun` = 'Asset' atau 'Aset'
   - Helper text: "Pilih akun aset untuk mencatat nilai perolehan aset"
   - Required field

2. **Akun COA Akumulasi Penyusutan**
   - Filter: `tipe_akun` = 'Asset' atau 'Aset' DAN `nama_akun` mengandung 'akumulasi'
   - Helper text: "Pilih akun akumulasi penyusutan aset"
   - Required field

3. **Akun COA Beban Penyusutan**
   - Filter: `tipe_akun` = 'Biaya', 'Beban', atau 'Expense' DAN `nama_akun` mengandung 'penyusutan'
   - Helper text: "Pilih akun beban penyusutan untuk mencatat biaya penyusutan"
   - Required field

## Manfaat

### Struktur Jurnal yang Rapi
Dengan adanya integrasi COA ini, setiap aset akan memiliki:
- Akun aset yang jelas untuk pencatatan nilai perolehan
- Akun akumulasi penyusutan yang terpisah
- Akun beban penyusutan yang sesuai

### Otomatisasi Jurnal
Sistem sekarang dapat membuat jurnal otomatis untuk:
- Pembelian aset (Debit: Akun Aset, Kredit: Kas/Hutang)
- Penyusutan bulanan (Debit: Beban Penyusutan, Kredit: Akumulasi Penyusutan)

### Laporan yang Akurat
Dengan COA yang terstruktur, laporan keuangan akan lebih akurat:
- Laporan Posisi Keuangan menampilkan nilai aset bersih (aset - akumulasi penyusutan)
- Laba rugi menampilkan beban penyusutan yang tepat

## Cara Penggunaan

1. Buka halaman **Master Data > Aset**
2. Klik **Create** untuk menambah aset baru
3. Isi data aset (nama, tanggal, harga, dll.)
4. Di section **Akun COA**, pilih:
   - Akun COA Aset (contoh: Peralatan, Gedung, Kendaraan)
   - Akun COA Akumulasi (contoh: Akumulasi Penyusutan Peralatan)
   - Akun COA Beban (contoh: Beban Penyusutan Peralatan)
5. Simpan data

## Catatan Teknis

- Semua field COA bersifat **required** untuk memastikan kelengkapan data
- Field COA menggunakan **searchable select** untuk memudahkan pencarian
- Kolom COA di tabel bersifat **toggleable** (bisa disembunyikan/ditampilkan)
- Relationship menggunakan **foreign key constraint** untuk menjaga integritas data

## Status
✅ **SELESAI** - Implementasi berhasil dan siap digunakan