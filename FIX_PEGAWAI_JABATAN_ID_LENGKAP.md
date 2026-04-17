# ✅ Fix Lengkap: Jumlah Pegawai BTKL Menampilkan 0

## 🐛 Masalah Utama

Halaman BTKL create dan edit menampilkan **jumlah pegawai = 0** untuk beberapa jabatan, padahal sebenarnya ada pegawai yang bekerja di jabatan tersebut.

### Contoh Masalah:
```
Jabatan: Penggorengan
Jumlah Pegawai: 0 ❌ (padahal ada Budi Susanto)
Tarif BTKL: Rp 0 ❌
```

## 🔍 Root Cause Analysis

### Masalah 1: Data Pegawai Tidak Lengkap ⚠️

Beberapa pegawai memiliki field `jabatan` (text) tapi **tidak memiliki `jabatan_id`** (foreign key):

| Pegawai | Jabatan (text) | jabatan_id | Status |
|---------|----------------|------------|--------|
| Budi Susanto | Penggorengan | **NULL** ❌ | Tidak terhubung |
| Dedi Gunawan | Bagian Gudang | **NULL** ❌ | Tidak terhubung |
| Ahmad Suryanto | Perbumbuan | 8 ✅ | Terhubung |
| Rina Wijaya | Pengemasan | 3 ✅ | Terhubung |

### Masalah 2: Relasi Model Tidak Berfungsi

Model `Jabatan` menggunakan relasi `hasMany` untuk menghitung pegawai:

```php
// app/Models/Jabatan.php
public function pegawais(): HasMany
{
    return $this->hasMany(Pegawai::class, 'jabatan_id');
}
```

Jika `jabatan_id` NULL, relasi ini **tidak akan menemukan pegawai** tersebut!

### Masalah 3: View Menggunakan Data Yang Salah

Di view, JavaScript menggunakan `$jabatanBtkl` yang tidak memiliki `pegawai_count`:

```javascript
// ❌ SALAH
const employeeData = @json($jabatanBtkl ?? []);
```

Seharusnya menggunakan `$employeeData` yang sudah di-map dengan `pegawai_count`.

## 🔧 Solusi Lengkap

### 1. Fix Data Pegawai (Database)

**Script:** `fix_pegawai_jabatan_id.php`

```php
// Cari pegawai yang jabatan_id nya kosong
$pegawaisWithoutJabatanId = \App\Models\Pegawai::whereNull('jabatan_id')
    ->orWhere('jabatan_id', '')
    ->get();

foreach ($pegawaisWithoutJabatanId as $pegawai) {
    // Cari jabatan berdasarkan nama
    $jabatan = \App\Models\Jabatan::where('nama', $pegawai->jabatan)->first();
    
    if ($jabatan) {
        // Update jabatan_id
        $pegawai->jabatan_id = $jabatan->id;
        $pegawai->save();
    }
}
```

**Hasil:**
```
✓ Budi Susanto: jabatan_id = 21 (Penggorengan)
✓ Dedi Gunawan: jabatan_id = 22 (Bagian Gudang)
```

### 2. Fix Controller (Sudah Dilakukan Sebelumnya)

**File:** `app/Http/Controllers/MasterData/BtklController.php`

Method `create()` sudah benar:
```php
$employeeData = $jabatanBtkl->map(function($jabatan) {
    return [
        'id' => $jabatan->id,
        'nama' => $jabatan->nama,
        'pegawai_count' => $jabatan->pegawais->count(), // ✅ Benar
        'tarif' => $jabatan->tarif ?? 0
    ];
});

return view('master-data.btkl.create', compact('jabatanBtkl', 'nextKode', 'satuanOptions', 'employeeData'));
```

Method `edit()` sudah diperbaiki:
```php
$employeeData = $jabatanBtkl->map(function($jabatan) {
    return [
        'id' => $jabatan->id,
        'nama' => $jabatan->nama,
        'pegawai_count' => $jabatan->pegawais->count(), // ✅ Benar
        'tarif' => $jabatan->tarif ?? 0
    ];
});

return view('master-data.btkl.edit', compact('btkl', 'jabatanBtkl', 'satuanOptions', 'employeeData'));
```

### 3. Fix View (Sudah Dilakukan Sebelumnya)

**File:** `resources/views/master-data/btkl/create.blade.php` dan `edit.blade.php`

```javascript
// ✅ BENAR - menggunakan employeeData
const employeeData = @json($employeeData ?? []);
```

## ✅ Hasil Setelah Fix

### Data Pegawai:

| Pegawai | Jabatan | jabatan_id | Kategori | Status |
|---------|---------|------------|----------|--------|
| Ahmad Suryanto | Perbumbuan | 8 | BTKL | ✅ |
| Rina Wijaya | Pengemasan | 3 | BTKL | ✅ |
| Budi Susanto | Penggorengan | 21 | BTKL | ✅ FIXED |
| Dedi Gunawan | Bagian Gudang | 22 | BTKTL | ✅ FIXED |

### Perhitungan BTKL:

| Jabatan | Tarif/Jam | Jumlah Pegawai | Tarif BTKL |
|---------|-----------|----------------|------------|
| Pengemasan | Rp 17.000 | 1 | Rp 17.000 ✅ |
| Penggorengan | Rp 20.000 | 1 | Rp 20.000 ✅ |
| Perbumbuan | Rp 18.000 | 1 | Rp 18.000 ✅ |

### Contoh Biaya Per Produk (Kapasitas 100 pcs/jam):

