# 🧪 Panduan Testing Sistem Barcode Scanner

## 📋 Checklist Testing

### ✅ Test 1: Scan Barcode Valid dengan Stok Tersedia

**Langkah:**
1. Buka halaman Tambah Penjualan
2. Pastikan ada produk dengan barcode dan stok > 0 di database
3. Scan barcode produk menggunakan scanner fisik ATAU ketik barcode dan tekan Enter

**Expected Result:**
- ✅ Input scanner langsung clear setelah scan
- ✅ Status indicator berubah menjadi "✓ [Nama Produk]" (hijau)
- ✅ Beep sukses terdengar (single high-pitched beep)
- ✅ Toast notification hijau muncul: "✅ [Nama Produk] ditambahkan | Total: X item"
- ✅ Produk muncul di tabel detail penjualan dengan quantity = 1
- ✅ Baris produk di-highlight dengan warna hijau dan animasi
- ✅ Cart counter bertambah (badge biru di kanan atas)
- ✅ Total pembayaran ter-update otomatis
- ✅ Status indicator kembali ke "Siap Scan" setelah 1.5 detik
- ✅ Focus kembali ke input scanner

**Screenshot:**
```
┌─────────────────────────────────────────────────────────┐
│ Status: ✓ Indomie Goreng (hijau)                        │
│ Toast: ✅ Indomie Goreng ditambahkan | Total: 1 item    │
│ Cart: 🛒 1 item                                          │
└─────────────────────────────────────────────────────────┘
```

---

### ❌ Test 2: Scan Barcode Tidak Valid (Produk Tidak Ada)

**Langkah:**
1. Buka halaman Tambah Penjualan
2. Scan barcode yang tidak ada di database (contoh: 9999999999999)

**Expected Result:**
- ✅ Input scanner langsung clear
- ✅ Status indicator berubah menjadi "Produk tidak ditemukan" (merah)
- ✅ Beep error terdengar (double low-pitched beep)
- ✅ Toast notification merah muncul: "❌ Produk dengan barcode [barcode] tidak ditemukan"
- ✅ Produk TIDAK ditambahkan ke tabel
- ✅ Cart counter TIDAK berubah
- ✅ Status indicator kembali ke "Siap Scan" setelah 2 detik
- ✅ Focus kembali ke input scanner

**Screenshot:**
```
┌─────────────────────────────────────────────────────────┐
│ Status: Produk tidak ditemukan (merah)                  │
│ Toast: ❌ Produk dengan barcode 9999999999999 tidak...  │
│ Cart: 🛒 0 item                                          │
└─────────────────────────────────────────────────────────┘
```

---

### ⚠️ Test 3: Scan Produk dengan Stok Habis

**Langkah:**
1. Buka halaman Tambah Penjualan
2. Pastikan ada produk dengan barcode tapi stok = 0
3. Scan barcode produk tersebut

**Expected Result:**
- ✅ Input scanner langsung clear
- ✅ Status indicator berubah menjadi "Stok habis!" (kuning)
- ✅ Beep error terdengar (double low-pitched beep)
- ✅ Toast notification kuning muncul: "⚠️ [Nama Produk] — stok habis"
- ✅ Produk TIDAK ditambahkan ke tabel
- ✅ Cart counter TIDAK berubah
- ✅ Status indicator kembali ke "Siap Scan" setelah 2 detik

**Screenshot:**
```
┌─────────────────────────────────────────────────────────┐
│ Status: Stok habis! (kuning)                            │
│ Toast: ⚠️ Indomie Goreng — stok habis                   │
│ Cart: 🛒 0 item                                          │
└─────────────────────────────────────────────────────────┘
```

---

### 🔄 Test 4: Scan Produk yang Sudah Ada di Keranjang

**Langkah:**
1. Buka halaman Tambah Penjualan
2. Scan barcode produk A (stok = 10)
3. Scan barcode produk A lagi (produk yang sama)

**Expected Result:**
- ✅ Setelah scan pertama: Produk A masuk ke tabel dengan qty = 1
- ✅ Setelah scan kedua:
  - Quantity produk A bertambah menjadi 2
  - Subtotal produk A ter-update (harga × 2)
  - Baris produk A di-highlight dengan animasi
  - Toast muncul: "✅ [Nama Produk] (2x) - Rp [total]"
  - Cart counter bertambah menjadi 2 item
  - Total pembayaran ter-update

**Screenshot:**
```
┌─────────────────────────────────────────────────────────┐
│ Scan 1: Indomie Goreng (1x) - Rp 3.500                 │
│ Scan 2: Indomie Goreng (2x) - Rp 7.000                 │
│ Toast: ✅ Indomie Goreng (2x) - Rp 7.000                │
│ Cart: 🛒 2 item                                          │
└─────────────────────────────────────────────────────────┘
```

---

### 🔍 Test 5: Pencarian Manual (Ketik Nama Produk)

