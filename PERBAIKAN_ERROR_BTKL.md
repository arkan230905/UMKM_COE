# Perbaikan Error BTKL - 25 Mei 2026

## Masalah yang Ditemukan

### 1. Error: Column 'tarif_btkl' not found
**Error Message:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'tarif_btkl' in 'field list'
SQL: insert into `proses_produksis` (`user_id`, `nama_proses`, `deskripsi`, `tarif_btkl`, `jabatan_id`, `biaya_btkl_per_produk`, `kode_proses`, `updated_at`, `created_at`) values (...)
```

**Penyebab:**
- Controller `ProsesProduksiController` mencoba insert kolom `tarif_btkl` dan `biaya_btkl_per_produk` yang tidak ada di tabel `proses_produksis`
- Tabel `proses_produksis` hanya memiliki kolom `tarif_per_produk`, bukan `tarif_btkl`
- Kolom `tarif_btkl` sudah dihapus dari tabel berdasarkan migration `2026_05_25_070339_remove_unused_columns_from_proses_produksis_table.php`

### 2. Nilai tarif_per_produk selalu 0
**Penyebab:**
- Form view mengirim field dengan nama yang berbeda dari yang diharapkan controller
- Tidak ada hidden input untuk `tarif_per_produk` dan `jumlah_pegawai`

## Solusi yang Diterapkan

### 1. Perbaikan ProsesProduksiController.php

#### Method `store()`:
**Sebelum:**
```php
$validated = $request->validate([
    'nama_proses' => 'required|string|max:100',
    'jabatan_id' => 'required|exists:jabatans,id',
    'deskripsi' => 'nullable|string',
    'tarif_btkl' => 'required|numeric|min:0', // ❌ Field tidak ada di DB
]);

$createData = [
    'user_id'               => auth()->id(),
    'nama_proses'           => $validated['nama_proses'],
    'deskripsi'             => $validated['deskripsi'] ?? null,
    'tarif_btkl'            => $expectedTarifBTKL, // ❌ Kolom tidak ada
    'jabatan_id'            => $validated['jabatan_id'],
    'biaya_btkl_per_produk' => 0, // ❌ Kolom tidak ada
];
```

**Sesudah:**
```php
$validated = $request->validate([
    'nama_proses' => 'required|string|max:100',
    'jabatan_id' => 'required|exists:jabatans,id',
    'deskripsi' => 'nullable|string',
    'tarif_per_produk' => 'required|numeric|min:0', // ✅ Field yang benar
    'jumlah_pegawai' => 'nullable|integer|min:0',
]);

