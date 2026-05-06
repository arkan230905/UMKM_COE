# 🚀 Quick Start: Barcode Scanner

## 📦 Persiapan (5 Menit)

### 1. Pastikan Produk Memiliki Barcode

**Buka:** Master Data > Produk

**Cek:** Setiap produk harus punya barcode (contoh: `8992000000001`)

**Jika belum ada barcode:**
1. Klik **Edit** pada produk
2. Isi field **Barcode** dengan angka unik (minimal 8 digit)
3. Klik **Simpan**

**Tips:** Gunakan format EAN-13 (13 digit) untuk barcode standar Indonesia

---

### 2. Test Barcode Scanner Hardware

**Hubungkan scanner** ke komputer via USB

**Test scanner:**
1. Buka Notepad
2. Scan barcode produk
3. Harus muncul angka barcode di Notepad
4. Jika tidak muncul, cek koneksi USB atau driver scanner

---

## 🎯 Cara Menggunakan (3 Langkah)

### Langkah 1: Buka Halaman Penjualan

```
Menu: Transaksi > Penjualan > Tambah Penjualan
URL: http://127.0.0.1:8000/transaksi/penjualan/create
```

---

### Langkah 2: Scan Barcode

**Cara 1: Menggunakan Scanner Fisik**
1. Arahkan scanner ke barcode produk
2. Tekan trigger scanner
3. **BEEP!** ✅ Produk langsung masuk ke keranjang
4. Scan produk berikutnya

**Cara 2: Ketik Manual**
1. Ketik barcode di input scanner (contoh: `8992000000001`)
2. Tekan **Enter**
3. Produk masuk ke keranjang

**Cara 3: Pencarian**
1. Ketik nama produk (contoh: `ayam`)
2. Pilih produk dari hasil pencarian
3. Klik tombol **[+ Tambah]**

---

### Langkah 3: Proses Pembayaran

1. Cek total di kanan bawah
2. Klik tombol **Bayar**
3. Pilih metode pembayaran (Tunai/Transfer)
4. Masukkan jumlah uang diterima
5. Klik **Proses Transaksi**
6. **SELESAI!** 🎉

---

## 🎨 Memahami Status Indicator

### 🟢 Hijau: "Siap Scan"
Scanner siap menerima barcode. Silakan scan produk.

### 🟡 Kuning: "Memproses..."
Scanner sedang memproses barcode. Tunggu sebentar.

### 🟢 Hijau: "✓ Nama Produk"
**SUKSES!** Produk berhasil ditambahkan ke keranjang.

### 🔴 Merah: "Produk tidak ditemukan"
**ERROR!** Barcode tidak valid atau produk tidak ada di database.

### 🟡 Kuning: "Stok habis!"
**WARNING!** Produk ditemukan tapi stok habis.

---

## 🔊 Memahami Beep Sound

### 🔔 Beep Tunggal (High-Pitched)
**SUKSES!** Produk berhasil ditambahkan.  
*Suara: "Beep~" (1x, nada tinggi)*

### 🔔 Beep Ganda (Low-Pitched)
**ERROR!** Produk tidak ditemukan atau stok habis.  
*Suara: "Beep-beep" (2x, nada rendah)*

---

## 💡 Tips & Trik

### Tip 1: Gunakan Keyboard Shortcut
- **F2** = Fokus ke input scanner
- **Escape** = Clear input
- **Arrow Down/Up** = Navigasi hasil pencarian
- **Enter** = Pilih produk

### Tip 2: Scan Cepat
Scan produk secara berurutan tanpa jeda. Sistem akan otomatis menambahkan semua produk.

### Tip 3: Cek Cart Counter
Badge biru di kanan atas menampilkan total item di keranjang:
```
🛒 5 item
```

### Tip 4: Produk Sama = Quantity Bertambah
Jika scan produk yang sama 2x, quantity otomatis bertambah (tidak duplikat baris).

### Tip 5: Gunakan Pencarian untuk Barcode Rusak
Jika barcode rusak/tidak terbaca, ketik nama produk untuk mencari.

---

## ⚠️ Troubleshooting Cepat

### ❌ Scanner tidak mendeteksi barcode?
1. Tekan **F2** untuk fokus ke input
2. Test scan di Notepad dulu
3. Cek koneksi USB scanner

### ❌ Produk tidak ditemukan?
1. Cek barcode di Master Data > Produk
2. Pastikan barcode sama persis
3. Refresh halaman (Ctrl+F5)

### ❌ Tidak ada beep?
1. Cek volume browser
2. Klik di halaman dulu
3. Scan lagi

### ❌ Input tidak fokus?
1. Tekan **F2**
2. Klik tombol refresh (🔄) di input scanner

---

## 📊 Contoh Workflow Kasir

### Skenario: Pelanggan beli 3 produk

**Produk:**
1. Ayam Crispy Macdi (2x)
2. Ayam Goreng Bundo (1x)

**Langkah:**
1. Scan barcode Ayam Crispy → **BEEP!** ✅
2. Scan barcode Ayam Crispy lagi → **BEEP!** ✅ (Qty jadi 2)
3. Scan barcode Ayam Goreng → **BEEP!** ✅
4. Cek total: Rp 55.333
5. Klik **Bayar**
6. Pilih **Tunai**
7. Input: Rp 60.000
8. Kembalian: Rp 4.667
9. Klik **Proses Transaksi**
10. **SELESAI!** Print struk

**Waktu:** ~30 detik ⚡

---

## 🎯 Best Practices

### ✅ DO (Lakukan)
- Scan barcode dengan jarak 5-15 cm
- Pastikan barcode bersih dan tidak rusak
- Cek cart counter setelah scan
- Verifikasi total sebelum bayar
- Gunakan F2 jika focus hilang

### ❌ DON'T (Jangan)
- Jangan scan terlalu cepat (tunggu beep)
- Jangan scan barcode yang rusak/blur
- Jangan lupa cek stok sebelum scan
- Jangan tutup halaman sebelum selesai transaksi

---

## 📞 Butuh Bantuan?

### Level 1: Self-Help
1. Baca `README_BARCODE_SCANNER.md`
2. Cek `TROUBLESHOOTING_BARCODE_SCANNER.md`
3. Test di `http://127.0.0.1:8000/test_barcode_scanner.html`

### Level 2: Debug
1. Tekan F12 → Console
2. Screenshot error
3. Jalankan `php check_barcode_products.php`

### Level 3: Contact Support
Hubungi IT/Developer dengan:
- Screenshot console log
- Screenshot halaman
- Barcode yang di-scan
- Output `check_barcode_products.php`

---

## 🎓 Video Tutorial (Coming Soon)

- [ ] Cara setup barcode scanner
- [ ] Cara scan produk
- [ ] Cara handle error
- [ ] Tips & tricks kasir profesional

---

## ✅ Checklist Harian Kasir

**Sebelum Buka Toko:**
- [ ] Test scanner di Notepad
- [ ] Buka halaman Tambah Penjualan
- [ ] Test scan 1 produk
- [ ] Cek beep dan notifikasi
- [ ] Siap melayani pelanggan! 🎉

**Setelah Tutup Toko:**
- [ ] Cek laporan penjualan hari ini
- [ ] Verifikasi total transaksi
- [ ] Backup data (jika perlu)

---

**Selamat menggunakan sistem barcode scanner! 🚀**

---

**Dibuat oleh**: Tim IT  
**Tanggal**: 29 April 2026  
**Versi**: 1.0.0  
**Status**: ✅ Production Ready
