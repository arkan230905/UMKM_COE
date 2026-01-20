# Fitur CREATE Biaya Bahan - SELESAI

## ✅ Status: COMPLETE

Sistem CREATE untuk biaya bahan telah selesai dibuat dan siap digunakan.

---

## 🎯 Konsep CREATE vs EDIT

### CREATE (Tambah Baru)
- **Kapan**: Ketika produk belum memiliki data biaya bahan (Total = Rp 0)
- **Tombol**: Tombol hijau "Tambah" di halaman index
- **Route**: `master-data/biaya-bahan/create/{id}`
- **Method**: POST ke `store`
- **Warna**: Hijau (success)

### EDIT (Ubah Data)
- **Kapan**: Ketika produk sudah memiliki data biaya bahan (Total > Rp 0)
- **Tombol**: Tombol kuning "Edit" di halaman index
- **Route**: `master-data/biaya-bahan/edit/{id}`
- **Method**: PUT ke `update`
- **Warna**: Kuning (warning)

---

## 📋 Fitur CREATE

### 1. Logika Button di Index
```php
@if($totalBiaya > 0)
    {{-- Jika sudah ada data: tampilkan View, Edit, Delete --}}
    <button>View</button>
    <button>Edit</button>
    <button>Delete</button>
@else
    {{-- Jika belum ada data: tampilkan Tambah --}}
    <button>Tambah</button>
@endif
```

### 2. Halaman CREATE
- **Header**: "Tambah Biaya Bahan" (bukan "Edit")
- **Warna**: Hijau (success)
- **Status**: Badge "Belum Ada Biaya Bahan"
- **Tabel**: Kosong, siap diisi
- **Button**: "Simpan Biaya Bahan" (bukan "Simpan Perubahan")

### 3. Fitur Auto-Add
- Saat halaman dibuka, otomatis menambah 1 baris Bahan Baku
- User langsung bisa input tanpa klik "Tambah Bahan Baku" dulu

### 4. Validasi
- Minimal 1 Bahan Baku wajib diisi
- Bahan Pendukung opsional
- Qty harus > 0
- Satuan harus dipilih

---

## 🔧 File yang Dibuat/Diubah

### 1. Routes (`routes/web.php`)
```php
Route::get('/create/{id}', [BiayaBahanController::class, 'create'])->name('create');
Route::post('/store/{id}', [BiayaBahanController::class, 'store'])->name('store');
```

### 2. Controller (`app/Http/Controllers/BiayaBahanController.php`)
- Method `create($id)` - tampilkan form create
- Method `store(Request $request, $id)` - simpan data baru

### 3. View (`resources/views/master-data/biaya-bahan/create.blade.php`)
- Form create dengan tabel kosong
- JavaScript untuk auto-add, auto-calculate, dll
- Warna hijau untuk theme

### 4. Index (`resources/views/master-data/biaya-bahan/index.blade.php`)
- Logika conditional button (CREATE vs EDIT)

---

## 🚀 Cara Menggunakan

### Langkah 1: Buka Halaman Index
```
http://localhost:8000/master-data/biaya-bahan
```

### Langkah 2: Cari Produk Tanpa Biaya Bahan
- Lihat kolom "Total Biaya Bahan"
- Cari yang nilainya Rp 0
- Tombol aksi akan menampilkan "Tambah" (hijau)

### Langkah 3: Klik Tombol Tambah
- Halaman CREATE akan terbuka
- 1 baris Bahan Baku otomatis ditambahkan

### Langkah 4: Isi Data Bahan Baku
1. Pilih bahan dari dropdown
2. Harga dan satuan otomatis terisi
3. Input qty
4. Subtotal otomatis dihitung

### Langkah 5: Tambah Bahan Lain (Opsional)
- Klik "Tambah Bahan Baku" untuk bahan baku lain
- Klik "Tambah Bahan Pendukung" untuk bahan pendukung

### Langkah 6: Simpan
- Cek ringkasan perhitungan
- Klik "Simpan Biaya Bahan"
- Redirect ke index dengan notifikasi sukses

---

