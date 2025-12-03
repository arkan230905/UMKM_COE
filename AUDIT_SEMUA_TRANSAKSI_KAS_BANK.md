# 🔍 AUDIT SEMUA TRANSAKSI KAS & BANK

## Tujuan
Memastikan SEMUA transaksi yang melibatkan kas/bank tercatat dengan benar di Laporan Kas dan Bank.

---

## Daftar Transaksi yang Melibatkan Kas/Bank

### 1. ✅ PEMBELIAN (PembelianController)
**Status:** SUDAH DIPERBAIKI
- Tunai: Pakai sumber dana yang dipilih user (1101, 101, 1102, 102)
- Transfer: Pakai sumber dana yang dipilih user
- Kredit: Tidak pakai kas (pakai Hutang Usaha 2101)

**Jurnal:**
```
Dr 1104 (Persediaan Bahan Baku)
Cr [Sumber Dana yang dipilih] atau 2101 (Hutang)
```

---

### 2. ⚠️ PENJUALAN (PenjualanController)
**Status:** PERLU DICEK
- Tunai: Harus masuk ke akun kas yang dipilih
- Transfer: Harus masuk ke akun bank yang dipilih
- Kredit: Tidak pakai kas (pakai Piutang Usaha)

**Jurnal yang Benar:**
```
Penjualan:
Dr [Sumber Dana] atau Piutang
Cr 4101 (Pendapatan)

HPP:
Dr 5001 (HPP)
Cr 1107 (Persediaan Barang Jadi)
```

**Yang Perlu Ditambahkan:**
- Field "Sumber Dana" di form penjualan
- Validasi saldo jika tunai/transfer
- Jurnal pakai akun yang dipilih

---

### 3. ⚠️ PEMBAYARAN BEBAN (ExpensePaymentController)
**Status:** PERLU DICEK
- Tunai: Harus keluar dari akun kas yang dipilih
- Transfer: Harus keluar dari akun bank yang dipilih

**Jurnal yang Benar:**
```
Dr [Akun Beban]
Cr [Sumber Dana yang dipilih]
```

**Yang Perlu Ditambahkan:**
- Field "Sumber Dana" di form
- Validasi saldo
- Jurnal pakai akun yang dipilih

---

### 4. ⚠️ PELUNASAN UTANG (ApSettlementController)
**Status:** PERLU DICEK
- Tunai: Harus keluar dari akun kas yang dipilih
- Transfer: Harus keluar dari akun bank yang dipilih

**Jurnal yang Benar:**
```
Dr 2101 (Hutang Usaha)
Cr [Sumber Dana yang dipilih]
```

**Yang Perlu Ditambahkan:**
- Field "Sumber Dana" di form
- Validasi saldo
- Jurnal pakai akun yang dipilih

---

### 5. ⚠️ PENGGAJIAN (PenggajianController)
**Status:** PERLU DICEK
- Pembayaran gaji harus keluar dari akun kas yang dipilih

**Jurnal yang Benar:**
```
Dr 2103 (Hutang Gaji BTKL) atau 2104 (Hutang Gaji BTKTL)
Cr [Sumber Dana yang dipilih]
```

**Yang Perlu Ditambahkan:**
- Field "Sumber Dana" di form
- Validasi saldo
- Jurnal pakai akun yang dipilih

---

### 6. ✅ PRODUKSI (ProduksiController)
**Status:** TIDAK PERLU PERBAIKAN
- Produksi tidak langsung melibatkan kas/bank
- Hanya mencatat biaya produksi ke hutang gaji dan BOP

**Jurnal:**
```
Dr 1105 (Barang Dalam Proses)
Cr 1104 (Bahan Baku)
Cr 2103 (Hutang Gaji BTKL)
Cr 2104 (Hutang BOP)

Dr 1107 (Barang Jadi)
Cr 1105 (Barang Dalam Proses)
```

---

