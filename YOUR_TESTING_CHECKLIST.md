# ✅ YOUR TESTING CHECKLIST

## 📋 Ikuti Checklist Ini Secara Berurutan

Print atau buka file ini saat testing. Centang setiap item setelah selesai.

---

## 🚀 PHASE 1: PERSIAPAN (5 menit)

### [ ] 1.1 Refresh Halaman
- [ ] Buka: `http://127.0.0.1:8000/transaksi/penjualan/create`
- [ ] Tekan: `Ctrl + F5` (hard refresh)
- [ ] Tunggu halaman load sempurna

### [ ] 1.2 Buka Browser Console
- [ ] Tekan: `F12`
- [ ] Klik tab: `Console`
- [ ] Lihat area hitam dengan text

### [ ] 1.3 Cek Console Log
**Harus muncul:**
```
=== BARCODE SCANNER DEBUG ===
Product Data loaded: 2 products
```

- [ ] ✅ Muncul log di atas
- [ ] ❌ Tidak muncul → Baca `DEBUG_INSTRUCTIONS.md` Step 2

**Jika muncul "0 products":**
- [ ] Buka Master Data > Produk
- [ ] Tambahkan barcode ke produk
- [ ] Refresh halaman (Ctrl+F5)
- [ ] Ulangi checklist 1.3

---

## 🧪 PHASE 2: TEST BASIC FUNCTIONS (10 menit)

### [ ] 2.1 Test Input Event
**Cara:**
- [ ] Klik di input scanner
- [ ] Ketik angka: `8`
- [ ] Lihat console

**Harus muncul:**
```
Input event fired! Value: 8 Length: 1
```

- [ ] ✅ Muncul log
- [ ] ❌ Tidak muncul → Baca `DEBUG_INSTRUCTIONS.md` Step 3

---

### [ ] 2.2 Test Ketik Barcode Manual
**Cara:**
- [ ] Ketik di input: `8992000000001`
- [ ] Tekan: `Enter`
- [ ] Lihat console

**Harus muncul di console:**
```
=== processBarcodeValue CALLED ===
✅ PRODUCT FOUND: {id: 1, nama: 'Ayam Crispy Macdi', ...}
```

**Harus terjadi di UI:**
- [ ] Status indicator: "✓ Ayam Crispy Macdi" (hijau)
- [ ] Toast notification hijau muncul
- [ ] Beep sukses terdengar (jika audio enabled)
- [ ] Produk muncul di tabel dengan highlight hijau
- [ ] Cart counter: "🛒 1 item"
- [ ] Total pembayaran ter-update

**Result:**
- [ ] ✅ Semua checklist di atas terpenuhi
- [ ] ❌ Ada yang tidak terjadi → Screenshot console + UI, lalu baca `TROUBLESHOOTING_BARCODE_SCANNER.md`

---

### [ ] 2.3 Test Scan Barcode dengan Scanner Fisik
**Cara:**
- [ ] Arahkan scanner ke barcode produk
- [ ] Tekan trigger scanner
- [ ] Lihat console dan UI

**Harus sama dengan 2.2:**
- [ ] Console log lengkap
- [ ] Status indicator hijau
- [ ] Toast notification
- [ ] Beep sukses
- [ ] Produk masuk tabel
- [ ] Cart counter bertambah

**Result:**
- [ ] ✅ Scanner fisik berfungsi
- [ ] ❌ Tidak berfungsi → Test scanner di Notepad dulu, cek koneksi USB

---

### [ ] 2.4 Test Barcode Invalid
**Cara:**
- [ ] Ketik: `9999999999999`
- [ ] Tekan: `Enter`
- [ ] Lihat console dan UI

**Harus muncul di console:**
```
❌ PRODUCT NOT FOUND
```

**Harus terjadi di UI:**
- [ ] Status indicator: "Produk tidak ditemukan" (merah)
- [ ] Toast notification merah
- [ ] Beep error (double low-pitched)
- [ ] Produk TIDAK masuk tabel
- [ ] Cart counter TIDAK berubah

**Result:**
- [ ] ✅ Error handling berfungsi
- [ ] ❌ Tidak sesuai → Screenshot console + UI

---

### [ ] 2.5 Test Pencarian
**Cara:**
- [ ] Ketik: `ayam`
- [ ] Tunggu 200ms
- [ ] Lihat hasil pencarian di bawah input

**Harus muncul:**
- [ ] Card hasil pencarian
- [ ] List produk yang namanya mengandung "ayam"
- [ ] Setiap produk punya tombol [+ Tambah]

**Test klik produk:**
- [ ] Klik salah satu produk
- [ ] Produk harus masuk ke tabel
- [ ] Cart counter bertambah

**Result:**
- [ ] ✅ Pencarian berfungsi
- [ ] ❌ Tidak muncul hasil → Screenshot console

---

## 🎯 PHASE 3: TEST ADVANCED FEATURES (10 menit)

### [ ] 3.1 Test Auto-increment Quantity
**Cara:**
- [ ] Scan barcode yang sama 2x
- [ ] Lihat tabel

**Harus terjadi:**
- [ ] Scan pertama: Produk masuk dengan qty = 1
- [ ] Scan kedua: Qty bertambah jadi 2 (TIDAK duplikat baris)
- [ ] Toast: "✅ [Nama Produk] (2x) - Rp [total]"
- [ ] Cart counter: 2 item

**Result:**
- [ ] ✅ Auto-increment berfungsi
- [ ] ❌ Duplikat baris → Screenshot