**Langkah:**
1. Buka halaman Tambah Penjualan
2. Ketik "indo" di input scanner (jangan tekan Enter)
3. Tunggu 200ms

**Expected Result:**
- ✅ Hasil pencarian muncul di bawah input scanner
- ✅ Menampilkan maksimal 10 produk yang cocok
- ✅ Produk yang barcodenya diawali "indo" muncul di atas
- ✅ Produk yang namanya mengandung "indo" muncul di bawah
- ✅ Setiap produk menampilkan:
  - Nama produk (bold)
  - Barcode (dengan highlight jika cocok)
  - Harga (hijau, bold)
  - Badge stok (hijau jika > 0, merah jika habis)
  - Tombol [+ Tambah]
- ✅ Hover pada produk: background berubah biru muda
- ✅ Klik produk: produk ditambahkan ke keranjang

**Screenshot:**
```
┌─────────────────────────────────────────────────────────┐
│ 🔍 Hasil Pencarian                          [3 produk]  │
├─────────────────────────────────────────────────────────┤
│ Indomie Goreng                                          │
│ 8992696311015 • Rp 3.500                    ✓ 100 [+]  │
├─────────────────────────────────────────────────────────┤
│ Indomie Soto                                            │
│ 8992696311022 • Rp 3.500                    ✓ 50  [+]  │
├─────────────────────────────────────────────────────────┤
│ Indomie Ayam Bawang                                     │
│ 8992696311039 • Rp 3.500                    ✓ 75  [+]  │
└─────────────────────────────────────────────────────────┘
```

---

### ⌨️ Test 6: Keyboard Shortcuts

#### Test 6a: F2 untuk Fokus
**Langkah:**
1. Buka halaman Tambah Penjualan
2. Klik di area lain (misalnya tabel)
3. Tekan F2

**Expected Result:**
- ✅ Focus langsung ke input scanner
- ✅ Text di input scanner ter-select semua

#### Test 6b: Escape untuk Clear
**Langkah:**
1. Ketik sesuatu di input scanner
2. Tekan Escape

**Expected Result:**
- ✅ Input scanner ter-clear
- ✅ Hasil pencarian tertutup (jika ada)

#### Test 6c: Arrow Keys untuk Navigasi
**Langkah:**
1. Ketik "indo" untuk memunculkan hasil pencarian
2. Tekan Arrow Down

**Expected Result:**
- ✅ Produk pertama di-highlight (background biru)
- ✅ Tekan Arrow Down lagi: highlight pindah ke produk kedua
- ✅ Tekan Arrow Up: highlight pindah ke produk sebelumnya
- ✅ Tekan Enter: produk yang di-highlight ditambahkan ke keranjang

---

### 🛒 Test 7: Cart Counter

**Langkah:**
1. Buka halaman Tambah Penjualan (cart counter = 0)
2. Scan produk A (qty = 1)
3. Scan produk A lagi (qty = 2)
4. Scan produk B (qty = 1)
5. Ubah quantity produk A menjadi 5 secara manual di tabel
6. Hapus produk B dari tabel

**Expected Result:**
- ✅ Setelah scan pertama: Cart counter = 1 item
- ✅ Setelah scan kedua: Cart counter = 2 item (dengan animasi pulse)
- ✅ Setelah scan produk B: Cart counter = 3 item
- ✅ Setelah ubah qty produk A: Cart counter = 6 item (5 + 1)
- ✅ Setelah hapus produk B: Cart counter = 5 item

---

### 🎨 Test 8: Visual Feedback

**Langkah:**
1. Scan produk valid