### 7. ✅ RETUR (ReturController)
**Status:** PERLU DICEK TAPI TIDAK PRIORITAS
- Retur penjualan: Kas keluar (refund)
- Retur pembelian: Kas masuk (refund dari vendor)

**Catatan:** Retur biasanya tidak langsung kas, tapi mengurangi piutang/hutang

---

## Prioritas Perbaikan

### HIGH PRIORITY (Sering Dipakai)
1. **PENJUALAN** - Penerimaan kas harus akurat
2. **PEMBAYARAN BEBAN** - Pengeluaran kas harus akurat
3. **PELUNASAN UTANG** - Pengeluaran kas harus akurat

### MEDIUM PRIORITY
4. **PENGGAJIAN** - Pembayaran gaji harus akurat

### LOW PRIORITY
5. **RETUR** - Jarang terjadi, bisa nanti

---

## Rencana Perbaikan

### Step 1: Penjualan ✅
- Tambah field "Sumber Dana" untuk penerimaan tunai/transfer
- Validasi tidak perlu (penerimaan kas tidak perlu cek saldo)
- Update jurnal pakai akun yang dipilih

### Step 2: Pembayaran Beban ✅
- Tambah field "Sumber Dana"
- Validasi saldo
- Update jurnal pakai akun yang dipilih

### Step 3: Pelunasan Utang ✅
- Tambah field "Sumber Dana"
- Validasi saldo
- Update jurnal pakai akun yang dipilih

### Step 4: Penggajian ✅
- Tambah field "Sumber Dana"
- Validasi saldo
- Update jurnal pakai akun yang dipilih

---

## Checklist Untuk Setiap Transaksi

### Form (View)
- [ ] Ada field "Metode Pembayaran" (Tunai/Transfer/Kredit)
- [ ] Ada field "Sumber Dana" (muncul jika Tunai/Transfer)
- [ ] JavaScript show/hide sumber dana
- [ ] Options sumber dana sesuai metode (Kas untuk Tunai, Bank untuk Transfer)

### Controller
- [ ] Validasi: `sumber_dana` required_if Tunai/Transfer
- [ ] Validasi saldo: Cek saldo akun yang dipilih
- [ ] Jurnal: Pakai akun yang dipilih user
- [ ] Error message: Tampilkan nama akun dan saldo

### Testing
- [ ] Test dengan saldo cukup → Berhasil
- [ ] Test dengan saldo tidak cukup → Error dengan pesan jelas
- [ ] Test muncul di Laporan Kas Bank → Transaksi tercatat
- [ ] Test saldo berkurang/bertambah → Saldo akurat

---

## Status Saat Ini

### ✅ SELESAI
1. **Pembelian** - Sudah ada pilihan sumber dana

### ⏳ BELUM
2. **Penjualan** - Perlu ditambahkan
3. **Pembayaran Beban** - Perlu ditambahkan
4. **Pelunasan Utang** - Perlu ditambahkan
5. **Penggajian** - Perlu ditambahkan

---

## Next Action

Saya akan perbaiki satu per satu dengan urutan prioritas:
1. Penjualan (HIGH)
2. Pembayaran Beban (HIGH)
3. Pelunasan Utang (HIGH)
4. Penggajian (MEDIUM)

Setiap perbaikan akan:
- ✅ Tidak merusak fitur yang sudah ada
- ✅ Konsisten dengan perbaikan Pembelian
- ✅ Tested sebelum lanjut ke berikutnya

---

## 🎯 TUJUAN AKHIR

**Semua transaksi kas/bank tercatat dengan benar di Laporan Kas dan Bank!**

Tidak ada lagi:
- ❌ Transaksi hilang
- ❌ Saldo tidak akurat
- ❌ Validasi salah
- ❌ Jurnal ke akun yang salah

Semua:
- ✅ Transaksi tercatat
- ✅ Saldo akurat
- ✅ Validasi tepat
- ✅ Jurnal benar
