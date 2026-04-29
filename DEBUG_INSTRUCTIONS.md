# 🔍 Instruksi Debug untuk User

## 📋 Langkah-langkah Debug

Ikuti langkah ini **SECARA BERURUTAN** untuk menemukan masalah:

---

## Step 1: Buka Browser Console

### Cara Buka Console:
1. Buka halaman **Tambah Penjualan**
2. Tekan tombol **F12** di keyboard
3. Klik tab **Console** (biasanya di atas atau samping)
4. Anda akan melihat area hitam dengan text

### Screenshot Console:
![Console Location](https://via.placeholder.com/800x400?text=Press+F12+%E2%86%92+Click+Console+Tab)

---

## Step 2: Cek Product Data

### Apa yang Harus Muncul:

Di console, harus ada text seperti ini:

```
=== BARCODE SCANNER DEBUG ===
Product Data loaded: 2 products
Product Data keys (barcodes): Array(2)
  0: "8992000000001"
  1: "8992000000002"
Searchable Products loaded: 2 products
First product in searchableProducts: {id: 1, nama: 'Ayam Crispy Macdi', ...}
Products with barcode: 2
Sample products with barcode: Array(2)
```

### ✅ Jika Muncul:
**BAGUS!** Data produk ter-load dengan benar. Lanjut ke Step 3.

### ❌ Jika TIDAK Muncul atau "0 products":
**MASALAH:** Tidak ada produk dengan barcode di database.

**SOLUSI:**
1. Buka **Master Data > Produk**
2. Edit setiap produk
3. Isi field **Barcode** (contoh: `8992000000001`)
4. Klik **Simpan**
5. **Refresh halaman** Tambah Penjualan (tekan Ctrl+F5)
6. Ulangi Step 2

---

## Step 3: Test Ketik Manual

### Cara Test:
1. Klik di input scanner (kotak besar dengan text "Scan barcode...")
2. Ketik angka **8** (satu angka saja)
3. Lihat console

### Apa yang Harus Muncul:

```
Input event fired! Value: 8 Length: 1
Manual typing detected, showing search results...
```

### ✅ Jika Muncul:
**BAGUS!** Input event berfungsi. Lanjut ke Step 4.

### ❌ Jika TIDAK Muncul:
**MASALAH:** JavaScript error atau input tidak ter-detect.

**SOLUSI:**
1. Cek apakah ada text **MERAH** di console (error)
2. Screenshot error tersebut
3. Tekan **Ctrl+F5** untuk hard refresh
4. Ulangi Step 3
5. Jika masih tidak muncul, hubungi IT dengan screenshot

---

## Step 4: Test Scan Barcode

### Cara Test:

**Opsi A: Menggunakan Scanner Fisik**
1. Arahkan scanner ke barcode produk
2. Tekan trigger scanner
3. Lihat console

**Opsi B: Ketik Manual**
1. Ketik barcode lengkap di input (contoh: `8992000000001`)
2. Tekan **Enter**
3. Lihat console

### Apa yang Harus Muncul (Produk Ditemukan):

```
Input event fired! Value: 8992000000001 Length: 13
Looks like barcode (8+ digits), waiting for Enter or timeout...
Keydown event: Enter Current value: 8992000000001
Enter pressed! Processing barcode...
Value to process: 8992000000001
=== processBarcodeValue CALLED ===
Raw barcode input: "8992000000001"
Cleaned barcode: "8992000000001"
Barcode length: 13
Looking up in productData...
Available barcodes in productData: ['8992000000001', '8992000000002']
Direct lookup result: {id: 1, nama: 'Ayam Crispy Macdi', harga: 25000, stok: 160, barcode: '8992000000001'}
✅ PRODUCT FOUND: {id: 1, nama: 'Ayam Crispy Macdi', ...}
Adding product to table...
```

### ✅ Jika Muncul Log Lengkap:
**SEMPURNA!** Scanner berfungsi 100%. Anda harus melihat:
- Status indicator berubah jadi **"✓ Ayam Crispy Macdi"** (hijau)
- Toast notification hijau muncul
- Beep sukses terdengar
- Produk muncul di tabel dengan highlight hijau
- Cart counter bertambah

### ❌ Jika Muncul "PRODUCT NOT FOUND":

```
❌ PRODUCT NOT FOUND
```

**MASALAH:** Barcode tidak ada di database atau tidak match.

**SOLUSI:**
1. Lihat di console: `Available barcodes in productData`
2. Bandingkan dengan barcode yang Anda scan
3. Contoh:
   - Scanner baca: `8992000000001`
   - Database punya: `8992000000002`
   - **TIDAK MATCH!**
4. Perbaiki barcode di **Master Data > Produk**
5. Refresh halaman (Ctrl+F5)
6. Ulangi Step 4

---

## Step 5: Test Pencarian

### Cara Test:
1. Ketik **ayam** di input scanner
2. Tunggu 200ms (sebentar)
3. Harus muncul hasil pencarian di bawah input

### ✅ Jika Muncul Hasil:
**BAGUS!** Pencarian berfungsi.

### ❌ Jika TIDAK Muncul:
**MASALAH:** Search function tidak berfungsi.

**SOLUSI:**
1. Cek console untuk error
2. Screenshot error
3. Hubungi IT

---

## 📸 Screenshot yang Dibutuhkan untuk IT

Jika masih bermasalah, kirim screenshot ini ke IT:

### Screenshot 1: Console Log
![Console Log](https://via.placeholder.com/800x400?text=Screenshot+Console+Log)

**Cara:**
1. Tekan F12
2. Klik tab Console
3. Screenshot seluruh area console

### Screenshot 2: Halaman Saat Scan
![Page Screenshot](https://via.placeholder.com/800x400?text=Screenshot+Page+When+Scanning)

**Cara:**
1. Scan barcode
2. Screenshot seluruh halaman (termasuk status indicator)

### Screenshot 3: Master Data Produk
![Product Data](https://via.placeholder.com/800x400?text=Screenshot+Product+Master+Data)

**Cara:**
1. Buka Master Data > Produk
2. Screenshot list produk (termasuk kolom barcode)

---

## 🧪 Test Page

Buka halaman test untuk verifikasi:

```
http://127.0.0.1:8000/test_barcode_scanner.html
```

Halaman ini akan otomatis test semua fungsi dan menampilkan hasilnya.

### Apa yang Harus Muncul:

**Test 1: Product Data Loaded**
```
✅ PASSED: 2 products loaded
- Barcode: 8992000000001 → Ayam Crispy Macdi (Stok: 160)
- Barcode: 8992000000002 → Ayam Goreng Bundo (Stok: 160)
```

**Test 2: Barcode Scanner Input**
- Ketik barcode di input
- Klik tombol **Test**
- Harus muncul: **FOUND: Product found!**

**Test 3: Search Function**
- Ketik "ayam" di input
- Harus muncul: **FOUND: 2 product(s) found**

---

## 📞 Kapan Harus Hubungi IT?

Hubungi IT jika:

1. ❌ Step 2 gagal (Product Data loaded: 0 products) **DAN** sudah tambah barcode tapi masih 0
2. ❌ Step 3 gagal (Input event tidak muncul) **DAN** sudah refresh tapi masih tidak muncul
3. ❌ Step 4 gagal (Product not found) **DAN** barcode sudah benar tapi masih tidak ketemu
4. ❌ Ada text **MERAH** (error) di console yang tidak hilang setelah refresh

**Jangan lupa kirim 3 screenshot di atas!**

---

## ✅ Checklist Sebelum Hubungi IT

Pastikan sudah coba ini semua:

- [ ] Refresh halaman (Ctrl+F5)
- [ ] Cek barcode di Master Data > Produk
- [ ] Test di halaman test (`/test_barcode_scanner.html`)
- [ ] Screenshot console log
- [ ] Screenshot halaman saat scan
- [ ] Screenshot master data produk
- [ ] Catat barcode yang di-scan

---

## 🎯 Expected Result (Normal)

Jika semua berfungsi normal, ini yang harus terjadi:

1. **Buka halaman** → Console log muncul "Product Data loaded: X products"
2. **Ketik 8** → Console log "Input event fired!"
3. **Scan barcode** → Console log lengkap + produk masuk tabel
4. **Lihat UI** → Status hijau + toast + beep + highlight + cart counter

**Waktu total:** < 5 detik dari scan sampai produk masuk tabel

---

**Semoga berhasil! 🚀**

Jika masih ada masalah, jangan ragu hubungi IT dengan screenshot lengkap.

---

**Dibuat oleh**: Tim IT  
**Tanggal**: 29 April 2026  
**Untuk**: User/Kasir
