# 📋 Panduan Lengkap: Tambah Biaya Bahan

## 🎯 Fitur Utama

Sistem **Tambah Biaya Bahan** memungkinkan Anda menghitung biaya produksi dengan menambahkan:
1. **Bahan Baku (BBB)** - Bahan utama untuk produksi
2. **Bahan Pendukung/Penolong** - Bahan tambahan yang mendukung produksi

---

## 🚀 Cara Menggunakan

### 1️⃣ Akses Halaman Tambah Biaya Bahan

1. Buka menu **Master Data** → **Biaya Bahan**
2. Cari produk dengan **Total Biaya = Rp 0**
3. Klik tombol **Tambah** (hijau) pada produk tersebut

### 2️⃣ Tambah Bahan Baku (BBB)

#### Langkah-langkah:

1. **Klik tombol "Tambah Bahan Baku"** (biru)
   - Baris baru akan muncul di tabel

2. **Pilih Bahan dari Dropdown**
   - Pilih bahan baku yang dibutuhkan
   - Sistem otomatis menampilkan **Harga Satuan** di kolom

3. **Input Jumlah**
   - Masukkan jumlah yang dibutuhkan (contoh: 10, 0.5, 2.5)
   - Bisa menggunakan desimal

4. **Pilih Satuan**
   - Sistem otomatis memilih satuan yang sesuai dengan bahan
   - Anda bisa mengubahnya jika perlu (contoh: kg → g)

5. **Subtotal Otomatis Terhitung**
   - Sistem langsung menghitung: `Jumlah × Harga Satuan`
   - Jika satuan berbeda, sistem otomatis konversi (kg ↔ g, liter ↔ ml)

#### Contoh:
```
Bahan: Ayam Kampung
Jumlah: 10
Satuan: Kilogram (otomatis terisi)
Harga Satuan: Rp 15.000
Subtotal: Rp 150.000 (otomatis terhitung)
```

#### Konversi Satuan Otomatis:
- **Berat**: kg ↔ g (1 kg = 1000 g)
- **Volume**: liter ↔ ml (1 liter = 1000 ml)
- **Unit**: pcs ↔ unit ↔ pieces (1:1)

### 3️⃣ Tambah Bahan Pendukung/Penolong

Langkah-langkahnya **sama persis** dengan Bahan Baku:

1. Klik tombol **"Tambah Bahan Pendukung"** (cyan/biru muda)
2. Pilih bahan dari dropdown
3. Input jumlah
4. Pilih satuan (otomatis terisi)
5. Subtotal otomatis terhitung

### 4️⃣ Hapus Bahan

Jika salah input atau ingin menghapus:
- Klik tombol **🗑️ (merah)** di kolom Aksi
- Baris akan terhapus
- Total otomatis diperbarui

### 5️⃣ Lihat Total

Sistem menampilkan 3 total:
1. **Total BBB** (footer tabel Bahan Baku - background kuning)
2. **Total Bahan Pendukung** (footer tabel Bahan Pendukung - background cyan)
3. **Total Biaya Bahan** (di bawah - gabungan keduanya)

### 6️⃣ Simpan Data

1. Pastikan semua data sudah benar
2. Klik tombol **"Simpan Biaya Bahan"** (hijau)
3. Sistem akan:
   - Menyimpan semua bahan baku ke database
   - Menyimpan semua bahan pendukung ke database
   - Menghitung total biaya bahan
   - Update `harga_bom` di tabel produk
   - Redirect ke halaman index dengan pesan sukses

---

## 🎨 Tampilan Visual

### Card 1: Informasi Produk
- **Header**: Dark/hitam
- **Isi**: Nama produk, jumlah produk yang dibuat