---

### [ ] 3.2 Test Validasi Stok
**Cara:**
- [ ] Cari produk dengan stok kecil (contoh: stok = 2)
- [ ] Scan barcode produk tersebut 3x
- [ ] Lihat apa yang terjadi

**Harus terjadi:**
- [ ] Scan 1-2: Berhasil, qty bertambah
- [ ] Scan 3: Gagal dengan toast warning
- [ ] Toast: "⚠️ Stok tidak cukup! Tersedia: 2 | Di keranjang: 2"
- [ ] Qty tetap 2, tidak bertambah

**Result:**
- [ ] ✅ Validasi stok berfungsi
- [ ] ❌ Bisa melebihi stok → BUG! Screenshot

---

### [ ] 3.3 Test Keyboard Shortcuts
**Test F2:**
- [ ] Klik di area lain (misalnya tabel)
- [ ] Tekan: `F2`
- [ ] Input scanner harus ter-focus

**Test Escape:**
- [ ] Ketik sesuatu di input
- [ ] Tekan: `Escape`
- [ ] Input harus ter-clear

**Test Arrow Keys:**
- [ ] Ketik: `ayam` (muncul hasil pencarian)
- [ ] Tekan: `Arrow Down`
- [ ] Produk pertama harus ter-highlight
- [ ] Tekan: `Enter`
- [ ] Produk harus masuk tabel

**Result:**
- [ ] ✅ F2 berfungsi
- [ ] ✅ Escape berfungsi
- [ ] ✅ Arrow keys berfungsi
- [ ] ❌ Ada yang tidak berfungsi → Screenshot

---

### [ ] 3.4 Test Cart Counter
**Cara:**
- [ ] Hapus semua produk di tabel (cart counter = 0)
- [ ] Scan produk A (qty = 1) → Counter = 1
- [ ] Scan produk A lagi (qty = 2) → Counter = 2
- [ ] Scan produk B (qty = 1) → Counter = 3
- [ ] Ubah qty produk A jadi 5 manual → Counter = 6
- [ ] Hapus produk B → Counter = 5

**Result:**
- [ ] ✅ Cart counter selalu akurat
- [ ] ❌ Counter tidak update → Screenshot

---

### [ ] 3.5 Test Visual Feedback
**Cara:**
- [ ] Scan produk
- [ ] Perhatikan baris produk di tabel

**Harus terjadi:**
- [ ] Baris ter-highlight hijau
- [ ] Ada box shadow
- [ ] Ada animasi scale (sedikit membesar)
- [ ] Setelah 600ms: kembali normal dengan smooth transition

**Result:**
- [ ] ✅ Visual feedback smooth
- [ ] ❌ Tidak ada animasi → Cek browser (mungkin perlu browser modern)

---

### [ ] 3.6 Test Audio Feedback
**Cara:**
- [ ] Scan barcode valid
- [ ] Dengarkan beep

**Harus terdengar:**
- [ ] Beep tunggal, nada tinggi (sukses)

**Test error:**
- [ ] Scan barcode invalid
- [ ] Harus terdengar: Beep ganda, nada rendah (error)

**Result:**
- [ ] ✅ Audio berfungsi
- [ ] ❌ Tidak ada suara → Cek volume browser, atau klik halaman dulu

---

## 🧪 PHASE 4: TEST PAGE (5 menit)

### [ ] 4.1 Buka Test Page
- [ ] Buka: `http://127.0.0.1:8000/test_barcode_scanner.html`

### [ ] 4.2 Cek Test Results
**Test 1: Product Data Loaded**
- [ ] ✅ PASSED: X products loaded

**Test 2: Barcode Scanner Input**
- [ ] Ketik barcode: `8992000000001`
- [ ] Klik: Test
- [ ] ✅ FOUND: Product found!

**Test 3: Search Function**
- [ ] Ketik: `ayam`
- [ ] ✅ FOUND: X product(s) found

**Result:**
- [ ] ✅ Semua test PASSED
- [ ] ❌ Ada yang FAILED → Screenshot

---

## 📊 FINAL REPORT

### Summary:
- Total tests: 20
- Passed: _____ / 20
- Failed: _____ / 20
- Success rate: _____ %

### Status:
- [ ] ✅ PRODUCTION READY (100% passed)
- [ ] ⚠️ NEEDS FIX (80-99% passed)
- [ ] ❌ CRITICAL ISSUES (< 80% passed)

### Bugs Found:
1. _______________________________________________
2. _______________________________________________
3. _______________________________________________

### Screenshots Attached:
- [ ] Console log
- [ ] UI saat scan berhasil
- [ ] UI saat scan gagal
- [ ] Test page results

---

## 📞 Next Steps

### Jika 100% Passed:
- [ ] ✅ Deploy ke production
- [ ] ✅ Training kasir
- [ ] ✅ Monitor usage

### Jika Ada Bugs:
- [ ] Kirim report ke developer dengan:
  - [ ] Checklist ini (yang sudah diisi)
  - [ ] Screenshot console log
  - [ ] Screenshot UI
  - [ ] Deskripsi bug
- [ ] Tunggu fix dari developer
- [ ] Ulangi testing setelah fix

---

## 🎉 Selesai!

Terima kasih sudah melakukan testing dengan teliti!

**Tanggal Testing:** _______________  
**Tester:** _______________  
**Browser:** _______________  
**OS:** _______________

---

**Simpan file ini sebagai bukti testing!**