## 📊 Perbedaan CREATE vs EDIT

| Aspek | CREATE | EDIT |
|-------|--------|------|
| **Kondisi** | Total Biaya = Rp 0 | Total Biaya > Rp 0 |
| **Button Index** | Tambah (hijau) | Edit (kuning) |
| **Header** | Tambah Biaya Bahan | Edit Perhitungan Biaya Bahan |
| **Warna Theme** | Hijau (success) | Biru (primary) |
| **Status** | Belum Ada Biaya Bahan | Total Biaya Bahan Saat Ini |
| **Tabel Awal** | Kosong | Berisi data existing |
| **Auto-Add** | Ya (1 baris) | Tidak |
| **Button Submit** | Simpan Biaya Bahan | Simpan Perubahan |
| **Route** | /create/{id} | /edit/{id} |
| **Method** | POST (store) | PUT (update) |
| **Redirect** | Ke index | Ke index |

---

## 🧪 Testing CREATE

### Test 1: Button Logic
1. [ ] Buka halaman index
2. [ ] Cari produk dengan Total Biaya = Rp 0
3. [ ] Tombol aksi hanya menampilkan "Tambah" (hijau)
4. [ ] Tidak ada tombol View, Edit, Delete

### Test 2: Halaman CREATE
1. [ ] Klik tombol "Tambah"
2. [ ] Halaman CREATE terbuka
3. [ ] Header: "Tambah Biaya Bahan"
4. [ ] Warna header: Hijau
5. [ ] Status: "Belum Ada Biaya Bahan"
6. [ ] 1 baris Bahan Baku otomatis ditambahkan

### Test 3: Input Data
1. [ ] Pilih bahan baku dari dropdown
2. [ ] Harga dan satuan otomatis terisi
3. [ ] Input qty: 2
4. [ ] Subtotal otomatis dihitung
5. [ ] Total Bahan Baku otomatis update

### Test 4: Tambah Baris
1. [ ] Klik "Tambah Bahan Baku"
2. [ ] Baris baru ditambahkan
3. [ ] Nomor urut otomatis update
4. [ ] Klik "Tambah Bahan Pendukung"
5. [ ] Baris bahan pendukung ditambahkan

### Test 5: Hapus Baris
1. [ ] Klik tombol trash pada baris
2. [ ] Baris langsung terhapus
3. [ ] Nomor urut otomatis update
4. [ ] Total otomatis update

### Test 6: Simpan Data
1. [ ] Isi minimal 1 bahan baku
2. [ ] Klik "Simpan Biaya Bahan"
3. [ ] Redirect ke index
4. [ ] Notifikasi sukses muncul
5. [ ] Total Biaya Bahan produk sudah > Rp 0
6. [ ] Tombol aksi berubah menjadi View, Edit, Delete

### Test 7: Validasi
1. [ ] Coba simpan tanpa isi bahan baku
2. [ ] Harus muncul error validasi
3. [ ] Coba simpan dengan qty = 0
4. [ ] Harus muncul error validasi

---

## 🔄 Flow Lengkap

```
1. User buka index
   ↓
2. Lihat produk dengan Total = Rp 0
   ↓
3. Klik tombol "Tambah" (hijau)
   ↓
4. Halaman CREATE terbuka
   ↓
5. 1 baris Bahan Baku otomatis ditambahkan
   ↓
6. User pilih bahan, input qty
   ↓
7. Subtotal otomatis dihitung
   ↓
8. User tambah bahan lain (opsional)
   ↓
9. User klik "Simpan Biaya Bahan"
   ↓
10. Data tersimpan ke database:
    - BOM dibuat
    - BomDetail dibuat (Bahan Baku)
    - BomJobCosting dibuat
    - BomJobBahanPendukung dibuat (jika ada)
    - harga_bom produk di-update
   ↓
11. Redirect ke index
   ↓
12. Notifikasi sukses muncul
   ↓
13. Tombol aksi berubah menjadi View, Edit, Delete
```

---

## 💾 Database Changes

Saat CREATE, sistem akan:

