# Cara Menghapus Cache Browser untuk Update BOP Proses

## Masalah
Setelah update kode, form "Tambah BOP Proses" masih menampilkan kolom "Jumlah Produksi Produk Per Bulan" padahal sudah dihapus dari kode.

## Penyebab
Browser menyimpan cache dari halaman lama. Perlu dibersihkan untuk melihat perubahan terbaru.

---

## SOLUSI: Hard Refresh Browser

### Cara 1: Hard Refresh (TERCEPAT)
Tekan kombinasi keyboard berikut:

**Windows:**
- Chrome/Edge: `Ctrl + Shift + R` atau `Ctrl + F5`
- Firefox: `Ctrl + Shift + R`

**Mac:**
- Chrome: `Cmd + Shift + R`
- Safari: `Cmd + Option + R`

### Cara 2: Empty Cache and Hard Reload (PALING AMPUH)
1. Buka halaman BOP Proses
2. Buka DevTools (F12)
3. **Klik kanan** pada tombol Refresh di browser
4. Pilih **"Empty Cache and Hard Reload"**

   ```
   ┌─────────────────────────────┐
   │  🔄 [Right Click Here]      │
   │     ↓                        │
   │  ✓ Normal Reload             │
   │  ✓ Hard Reload              │
   │  ✓ Empty Cache and Hard Reload ← PILIH INI
   └─────────────────────────────┘
   ```

### Cara 3: Clear Site Data
1. Buka halaman: `jobcost.eadtmanufaktur.com`
2. Tekan `F12` untuk buka DevTools
3. Klik tab **Application**
4. Di sidebar kiri, pilih **Storage**
5. Klik **"Clear site data"**
6. Refresh halaman (F5)

### Cara 4: Manual Clear Cache
**Chrome/Edge:**
1. Tekan `Ctrl + Shift + Delete`
2. Pilih **Time range**: "Last hour" atau "All time"
3. Centang:
   - ✅ Cached images and files
   - ✅ Cookies and other site data
4. Klik **Clear data**

**Firefox:**
1. Tekan `Ctrl + Shift + Delete`
2. Pilih **Time range**: "Last hour"
3. Centang:
   - ✅ Cache
   - ✅ Cookies
4. Klik **OK**

---

## VERIFIKASI: Form Sudah Benar

Setelah membersihkan cache, form **"Tambah BOP Proses - Bahan Pendukung"** seharusnya menampilkan:

### ✅ Yang HARUS ADA:
1. **Nama BOP Proses** (input text)
2. **Produk** (dropdown select)
   - Pilihan: list produk dari database
3. **Periode (Bulan)** (input month)
   - Format: Juni 2026, Juli 2026, dst
4. **Target Produksi** (alert box - OTOMATIS)
   - Contoh: "Target Produksi: 5.000 unit untuk bulan Juni 2026"
   - Muncul otomatis setelah pilih Produk + Periode
5. **Tabel Bahan Pendukung**
6. **Tabel BOP Proses Lainnya**

### ❌ Yang TIDAK BOLEH ADA:
- ~~**Jumlah Produksi Produk Per Bulan**~~ (input manual) - INI SUDAH DIHAPUS!

---

## Deployment Server (Jika Perlu)

Jika sudah clear browser cache tapi masih muncul, clear cache server:

```bash
cd /var/www/html

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Restart PHP-FPM (optional)
sudo systemctl restart php8.3-fpm
```

---

## Screenshot Form yang Benar

Setelah cache dibersihkan, form seharusnya seperti ini:

```
┌────────────────────────────────────────────────────┐
│ Tambah BOP Proses - Bahan Pendukung                │
├────────────────────────────────────────────────────┤
│                                                     │
│ Nama BOP Proses *                                  │
│ [_______________________________]                  │
│                                                     │
│ Produk *              Periode (Bulan) *            │
│ [-- Pilih Produk --]  [Juni 2026        ▼]        │
│                                                     │
│ ┌────────────────────────────────────────────┐    │
│ │ ℹ️ Target Produksi: 5.000 unit untuk      │    │
│ │    bulan Juni 2026                          │    │
│ └────────────────────────────────────────────┘    │
│                                                     │
│ 📦 Bahan Pendukung                                 │
│ [Tabel Bahan Pendukung]                            │
│ [+ Tambah Bahan Pendukung]                         │
│                                                     │
│ ⚙️ BOP Proses Lainnya                              │
│ [Tabel BOP Lainnya]                                │
│ [+ Tambah Komponen Lainnya]                        │
│                                                     │
│ [💾 Simpan BOP Proses] [← Kembali]                │
└────────────────────────────────────────────────────┘
```

**PERHATIKAN**: Tidak ada input manual "Jumlah Produksi Produk Per Bulan" !

---

## Troubleshooting

### Masalah 1: Masih muncul field "Jumlah Produksi Produk Per Bulan"
**Solusi**:
1. Lakukan **Empty Cache and Hard Reload** (Cara 2)
2. Coba browser lain (Chrome/Firefox/Edge)
3. Coba mode Incognito/Private browsing

### Masalah 2: Target Produksi tidak muncul saat pilih produk
**Solusi**:
1. Pastikan sudah ada data di **Master Data → Target Produksi**
2. Pastikan periode yang dipilih sesuai dengan data target
3. Buka browser console (F12 → Console tab) untuk lihat error
4. Check Laravel log: `/var/www/html/storage/logs/laravel.log`

### Masalah 3: Error saat submit "Target produksi tidak ditemukan"
**Solusi**:
1. Pastikan sudah pilih Produk dan Periode
2. Pastikan target produksi untuk produk tersebut sudah diinput di Master Data
3. Pastikan bulan di periode sesuai dengan data target (misal: target untuk Juni 2026)

---

## Test Workflow yang Benar

### Step 1: Buat Target Produksi Dulu
1. Buka **Master Data → Target Produksi**
2. Klik **Tambah Target Produksi**
3. Pilih **Produk**: "Ayam Goreng Crispy MacDi"
4. Pilih **Tahun**: 2026
5. Input target per bulan:
   - Juni 2026: 5000
   - Juli 2026: 6000
6. Simpan

### Step 2: Buat BOP Proses
1. Buka **Master Data → BOP**
2. Klik **Tambah BOP Proses**
3. Input **Nama BOP Proses**: "Perbandingan Ayam"
4. Pilih **Produk**: "Ayam Goreng Crispy MacDi"
5. Pilih **Periode**: "Juni 2026" (2026-06)
6. **Otomatis muncul**: "Target Produksi: 5.000 unit untuk bulan Juni 2026"
7. Tambah Bahan Pendukung atau Komponen Lainnya
8. Klik **Simpan BOP Proses**
9. ✅ **Berhasil disimpan!**

---

## Kesimpulan

✅ **Kolom "Jumlah Produksi Produk Per Bulan" SUDAH DIHAPUS dari kode**

Jika masih terlihat di browser Anda:
1. Hard refresh browser (Ctrl + Shift + R)
2. Atau Empty Cache and Hard Reload (DevTools → Right click Refresh)
3. Atau Clear browsing data (Ctrl + Shift + Delete)

Setelah cache dibersihkan, form akan menampilkan:
- Produk dropdown
- Periode picker
- Target Produksi (otomatis dari master data)
- TIDAK ADA input manual jumlah produksi

---

**Update**: June 30, 2026  
**Status**: ✅ Kode sudah benar, tinggal clear cache browser