**Expected Result:**
- ✅ Baris produk di-highlight dengan:
  - Background color: hijau (#d4edda)
  - Box shadow: 0 0 20px rgba(40, 167, 69, 0.5)
  - Transform: scale(1.02)
- ✅ Animasi berlangsung 600ms
- ✅ Setelah 600ms: kembali normal dengan smooth transition

---

### 🎵 Test 9: Audio Feedback

#### Test 9a: Success Beep
**Langkah:**
1. Scan produk valid

**Expected Result:**
- ✅ Beep terdengar: single high-pitched (1200Hz)
- ✅ Durasi: 150ms
- ✅ Volume: sedang (gain 0.3)

#### Test 9b: Error Beep
**Langkah:**
1. Scan barcode invalid

**Expected Result:**
- ✅ Beep terdengar: double low-pitched
- ✅ First beep: 400Hz, 100ms
- ✅ Second beep: 350Hz, 100ms (delayed 150ms)
- ✅ Total durasi: 300ms

---

### 🔄 Test 10: Auto-focus

**Langkah:**
1. Buka halaman Tambah Penjualan
2. Klik di area lain (misalnya tabel)
3. Tunggu 3 detik

**Expected Result:**
- ✅ Focus otomatis kembali ke input scanner setelah 3 detik
- ✅ Tidak mengganggu jika sedang mengetik di input/select lain

---

### ⚠️ Test 11: Validasi Stok Saat Increment

**Langkah:**
1. Scan produk dengan stok = 5
2. Scan produk yang sama 5 kali (total qty = 5)
3. Scan produk yang sama lagi (qty akan menjadi 6, melebihi stok)

**Expected Result:**
- ✅ Scan 1-5: Berhasil, qty bertambah
- ✅ Scan 6: Gagal dengan toast warning
- ✅ Toast: "⚠️ Stok tidak cukup! Tersedia: 5 | Di keranjang: 5"
- ✅ Quantity tetap 5, tidak bertambah

---

### 📱 Test 12: Responsive Design

**Langkah:**
1. Buka halaman di berbagai ukuran layar:
   - Desktop (1920x1080)
   - Tablet (768x1024)
   - Mobile (375x667)

**Expected Result:**
- ✅ Scanner input tetap terlihat jelas
- ✅ Hasil pencarian tidak overflow
- ✅ Toast notification tidak keluar layar
- ✅ Cart counter tetap terlihat

---

### 🔧 Test 13: Edge Cases

#### Test 13a: Barcode dengan Spasi
**Langkah:**
1. Scan barcode: "8992696311015 " (ada spasi di akhir)

**Expected Result:**
- ✅ Spasi otomatis di-trim
- ✅ Produk tetap ditemukan

#### Test 13b: Barcode dengan Enter di Tengah
**Langkah:**
1. Ketik "8992696"
2. Tekan Enter
3. Ketik "311015"
4. Tekan Enter

**Expected Result:**
- ✅ Scan pertama: Barcode tidak lengkap, tidak ditemukan
- ✅ Scan kedua: Barcode tidak lengkap, tidak ditemukan

#### Test 13c: Multiple Scan Cepat
**Langkah:**
1. Scan 5 produk berbeda secara cepat (interval < 1 detik)

**Expected Result:**
- ✅ Semua produk berhasil ditambahkan
- ✅ Tidak ada produk yang terlewat
- ✅ Toast notification muncul untuk setiap produk

---

## 📊 Test Report Template

```
┌─────────────────────────────────────────────────────────┐
│ BARCODE SCANNER TESTING REPORT                          │
├─────────────────────────────────────────────────────────┤
│ Tanggal: [DD/MM/YYYY]                                   │
│ Tester: [Nama]                                          │
│ Browser: [Chrome/Firefox/Safari/Edge]                   │
│ OS: [Windows/Mac/Linux]                                 │
├─────────────────────────────────────────────────────────┤
│ Test Results:                                           │
│ ✅ Test 1: Scan Barcode Valid                           │
│ ✅ Test 2: Scan Barcode Invalid                         │
│ ✅ Test 3: Scan Produk Stok Habis                       │
│ ✅ Test 4: Scan Produk yang Sudah Ada                   │
│ ✅ Test 5: Pencarian Manual                             │
│ ✅ Test 6: Keyboard Shortcuts                           │
│ ✅ Test 7: Cart Counter                                 │
│ ✅ Test 8: Visual Feedback                              │
│ ✅ Test 9: Audio Feedback                               │
│ ✅ Test 10: Auto-focus                                  │
│ ✅ Test 11: Validasi Stok                               │
│ ✅ Test 12: Responsive Design                           │
│ ✅ Test 13: Edge Cases                                  │
├─────────────────────────────────────────────────────────┤
│ Total: 13/13 Passed (100%)                              │
│ Status: ✅ PRODUCTION READY                             │
└─────────────────────────────────────────────────────────┘
```

---

## 🐛 Bug Report Template

Jika menemukan bug, gunakan template ini:

```
BUG REPORT #[ID]
================

Severity: [Critical/High/Medium/Low]
Status: [Open/In Progress/Resolved]

Description:
[Deskripsi singkat bug]

Steps to Reproduce:
1. [Langkah 1]
2. [Langkah 2]
3. [Langkah 3]

Expected Result:
[Apa yang seharusnya terjadi]

Actual Result:
[Apa yang sebenarnya terjadi]

Screenshots:
[Attach screenshots jika ada]

Environment:
- Browser: [Chrome 120.0]
- OS: [Windows 11]
- Screen Size: [1920x1080]

Console Errors:
[Copy paste error dari console]

Additional Notes:
[Catatan tambahan]
```

---

## ✅ Acceptance Criteria

Sistem dianggap **PRODUCTION READY** jika:

1. ✅ Semua 13 test cases PASSED
2. ✅ Tidak ada critical/high severity bugs
3. ✅ Audio feedback bekerja di semua browser modern
4. ✅ Visual feedback smooth dan tidak lag
5. ✅ Validasi stok 100% akurat
6. ✅ Keyboard shortcuts berfungsi sempurna
7. ✅ Responsive di semua ukuran layar
8. ✅ Performance: scan detection < 100ms
9. ✅ Performance: search results < 300ms
10. ✅ No console errors

---

**Happy Testing! 🎉**