1. **Buat BOM** (jika belum ada)
   ```sql
   INSERT INTO boms (produk_id, nama_bom, deskripsi)
   ```

2. **Buat BomDetail** (untuk setiap Bahan Baku)
   ```sql
   INSERT INTO bom_details (bom_id, bahan_baku_id, jumlah, satuan, harga_per_satuan, total_harga)
   ```

3. **Buat BomJobCosting** (jika belum ada)
   ```sql
   INSERT INTO bom_job_costings (produk_id)
   ```

4. **Buat BomJobBahanPendukung** (untuk setiap Bahan Pendukung)
   ```sql
   INSERT INTO bom_job_bahan_pendukungs (bom_job_costing_id, bahan_pendukung_id, jumlah, satuan, harga_satuan, subtotal)
   ```

5. **Update harga_bom Produk**
   ```sql
   UPDATE produks SET harga_bom = [total_biaya_bahan] WHERE id = [produk_id]
   ```

---

## 🐛 Troubleshooting

### Tombol "Tambah" Tidak Muncul
- Cek apakah Total Biaya Bahan = Rp 0
- Hard refresh: `Ctrl + F5`
- Cek view index.blade.php

### Halaman CREATE Error 404
- Cek route: `php artisan route:list --name=biaya-bahan`
- Pastikan route `create` ada
- Cek controller method `create()`

### Data Tidak Tersimpan
- Cek Laravel log: `storage/logs/laravel.log`
- Cari log: "Biaya Bahan Store Request"
- Cek error validasi
- Cek database connection

### Subtotal Tidak Otomatis
- Cek Browser Console (F12)
- Cari error JavaScript
- Hard refresh: `Ctrl + F5`

---

## ✅ Checklist Selesai

- [x] Route create dan store dibuat
- [x] Method create() di controller
- [x] Method store() di controller
- [x] View create.blade.php dibuat
- [x] Logika conditional button di index
- [x] Auto-add 1 baris Bahan Baku
- [x] Auto-fill harga dan satuan
- [x] Auto-calculate subtotal
- [x] Konversi satuan
- [x] Validasi input
- [x] Simpan ke database
- [x] Update harga_bom produk
- [x] Redirect dengan notifikasi
- [x] Logging lengkap

---

## 📝 Contoh Penggunaan

### Skenario: Tambah Biaya Bahan untuk Produk "Nasi Goreng"

1. **Buka Index**
   - Produk "Nasi Goreng" memiliki Total Biaya = Rp 0
   - Tombol aksi: "Tambah" (hijau)

2. **Klik Tambah**
   - Halaman CREATE terbuka
   - 1 baris Bahan Baku otomatis ditambahkan

3. **Isi Bahan Baku**
   - Baris 1: Beras (Rp 12.000/Kilogram), Qty: 0.5, Satuan: Kilogram
   - Subtotal: Rp 6.000
   - Klik "Tambah Bahan Baku"
   - Baris 2: Telur (Rp 2.000/Pieces), Qty: 2, Satuan: Pieces
   - Subtotal: Rp 4.000
   - Total Bahan Baku: Rp 10.000

4. **Isi Bahan Pendukung**
   - Klik "Tambah Bahan Pendukung"
   - Baris 1: Minyak Goreng (Rp 20.000/Liter), Qty: 50, Satuan: Mililiter
   - Subtotal: Rp 1.000 (50ml = 0.05L × Rp 20.000)
   - Total Bahan Pendukung: Rp 1.000

5. **Ringkasan**
   - Total Bahan Baku: Rp 10.000
   - Total Bahan Pendukung: Rp 1.000
   - Total Biaya Bahan: Rp 11.000

6. **Simpan**
   - Klik "Simpan Biaya Bahan"
   - Notifikasi: "Biaya bahan berhasil ditambahkan untuk produk 'Nasi Goreng'."
   - Produk "Nasi Goreng" sekarang memiliki Total Biaya = Rp 11.000
   - Tombol aksi berubah menjadi: View, Edit, Delete

---

**Sistem CREATE Biaya Bahan Siap Digunakan!** 🚀
