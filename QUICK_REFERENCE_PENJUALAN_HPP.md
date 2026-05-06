# 📖 Quick Reference: Penjualan dengan HPP

**Status**: ✅ PRODUCTION READY  
**Last Updated**: May 6, 2026

---

## 🎯 Ringkasan Singkat

Sistem penjualan sekarang **otomatis mencatat jurnal dengan HPP** yang diambil dari `/master-data/harga-pokok-produksi`.

---

## ✅ Yang Sudah Selesai

### 1. HPP Otomatis dari Harga Pokok Produksi
- ✅ HPP dihitung dari: **BBB + BTKL + BOP**
- ✅ Sumber data: `/master-data/harga-pokok-produksi`
- ✅ Otomatis digunakan saat penjualan

### 2. Jurnal Penjualan Otomatis
Setiap penjualan membuat **4 baris jurnal**:
```
Dr. Kas/Bank/Piutang    Rp XXX,XXX
    Cr. Pendapatan                  Rp XXX,XXX
Dr. HPP (554)           Rp YYY,YYY
    Cr. Persediaan (116)            Rp YYY,YYY
```

### 3. Kolom HPP di Halaman Produk
- ✅ Menampilkan nilai HPP yang benar
- ✅ Sinkron dengan perhitungan di harga-pokok-produksi

---

## 🔍 Cara Menggunakan

### Step 1: Input Harga Pokok Produksi
1. Buka `/master-data/harga-pokok-produksi`
2. Pilih **BBB** (Biaya Bahan Baku)
3. Pilih **BTKL** (Biaya Tenaga Kerja Langsung)
4. Pilih **BOP** (Biaya Overhead Pabrik)
5. Lihat **Total HPP** di kolom paling kanan

### Step 2: Buat Penjualan
1. Buka `/transaksi/penjualan/create`
2. Pilih produk dan jumlah
3. Klik "Proses Pembayaran"
4. Pilih metode pembayaran (Cash/Transfer/Credit)
5. Konfirmasi pembayaran

### Step 3: Verifikasi Jurnal
1. Buka database atau laporan jurnal
2. Cari jurnal dengan `tipe_referensi = 'sale'`
3. Pastikan ada 4 baris:
   - Kas/Bank/Piutang (Debit)
   - Pendapatan (Kredit)
   - HPP 554 (Debit) ← **Harus ada!**
   - Persediaan 116 (Kredit)

---

## 📊 Contoh Konkret

### Penjualan Jasuke (2 pcs)
**Data Produk:**
- Nama: Jasuke
- Harga Jual: Rp 10.000/pcs
- HPP (dari harga-pokok-produksi): Rp 5.372/pcs

**Transaksi:**
- Qty: 2 pcs
- Total Penjualan: Rp 20.000
- Total HPP: Rp 10.744 (2 × Rp 5.372)

**Jurnal yang Dibuat:**
| Akun | Kode | Debit | Kredit |
|------|------|-------|--------|
| Kas | 112 | Rp 20.000 | - |
| Pendapatan | 41 | - | Rp 20.000 |
| HPP | 554 | Rp 10.744 | - |
| Persediaan | 116 | - | Rp 10.744 |

**Total Debit**: Rp 30.744  
**Total Kredit**: Rp 30.744  
✅ **BALANCED**

---

## 🔧 Troubleshooting

### Problem: HPP = 0 di jurnal
**Penyebab**: Produk belum ada data di `/master-data/harga-pokok-produksi`

**Solusi**:
1. Buka `/master-data/harga-pokok-produksi`
2. Pilih BBB, BTKL, dan BOP untuk produk tersebut
3. Pastikan Total HPP > 0

### Problem: Kolom HPP di halaman produk = 0
**Penyebab**: Sama seperti di atas

**Solusi**: Input data di harga-pokok-produksi

### Problem: Jurnal tidak dibuat
**Penyebab**: COA tidak ditemukan

**Solusi**: Pastikan COA berikut ada di database:
- 554 - Harga Pokok Penjualan
- 116 - Persediaan Barang Jadi
- 41 - Pendapatan Penjualan
- 112 - Kas
- 111 - Kas Bank
- 113 - Piutang Usaha

---

## 📋 Checklist Verifikasi

Setelah membuat penjualan, cek:

- [ ] Penjualan tersimpan di tabel `penjualans`
- [ ] Detail tersimpan di tabel `penjualan_details`
- [ ] Stok produk berkurang
- [ ] Ada 4 baris di tabel `jurnal_umum` dengan `tipe_referensi = 'sale'`
- [ ] Jurnal balanced (total debit = total kredit)
- [ ] HPP > 0 (jika produk punya data harga-pokok-produksi)
- [ ] Akun HPP menggunakan kode **554** (bukan 560)

---

## 🎯 Akun COA yang Digunakan

| Kode | Nama Akun | Tipe | Digunakan Untuk |
|------|-----------|------|-----------------|
| **554** | Harga Pokok Penjualan | Expense | HPP (Debit) |
| **116** | Persediaan Barang Jadi | Asset | Keluar persediaan (Kredit) |
| **41** | Pendapatan Penjualan | Revenue | Pendapatan (Kredit) |
| **112** | Kas | Asset | Penerimaan cash (Debit) |
| **111** | Kas Bank | Asset | Penerimaan transfer (Debit) |
| **113** | Piutang Usaha | Asset | Penjualan kredit (Debit) |

---

## 📖 Dokumentasi Lengkap

Untuk detail teknis, lihat:
1. `JURNAL_PENJUALAN_COMPLETE.md` - Dokumentasi lengkap
2. `PENJUALAN_JOURNAL_SUMMARY.md` - Ringkasan untuk developer
3. `FIX_PRODUK_HPP_DISPLAY.md` - Fix kolom HPP di halaman produk
4. `VERIFICATION_COMPLETE_IMPLEMENTATION.md` - Verifikasi lengkap

---

## ✅ Kesimpulan

**Sistem sudah siap digunakan!**

Setiap penjualan akan:
1. ✅ Otomatis menghitung HPP dari harga-pokok-produksi
2. ✅ Membuat jurnal lengkap (4 baris)
3. ✅ Menggunakan akun HPP yang benar (554)
4. ✅ Balance (debit = kredit)
5. ✅ Multi-tenant safe

**Tidak ada yang perlu dilakukan lagi - sistem sudah berjalan otomatis!**

---

**Date**: May 6, 2026  
**Status**: ✅ READY TO USE  
**Support**: Check logs at `storage/logs/laravel.log`
