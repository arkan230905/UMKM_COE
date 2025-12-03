# 🚀 QUICK GUIDE - SISTEM RETUR

## 📌 MENU LOKASI
```
Sidebar > TRANSAKSI > Retur
```

---

## 🔄 RETUR PENJUALAN (Customer Return)

### Kapan Digunakan?
- Pelanggan mengembalikan produk yang sudah dibeli
- Produk rusak/cacat/tidak sesuai
- Pembatalan sebagian pesanan

### Langkah-langkah:
```
1. Klik "Tambah Retur"
2. Pilih "Retur Penjualan"
3. Pilih Kompensasi:
   - Kredit/Nota → Jika pelanggan kredit
   - Refund → Jika pelanggan tunai
4. Pilih Produk dari dropdown
5. Input Qty yang diretur
6. Klik "Simpan Retur"
7. Status otomatis "Approved"
8. Klik "Post" untuk finalisasi
```

### Efek:
- ✅ Stok produk **BERTAMBAH** (masuk gudang)
- ✅ Piutang/Kas **BERKURANG**
- ✅ Penjualan **BERKURANG** (contra revenue)

---

## 🔄 RETUR PEMBELIAN (Supplier Return)

### Kapan Digunakan?
- Mengembalikan bahan baku ke supplier
- Bahan baku rusak/cacat/tidak sesuai
- Pembatalan sebagian pesanan

### Langkah-langkah:
```
1. Klik "Tambah Retur"
2. Pilih "Retur Pembelian"
3. Pilih Kompensasi:
   - Kredit/Nota → Jika pembelian kredit
   - Refund → Jika pembelian tunai
4. Pilih Bahan Baku dari dropdown
5. Input Qty yang diretur
6. Klik "Simpan Retur"
7. Status otomatis "Approved"
8. Klik "Post" untuk finalisasi
```

### Efek:
- ✅ Stok bahan baku **BERKURANG** (keluar gudang)
- ✅ Utang/Kas **BERKURANG**
- ✅ Pembelian **BERKURANG**

---

## 💰 KOMPENSASI

### Kredit/Nota
```
Retur Penjualan:
  Dr. Retur Penjualan
  Cr. Piutang Dagang

Retur Pembelian:
  Dr. Utang Dagang
  Cr. Persediaan
```

### Refund (Tunai)
```
Retur Penjualan:
  Dr. Retur Penjualan
  Cr. Kas

Retur Pembelian:
  Dr. Kas
  Cr. Persediaan
```

---

## 📊 STATUS FLOW

```
Draft → Approved → Posted
  ↓        ↓         ↓
Edit    Post     Final
```

---

## ⚠️ PENTING!

1. **Retur Penjualan** = Produk MASUK gudang
2. **Retur Pembelian** = Bahan Baku KELUAR gudang
3. **Posted** = Tidak bisa edit lagi
4. **Validasi** = Otomatis oleh sistem

---

## 🎯 CONTOH KASUS

### Kasus 1: Pelanggan Return Ayam Rica-Rica
```
Transaksi Awal:
- Jual 100 pcs Ayam Rica-Rica @ Rp 20,000
- Total: Rp 2,000,000

Retur:
- Customer return 10 pcs (rusak)
- Kompensasi: Kredit/Nota

Proses:
1. Buat Retur Penjualan
2. Produk: Ayam Rica-Rica
3. Qty: 10
4. Kompensasi: Kredit/Nota
5. Simpan & Post

Hasil:
- Stok Ayam Rica-Rica +10
- Piutang -Rp 200,000
- Penjualan -Rp 200,000
```

### Kasus 2: Return Bahan Baku ke Supplier
```
Transaksi Awal:
- Beli 50 kg Ayam @ Rp 50,000
- Total: Rp 2,500,000

Retur:
- Return 5 kg (tidak fresh)
- Kompensasi: Refund

Proses:
1. Buat Retur Pembelian
2. Bahan Baku: Ayam
3. Qty: 5
4. Kompensasi: Refund
5. Simpan & Post

Hasil:
- Stok Ayam -5 kg
- Kas +Rp 250,000
- Pembelian -Rp 250,000
```

---

## ✅ CHECKLIST SEBELUM POST

- [ ] Tanggal sudah benar
- [ ] Tipe retur sudah benar (Penjualan/Pembelian)
- [ ] Produk/Bahan Baku sudah benar
- [ ] Qty sudah benar
- [ ] Kompensasi sudah benar
- [ ] Sudah review semua data

---

## 🆘 TROUBLESHOOTING

**Q: Tidak bisa pilih produk?**
A: Pastikan produk sudah ada di Master Data > Produk

**Q: Tidak bisa pilih bahan baku?**
A: Pastikan bahan baku sudah ada di Master Data > Bahan Baku

**Q: Retur tidak muncul di laporan?**
A: Pastikan sudah di-POST, bukan hanya Approved

**Q: Stok tidak berubah?**
A: Pastikan sudah di-POST

**Q: Ingin edit retur yang sudah posted?**
A: Tidak bisa! Buat retur baru untuk koreksi

---

*Sistem retur sudah sempurna, tinggal digunakan dengan benar!* 🎉
