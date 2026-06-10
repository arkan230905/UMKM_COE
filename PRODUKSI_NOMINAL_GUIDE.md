# 📋 GUIDE: SISTEM PRODUKSI BERBASIS NOMINAL

## 🎯 KONSEP PERUBAHAN

Sistem produksi sekarang menggunakan **NILAI RUPIAH (NOMINAL)** untuk menghitung kebutuhan dan stok Bahan Pendukung, bukan lagi berdasarkan **KUANTITAS (QTY)**.

---

## 📊 CONTOH KASUS

### Sebelum (Sistem Lama - Berdasarkan QTY):
- Stok Susu: **20 bungkus**
- Produksi butuh: **15 bungkus**
- ❌ MASALAH: Tidak memperhitungkan harga satuan yang berbeda-beda

### Sesudah (Sistem Baru - Berdasarkan NOMINAL):
- Stok Susu: **20 bungkus × Rp 20.000 = Rp 400.000**
- Produksi butuh: **Rp 200.000**
- Sisa stok: **Rp 200.000 → dihitung jadi 10 bungkus**

---

## 🔧 CARA KERJA SISTEM BARU

### 1️⃣ **Validasi Stok Sebelum Produksi**

Ketika Anda klik **"Mulai Produksi"**, sistem akan:

```
✅ Cek stok Bahan Pendukung berdasarkan NOMINAL
   
   Untuk setiap bahan:
   - Qty Stok × Harga Satuan = Nominal Tersedia
   - Qty Butuh × Harga Satuan = Nominal Dibutuhkan
   
   Jika Nominal Tersedia < Nominal Dibutuhkan → ❌ GAGAL
```

**Contoh Error Message:**
```
Tidak dapat memulai produksi. 
Bahan yang kurang: Susu, Keju

Detail: 
- Stok Susu tidak cukup. Butuh Rp 200.000, tersedia Rp 150.000
- Stok Keju tidak cukup. Butuh Rp 100.000, tersedia Rp 80.000
```

### 2️⃣ **Pengurangan Stok Saat Produksi**

Setelah validasi berhasil, sistem akan:

```
1. Hitung nominal yang digunakan:
   Nominal = Qty Resep × Harga Satuan

2. Convert nominal ke qty yang dikurangi:
   Qty Dikurangi = Nominal ÷ Harga Satuan

3. Update stok di database:
   Stok Baru = Stok Lama - Qty Dikurangi

4. Catat di Stock Movement:
   - qty: Qty Dikurangi
   - unit_cost: Harga Satuan
   - total_cost: Nominal
   - keterangan: "Produksi [Produk] - Nominal Rp X"
```

---

## 📝 FLOW LENGKAP

### **A. Persiapan Data**

1. **Set Harga Satuan Bahan Pendukung**
   - Masuk ke: Master Data > Bahan Pendukung
   - Pastikan setiap bahan punya **Harga Satuan** yang benar
   - Contoh: Susu = Rp 20.000/bungkus

2. **Set Stok Awal**
   - Stok dicatat dalam **QTY**
   - Sistem akan otomatis hitung **Nominal** saat validasi
   - Contoh: Susu = 20 bungkus → Nominal: Rp 400.000

### **B. Buat Produksi**

1. Pilih produk yang mau diproduksi
2. Sistem akan load **Resep (BOM)** dari HPP
3. Untuk setiap Bahan Pendukung:
   - Qty Resep × Qty Produksi = Total Qty Butuh
   - Total Qty × Harga Satuan = **Total Nominal Butuh**

### **C. Mulai Produksi**

1. Klik **"Mulai Produksi"**
2. Sistem validasi stok berdasarkan **NOMINAL**:
   ```
   ✅ Cek: Nominal Tersedia ≥ Nominal Dibutuhkan
   ```
3. Jika lolos, stok akan dikurangi:
   ```
   Stok Baru = (Stok Lama × Harga) - Nominal Dipakai
             ÷ Harga Satuan
   ```

---

## 💡 KEUNTUNGAN SISTEM BARU

1. **Akurat untuk harga berubah-ubah**
   - Jika harga Susu naik dari Rp 20.000 → Rp 25.000
   - Sistem otomatis adjust dengan harga terbaru

2. **Konsisten dengan akuntansi**
   - Jurnal menggunakan nilai Rupiah
   - Laporan keuangan lebih akurat

3. **Fleksibel untuk multi-satuan**
   - Bisa pakai bungkus, liter, kg, dll
   - Selama harga satuan di-set, nominal akan tepat

---

## 🔍 TROUBLESHOOTING

### ❌ Error: "Stok tidak cukup. Butuh Rp X, tersedia Rp Y"

**Penyebab:**
- Harga satuan bahan pendukung = 0
- Stok aktual kurang dari kebutuhan nominal

**Solusi:**
1. Cek harga satuan:
   ```
   Master Data > Bahan Pendukung > Edit > Harga Satuan
   ```
2. Tambah stok via pembelian:
   ```
   Transaksi > Pembelian > Tambah Pembelian
   ```

### ❌ Stok berkurang terlalu banyak

**Penyebab:**
- Harga satuan di BOM berbeda dengan harga aktual
- Qty resep salah

**Solusi:**
1. Cek HPP:
   ```
   Master Data > Harga Pokok Produksi > Edit
   ```
2. Pastikan harga satuan sesuai dengan harga pembelian terakhir

---

## 📌 FILE YANG DIMODIFIKASI

### 1. `app/Http/Controllers/ProduksiController.php`

**Method: `mulaiProduksi()`**

**Perubahan:**
- ✅ Validasi stok menggunakan **nominal** bukan qty
- ✅ Pengurangan stok berdasarkan **nilai Rupiah**
- ✅ Stock movement mencatat **total_cost** sebagai nominal

**Line yang diubah:**
- Line ~245-260: Validasi stok Bahan Pendukung
- Line ~290-315: Pengurangan stok Bahan Pendukung

---

## 🎓 CATATAN PENTING

1. **Bahan Baku** masih menggunakan sistem **QTY**
   - Karena bahan baku punya konversi satuan kompleks (ekor, potong, kg, gram)
   - Sistem FIFO layers butuh tracking qty

2. **Bahan Pendukung** menggunakan sistem **NOMINAL**
   - Lebih simple, tidak ada konversi satuan kompleks
   - Fokus pada nilai biaya untuk BOP

3. **Barang Jadi** tetap menggunakan **QTY**
   - Stok produk final dalam satuan unit/pcs

---

## 📞 SUPPORT

Jika ada masalah atau pertanyaan, hubungi developer.

---

**Last Updated:** June 10, 2026
**Version:** 2.0 - Nominal Based Production