### Card 2: Bahan Baku (BBB)
- **Header**: Gradient ungu (purple)
- **Background**: Light purple (#f8f4ff)
- **Tabel Header**: Purple (#9f7aea)
- **Footer Total**: Kuning (#fef3c7)

### Card 3: Bahan Pendukung
- **Header**: Gradient cyan/turquoise
- **Background**: Light cyan (#ecfeff)
- **Tabel Header**: Cyan (#22d3ee)
- **Footer Total**: Light cyan (#cffafe)

### Card 4: Summary & Tombol
- **Total Biaya Bahan**: Hijau (success)
- **Tombol Simpan**: Hijau
- **Tombol Batal**: Abu-abu

---

## ✅ Fitur Otomatis

### 1. Auto-Fill Harga Satuan
Saat memilih bahan dari dropdown:
```javascript
✓ Harga satuan otomatis muncul di kolom "Harga Satuan"
✓ Diambil dari master data bahan baku/pendukung
```

### 2. Auto-Select Satuan
Saat memilih bahan:
```javascript
✓ Satuan otomatis terisi sesuai satuan bahan di master data
✓ Contoh: Ayam Kampung (kg) → otomatis pilih "Kilogram"
```

### 3. Auto-Calculate Subtotal
Saat input jumlah atau ubah satuan:
```javascript
✓ Subtotal langsung terhitung
✓ Dengan konversi satuan jika berbeda
✓ Contoh: 500 g × Rp 15.000/kg = Rp 7.500
```

### 4. Auto-Update Total
Setiap perubahan:
```javascript
✓ Total BBB diperbarui
✓ Total Bahan Pendukung diperbarui
✓ Total Biaya Bahan diperbarui
```

---

## 🔧 Teknologi

### Frontend (JavaScript)
```javascript
- addEventListener untuk semua tombol dan input
- Real-time calculation
- Unit conversion logic
- DOM manipulation untuk tambah/hapus baris
```

### Backend (Laravel)
```php
- Validation untuk semua input
- UnitConverter class untuk konversi satuan
- Transaction untuk data integrity
- Logging untuk debugging
```

### Database
```sql
- bom_details (untuk bahan baku)
- bom_job_bahan_pendukungs (untuk bahan pendukung)
- bom_job_costings (untuk job costing)
- produks.harga_bom (update otomatis)
```

---

## 📊 Contoh Perhitungan

### Produk: Ayam Pop (10 porsi)

#### Bahan Baku:
| Bahan | Jumlah | Satuan | Harga/Satuan | Subtotal |
|-------|--------|--------|--------------|----------|
| Ayam Kampung | 10 | kg | Rp 15.000 | Rp 150.000 |
| Bumbu Balado | 10 | g | Rp 300 | Rp 3.000 |
| **Total BBB** | | | | **Rp 153.000** |

#### Bahan Pendukung:
| Bahan | Jumlah | Satuan | Harga/Satuan | Subtotal |
|-------|--------|--------|--------------|----------|
| Garam | 7 | g | Rp 200 | Rp 1.400 |
| Minyak Goreng | 30 | ml | Rp 30 | Rp 900 |
| **Total Bahan Pendukung** | | | | **Rp 2.300** |

#### Total Biaya Bahan: **Rp 155.300**

---

## 🐛 Troubleshooting

### Tombol "Tambah Bahan" tidak berfungsi?
```bash
✓ Hard refresh: Ctrl + F5
✓ Cek console browser (F12) untuk error
✓ Pastikan JavaScript tidak di-block
```

### Subtotal tidak terhitung?
```bash
✓ Pastikan pilih bahan dari dropdown
✓ Pastikan input jumlah > 0
✓ Pastikan pilih satuan
✓ Cek console untuk error
```

### Data tidak tersimpan?
```bash
✓ Cek laravel.log untuk error
✓ Pastikan semua field required terisi
✓ Cek koneksi database
```

---

## 📝 Catatan Penting

1. **Minimal 1 Bahan**: Harus ada minimal 1 bahan baku atau bahan pendukung
2. **Validasi**: Semua field wajib diisi (bahan, jumlah, satuan)
3. **Konversi Satuan**: Sistem otomatis konversi jika satuan berbeda
4. **Update Otomatis**: `harga_bom` produk otomatis diperbarui setelah simpan
5. **Logging**: Semua operasi tercatat di `storage/logs/laravel.log`

---

## 🎓 Tips & Trik

1. **Gunakan Konversi Satuan**
   - Input dalam satuan apapun (g, kg, ml, liter)
   - Sistem otomatis konversi ke satuan base

2. **Tambah Banyak Bahan Sekaligus**
   - Klik "Tambah Bahan" berkali-kali
   - Isi semua baris
   - Simpan sekali saja

3. **Cek Total Real-time**
   - Total selalu update otomatis
   - Tidak perlu klik tombol hitung

4. **Hapus Bahan yang Salah**
   - Langsung klik tombol hapus (merah)
   - Tidak perlu konfirmasi

---

## ✨ Keunggulan Sistem

✅ **User-Friendly**: Interface intuitif dan mudah digunakan
✅ **Real-time**: Perhitungan otomatis tanpa reload
✅ **Flexible**: Konversi satuan otomatis
✅ **Accurate**: Perhitungan presisi dengan desimal
✅ **Fast**: Tambah banyak bahan sekaligus
✅ **Reliable**: Validasi dan error handling lengkap

---

## 📞 Bantuan

Jika mengalami masalah:
1. Cek file log: `storage/logs/laravel.log`
2. Cek console browser (F12)
3. Hard refresh (Ctrl + F5)
4. Hubungi administrator sistem

---

**Sistem Biaya Bahan - UMKM COE**
*Versi 1.0 - Januari 2026*