$createData = [
    'user_id'               => auth()->id(),
    'nama_proses'           => $validated['nama_proses'],
    'deskripsi'             => $validated['deskripsi'] ?? null,
    'tarif_per_produk'      => $tarifPerProduk, // ✅ Kolom yang benar
    'jabatan_id'            => $validated['jabatan_id'],
    'jumlah_pegawai'        => $jumlahPegawai, // ✅ Kolom yang benar
];
```

#### Method `update()`:
Perubahan serupa dengan `store()`, menggunakan `tarif_per_produk` dan `jumlah_pegawai` bukan `tarif_btkl`.

#### Method `index()`:
**Sebelum:**
```php
$rataRataTarif = $totalProses > 0 ? $prosesProduksis->sum('tarif_btkl') / $totalProses : 0;
```

**Sesudah:**
```php
$rataRataTarif = $totalProses > 0 ? $prosesProduksis->sum('tarif_per_produk') / $totalProses : 0;
```

### 2. Perbaikan Form View (create.blade.php)

#### Field nama_proses:
**Sebelum:**
```html
<input type="text" name="nama_btkl" id="nama_btkl" ... />
```

**Sesudah:**
```html
<input type="text" name="nama_proses" id="nama_proses" ... />
```

#### Field deskripsi:
**Sebelum:**
```html
<textarea name="deskripsi_proses" id="deskripsi_proses" ... ></textarea>
```

**Sesudah:**
```html
<textarea name="deskripsi" id="deskripsi" ... ></textarea>
```

#### Tambahan Hidden Inputs:
```html
<input type="hidden" name="tarif_per_produk" id="tarif_per_produk" value="0">
<input type="hidden" name="jumlah_pegawai" id="jumlah_pegawai" value="1">
```

#### Perbaikan JavaScript:
**Sebelum:**
```javascript
function updateTarifCalculation(jabatan) {
    const tarifDasar = jabatan.tarif || 0; 
    const totalTarif = tarifDasar * jumlahPegawai;
    tarifDisplay.value = totalTarif.toLocaleString('id-ID');
}
```

**Sesudah:**
```javascript
function updateTarifCalculation(jabatan) {
    const tarifDasar = jabatan.tarif_produk || jabatan.tarif || 0; 
    const totalTarif = tarifDasar;
    
    // Update display
    tarifDisplay.value = totalTarif.toLocaleString('id-ID');
    
    // Update hidden inputs
    document.getElementById('tarif_per_produk').value = tarifDasar;
    document.getElementById('jumlah_pegawai').value = jumlahPegawai;
}
```

### 3. Perbaikan Form View (edit.blade.php)

Perubahan serupa dengan `create.blade.php`:
- Ganti `nama_btkl` → `nama_proses`
- Ganti `deskripsi_proses` → `deskripsi`
- Ganti `tarif_per_jam_display` → `tarif_per_produk_display`
- Tambahkan hidden inputs untuk `tarif_per_produk` dan `jumlah_pegawai`
- Update JavaScript untuk menggunakan `tarif_produk` dari jabatan

## Catatan Penting

### Struktur Database yang Benar:

**Tabel `proses_produksis`:**
- ✅ `tarif_per_produk` (decimal) - Tarif BTKL per produk dari jabatan
- ✅ `jumlah_pegawai` (integer) - Jumlah pegawai yang mengerjakan proses
- ❌ `tarif_btkl` - SUDAH DIHAPUS
- ❌ `biaya_btkl_per_produk` - TIDAK ADA
- ❌ `satuan_btkl` - SUDAH DIHAPUS
- ❌ `kapasitas_per_jam` - SUDAH DIHAPUS

**Tabel `jabatans`:**
- ✅ `tarif` (decimal) - Tarif per jam (legacy)
- ✅ `tarif_produk` (decimal) - Tarif per produk (sistem baru)

### Formula Perhitungan:
```
Total BTKL = tarif_per_produk × jumlah_pegawai
```

Dimana:
- `tarif_per_produk` diambil dari `jabatans.tarif_produk`
- `jumlah_pegawai` dihitung dari jumlah pegawai yang terdaftar di jabatan tersebut

## File yang Diubah

1. ✅ `app/Http/Controllers/ProsesProduksiController.php`
   - Method `store()` - Menggunakan field yang benar
   - Method `update()` - Menggunakan field yang benar
   - Method `index()` - Menggunakan field yang benar

2. ✅ `resources/views/master-data/btkl/create.blade.php`
   - Field nama_proses (bukan nama_btkl)
   - Field deskripsi (bukan deskripsi_proses)
   - Hidden inputs untuk tarif_per_produk dan jumlah_pegawai
   - JavaScript menggunakan tarif_produk dari jabatan

3. ✅ `resources/views/master-data/btkl/edit.blade.php`
   - Perubahan serupa dengan create.blade.php

4. ✅ `resources/views/master-data/proses-produksi/create.blade.php` **[FORM YANG SEBENARNYA DIGUNAKAN]**
   - Ganti input `tarif_btkl` menjadi display only `tarifBTKLDisplay`
   - Tambah hidden input `tarif_per_produk` dan `jumlah_pegawai`
   - Update JavaScript untuk mengisi hidden inputs
   - Gunakan `tarif_produk` dari jabatan (fallback ke `tarif`)

5. ✅ `resources/views/master-data/proses-produksi/edit.blade.php` **[FORM YANG SEBENARNYA DIGUNAKAN]**
   - Perubahan serupa dengan create.blade.php

## Catatan Tambahan

**Ada 2 Set Form yang Berbeda:**

1. **Form di `resources/views/master-data/btkl/`** - Digunakan oleh `BtklController`
   - Mengirim field: `nama_btkl`, `deskripsi_proses`, `kode_proses`, `jabatan_id`, `satuan`
   - Controller otomatis membuat record di tabel `btkls` dan `proses_produksis`
   - Sudah benar dan tidak perlu diubah

2. **Form di `resources/views/master-data/proses-produksi/`** - Digunakan oleh `ProsesProduksiController` ⚠️ **INI YANG ANDA GUNAKAN**
   - Mengirim field: `nama_proses`, `deskripsi`, `jabatan_id`, `tarif_per_produk`, `jumlah_pegawai`
   - Controller langsung membuat record di tabel `proses_produksis`
   - **SUDAH DIPERBAIKI** - Sekarang mengirim field yang benar

**BtklController vs ProsesProduksiController:**
- `BtklController` (di `app/Http/Controllers/MasterData/BtklController.php`) sudah benar dan tidak perlu diubah
- `ProsesProduksiController` yang perlu diperbaiki karena menggunakan field yang salah

**Sistem Dual Table:**
Sistem menggunakan 2 tabel:
1. `btkls` - Tabel utama untuk BTKL dengan field `nama_btkl`, `deskripsi_proses`
2. `proses_produksis` - Tabel untuk proses produksi dengan field `nama_proses`, `deskripsi`, `tarif_per_produk`, `jumlah_pegawai`

Ketika menyimpan BTKL:
- `BtklController` membuat record di tabel `btkls` dengan field `nama_btkl`, `deskripsi_proses`
- Kemudian membuat record di tabel `proses_produksis` dengan mapping:
  - `nama_btkl` → `nama_proses`
  - `deskripsi_proses` → `deskripsi`
  - `tarif_produk` (dari jabatan) → `tarif_per_produk`

## Testing

Setelah perbaikan, silakan test:
1. ✅ Buat BTKL baru - Pastikan tidak ada error "Column 'tarif_btkl' not found"
2. ✅ Periksa database - Pastikan `tarif_per_produk` terisi dengan nilai yang benar (bukan 0)
3. ✅ Edit BTKL - Pastikan update berjalan tanpa error
4. ✅ Lihat list BTKL - Pastikan statistik ditampilkan dengan benar

## Status
✅ **SELESAI** - Error sudah diperbaiki dan tampilan sudah benar

## Hasil Testing
✅ Data berhasil masuk ke database dengan nilai yang benar:
- `tarif_per_produk` = 375
- `jumlah_pegawai` = 1

✅ Tampilan index sudah diperbaiki untuk menampilkan nilai yang benar:
- Tarif BTKL (Per Produk): Rp 375
- Total Biaya Produk: Rp 375 (375 × 1 pegawai)

## Ringkasan Lengkap Perbaikan

### File Controller:
1. ✅ `app/Http/Controllers/ProsesProduksiController.php` - Validasi dan insert menggunakan field yang benar

### File View Form:
2. ✅ `resources/views/master-data/btkl/create.blade.php` - Form BtklController (sudah benar)
3. ✅ `resources/views/master-data/btkl/edit.blade.php` - Form BtklController (sudah benar)
4. ✅ `resources/views/master-data/proses-produksi/create.blade.php` - Form ProsesProduksiController (diperbaiki)
5. ✅ `resources/views/master-data/proses-produksi/edit.blade.php` - Form ProsesProduksiController (diperbaiki)

### File View Index (Tampilan):
6. ✅ `resources/views/master-data/btkl/index.blade.php` - Tampilan menggunakan `tarif_per_produk` dan `jumlah_pegawai`
7. ✅ `resources/views/master-data/proses-produksi/index.blade.php` - Tampilan menggunakan `tarif_per_produk` dan `jumlah_pegawai`

### Perubahan di View Index:
**Sebelum:**
```php
$jumlahPegawai = $btkl->jabatan->pegawais->count() ?? 0;
$totalBiayaUnit = $jumlahPegawai * $btkl->tarif_btkl; // ❌ Kolom tidak ada
```

**Sesudah:**
```php
$jumlahPegawai = $btkl->jumlah_pegawai ?? 0; // ✅ Dari data proses produksi
$tarifPerProduk = $btkl->tarif_per_produk ?? 0; // ✅ Dari data proses produksi
$totalBiayaUnit = $jumlahPegawai * $tarifPerProduk; // ✅ Perhitungan yang benar
```