| Jabatan | Tarif BTKL | Kapasitas | Biaya/Produk |
|---------|------------|-----------|--------------|
| Pengemasan | Rp 17.000 | 100 pcs | Rp 170/pcs ✅ |
| Penggorengan | Rp 20.000 | 100 pcs | Rp 200/pcs ✅ |
| Perbumbuan | Rp 18.000 | 100 pcs | Rp 180/pcs ✅ |

## 🧪 Testing

### 1. Test di Halaman BTKL Create:

1. Buka: `/master-data/btkl/create`
2. Pilih jabatan "Penggorengan"
3. **Verifikasi:**
   - Jumlah Pegawai: **1** ✅ (bukan 0)
   - Tarif BTKL: **Rp 20.000** ✅ (Rp 20.000 × 1 pegawai)
   - Masukkan kapasitas: 100
   - Biaya per Produk: **Rp 200** ✅ (Rp 20.000 ÷ 100)

### 2. Test di Halaman BTKL Edit:

1. Edit BTKL yang sudah ada
2. Ganti jabatan ke "Penggorengan"
3. **Verifikasi:** Perhitungan otomatis update dengan benar

### 3. Test di Halaman Pegawai:

1. Buka: `/master-data/pegawai`
2. **Verifikasi:** Semua pegawai memiliki jabatan yang benar
3. Edit pegawai "Budi Susanto"
4. **Verifikasi:** Jabatan "Penggorengan" sudah terpilih

## 🔍 Cara Cek Manual

### Cek di Database:

```sql
-- Cek pegawai dengan jabatan_id NULL
SELECT id, nama, jabatan, jabatan_id 
FROM pegawais 
WHERE jabatan_id IS NULL OR jabatan_id = '';

-- Seharusnya: 0 rows (semua sudah terisi)
```

```sql
-- Cek jumlah pegawai per jabatan BTKL
SELECT 
    j.id,
    j.nama as jabatan,
    j.kategori,
    j.tarif,
    COUNT(p.id) as jumlah_pegawai
FROM jabatans j
LEFT JOIN pegawais p ON p.jabatan_id = j.id
WHERE j.kategori = 'btkl'
GROUP BY j.id, j.nama, j.kategori, j.tarif;
```

**Output yang diharapkan:**
```
| id | jabatan      | kategori | tarif | jumlah_pegawai |
|----|--------------|----------|-------|----------------|
| 3  | Pengemasan   | btkl     | 17000 | 1              |
| 8  | Perbumbuan   | btkl     | 18000 | 1              |
| 21 | Penggorengan | btkl     | 20000 | 1              |
```

### Cek di Browser Console:

1. Buka halaman BTKL create/edit
2. Tekan F12 (Developer Tools)
3. Di Console, ketik:

```javascript
console.log(employeeData);
```

**Output yang diharapkan:**
```javascript
[
  {id: 3, nama: "Pengemasan", pegawai_count: 1, tarif: "17000.00"},
  {id: 21, nama: "Penggorengan", pegawai_count: 1, tarif: "20000.00"},
  {id: 8, nama: "Perbumbuan", pegawai_count: 1, tarif: "18000.00"}
]
```

## 🛡️ Pencegahan Masalah Di Masa Depan

### 1. Validasi di Controller Pegawai

Pastikan `jabatan_id` selalu terisi saat create/update pegawai:

```php
// app/Http/Controllers/PegawaiController.php - method store()
$validated = $request->validate([
    'jabatan_id' => 'required|exists:jabatans,id', // ✅ Required
    // ... fields lainnya
]);
```

### 2. Migration untuk Constraint

Buat migration untuk menambah constraint:

```php
Schema::table('pegawais', function (Blueprint $table) {
    $table->foreign('jabatan_id')
          ->references('id')
          ->on('jabatans')
          ->onDelete('restrict'); // Tidak bisa hapus jabatan jika masih ada pegawai
});
```

### 3. Seeder untuk Data Konsisten

Pastikan seeder selalu mengisi `jabatan_id`:

```php
Pegawai::create([
    'nama' => 'John Doe',
    'jabatan_id' => 8, // ✅ Selalu isi
    'jabatan' => 'Perbumbuan', // Untuk backward compatibility
    // ... fields lainnya
]);
```

## 📊 Summary

### Masalah:
1. ❌ Pegawai tidak memiliki `jabatan_id` (NULL)
2. ❌ Relasi `pegawais()` tidak menemukan pegawai
3. ❌ View menggunakan data yang salah

### Solusi:
1. ✅ Fix data pegawai: Set `jabatan_id` berdasarkan nama jabatan
2. ✅ Fix controller: Kirim `$employeeData` ke view
3. ✅ Fix view: Gunakan `$employeeData` di JavaScript

### Hasil:
- ✅ Semua pegawai memiliki `jabatan_id` yang benar
- ✅ Jumlah pegawai ditampilkan dengan benar di BTKL
- ✅ Tarif BTKL dihitung otomatis dengan benar
- ✅ Biaya per produk dihitung dengan benar

---

**Tanggal:** 17 April 2026

**Status:** ✅ **SELESAI - FULLY FIXED**

**File yang Diubah:**
1. Database: `pegawais` table (update `jabatan_id`)
2. `app/Http/Controllers/MasterData/BtklController.php` (method `edit`)
3. `resources/views/master-data/btkl/create.blade.php` (JavaScript)
4. `resources/views/master-data/btkl/edit.blade.php` (JavaScript)

**Script Helper:**
- `check_pegawai_btkl.php` - Cek data pegawai BTKL
- `fix_pegawai_jabatan_id.php` - Fix jabatan_id yang kosong
- `verify_btkl_calculation.php` - Verifikasi perhitungan BTKL
