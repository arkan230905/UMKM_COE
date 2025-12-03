# ✅ FITUR PILIH SUMBER DANA - PEMBELIAN

## Fitur Baru

### Pilih Sumber Dana saat Pembayaran Tunai/Transfer
User sekarang bisa memilih sumber uang saat melakukan pembelian:
- **Tunai:** Pilih antara Kas Kecil (1101) atau Kas (101)
- **Transfer Bank:** Pilih antara Kas di Bank (1102) atau Bank (102)

---

## Keuntungan

### 1. Kontrol Lebih Baik
✅ User bisa pilih mau pakai uang dari mana  
✅ Tidak otomatis pakai akun dengan saldo terbesar  
✅ Lebih fleksibel sesuai kebutuhan  

### 2. Laporan Lebih Akurat
✅ Transaksi tercatat di akun yang benar  
✅ Saldo kas/bank sesuai dengan kenyataan  
✅ Mudah tracking uang dari mana  

### 3. Validasi Tepat
✅ Cek saldo akun yang dipilih  
✅ Pesan error jelas (nama akun + saldo)  
✅ Tidak bisa bayar jika saldo tidak cukup  

---

## Cara Menggunakan

### 1. Buka Form Pembelian
```
http://127.0.0.1:8000/transaksi/pembelian/create
```

### 2. Pilih Metode Pembayaran
- **Tunai:** Muncul pilihan Kas Kecil (1101) atau Kas (101)
- **Transfer Bank:** Muncul pilihan Kas di Bank (1102) atau Bank (102)
- **Kredit:** Tidak ada pilihan sumber dana (langsung ke Hutang Usaha)

### 3. Pilih Sumber Dana
Contoh:
```
Metode: Tunai
Sumber Dana: Kas (101) ← Pilih ini jika mau pakai uang dari Kas

Atau:

Metode: Transfer Bank
Sumber Dana: Kas di Bank (1102) ← Pilih ini jika mau pakai uang dari Bank
```

### 4. Isi Detail Pembelian
- Vendor, tanggal, bahan baku, jumlah, harga
- Klik Simpan

### 5. Hasil
- Jurnal: Cr [Akun yang dipilih] Rp [Total]
- Saldo akun yang dipilih berkurang
- Muncul di Laporan Kas Bank

---

## Contoh Kasus

### Kasus 1: Bayar dari Kas
```
Saldo:
- Kas (101): Rp 10.000.000
- Kas Kecil (1101): Rp 500.000

Pembelian: Rp 3.000.000
Metode: Tunai
Sumber Dana: Kas (101) ← User pilih ini

Hasil:
✓ Jurnal: Cr 101 (Kas) Rp 3.000.000
✓ Saldo Kas (101) jadi Rp 7.000.000
✓ Kas Kecil (1101) tetap Rp 500.000
```

### Kasus 2: Bayar dari Kas Kecil
```
Saldo:
- Kas (101): Rp 10.000.000
- Kas Kecil (1101): Rp 500.000

Pembelian: Rp 300.000
Metode: Tunai
Sumber Dana: Kas Kecil (1101) ← User pilih ini

Hasil:
✓ Jurnal: Cr 1101 (Kas Kecil) Rp 300.000
✓ Saldo Kas Kecil (1101) jadi Rp 200.000
✓ Kas (101) tetap Rp 10.000.000
```

### Kasus 3: Saldo Tidak Cukup
```
Saldo:
- Kas (101): Rp 10.000.000
- Kas Kecil (1101): Rp 500.000

Pembelian: Rp 3.000.000
Metode: Tunai
Sumber Dana: Kas Kecil (1101) ← User pilih ini

Hasil:
❌ Error: "Saldo Kas Kecil tidak cukup untuk pembelian. 
         Saldo saat ini: Rp 500.000 ; 
         Total pembelian: Rp 3.000.000"
✓ Pembelian tidak jadi
✓ User bisa ganti ke Kas (101) yang saldonya cukup
```

---

## File yang Diubah

### 1. `resources/views/transaksi/pembelian/create.blade.php`
**Tambahan:**
- Field "Sumber Dana" yang muncul saat pilih Tunai/Transfer
- JavaScript untuk show/hide dan update options
- Validasi required jika Tunai/Transfer

### 2. `app/Http/Controllers/PembelianController.php`
**Perubahan:**
- Validasi: Tambah `sumber_dana` required_if Tunai/Transfer
- Cek saldo: Pakai akun yang dipilih user (bukan total semua kas)
- Jurnal: Pakai akun yang dipilih user (bukan otomatis)

---

## Next Steps

Fitur ini baru diterapkan di **Pembelian**. Untuk konsistensi, perlu diterapkan juga di:

### 1. Pembayaran Beban
- Form: `resources/views/transaksi/expense-payment/create.blade.php`
- Controller: `app/Http/Controllers/ExpensePaymentController.php`

### 2. Pelunasan Utang
- Form: `resources/views/transaksi/ap-settlement/create.blade.php`
- Controller: `app/Http/Controllers/ApSettlementController.php`

### 3. Penggajian
- Form: `resources/views/transaksi/penggajian/create.blade.php`
- Controller: `app/Http/Controllers/PenggajianController.php`

### 4. Penjualan (untuk penerimaan kas)
- Form: `resources/views/transaksi/penjualan/create.blade.php`
- Controller: `app/Http/Controllers/PenjualanController.php`

---

## 🎉 SELESAI (Pembelian)!

**Status:** ✅ BERHASIL  
**Fitur:** ✅ Pilih Sumber Dana di Pembelian  
**Tested:** ✅ Show/hide bekerja  
**Validasi:** ✅ Cek saldo akun yang dipilih  
**Jurnal:** ✅ Pakai akun yang dipilih  

Silakan test di form pembelian! Untuk transaksi lain, beri tahu saya jika ingin saya lanjutkan. 🚀
