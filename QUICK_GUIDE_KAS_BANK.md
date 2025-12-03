# 🚀 QUICK GUIDE: Sistem Kas & Bank

## 📌 AKUN KAS & BANK YANG TERSEDIA

| Kode | Nama Akun | Gunakan Untuk |
|------|-----------|---------------|
| **1101** | Kas Kecil | Transaksi tunai kecil sehari-hari |
| **1102** | Kas di Bank | Transaksi via bank/transfer |
| **1103** | Kas Lainnya | Kas lain-lain |
| **101** | Kas | Kas umum (backward compatibility) |
| **102** | Bank | Bank umum (backward compatibility) |

---

## 💰 CARA MENGGUNAKAN

### 1. Penjualan Tunai/Transfer

**Langkah:**
1. Buka: **Transaksi → Penjualan → Tambah**
2. Pilih **Metode Pembayaran:**
   - **Tunai** → Uang diterima cash
   - **Transfer Bank** → Uang masuk via transfer
   - **Kredit** → Piutang (tidak perlu pilih akun)
3. Jika Tunai/Transfer, pilih **Terima di:**
   - **1101 (Kas Kecil)** → Jika terima cash di kasir
   - **1102 (Kas di Bank)** → Jika terima via transfer
4. Isi detail produk dan simpan

**Hasil:**
✅ Uang masuk ke akun yang dipilih
✅ Muncul di Laporan Kas Bank
✅ Saldo bertambah

---

### 2. Pembelian Tunai/Transfer

**Langkah:**
1. Buka: **Transaksi → Pembelian → Tambah**
2. Pilih **Metode Pembayaran:**
   - **Tunai** → Bayar cash
   - **Transfer** → Bayar via transfer
   - **Kredit** → Utang (tidak perlu pilih akun)
3. Jika Tunai/Transfer, pilih **Sumber Dana:**
   - **1101 (Kas Kecil)** → Jika bayar dari kas
   - **1102 (Kas di Bank)** → Jika bayar via transfer
4. Isi detail bahan baku dan simpan

**Hasil:**
✅ Uang keluar dari akun yang dipilih
✅ Muncul di Laporan Kas Bank
✅ Saldo berkurang

---

### 3. Pembayaran Beban

**Langkah:**
1. Buka: **Transaksi → Pembayaran Beban → Tambah**
2. Pilih **COA Beban** (misal: Beban Listrik)
3. Pilih **Metode Bayar:**
   - **Cash** → Bayar tunai
   - **Bank** → Bayar via bank
4. Pilih **COA Kas/Bank:**
   - **1101 (Kas Kecil)** → Jika bayar dari kas
   - **1102 (Kas di Bank)** → Jika bayar via bank
5. Isi nominal dan simpan

**Hasil:**
✅ Beban tercatat
✅ Uang keluar dari akun yang dipilih
✅ Muncul di Laporan Kas Bank
✅ Saldo berkurang

---

### 4. Penggajian

**Langkah:**
1. Buka: **Transaksi → Penggajian → Tambah**
2. Pilih **Pegawai**
3. Pilih **Tanggal Penggajian**
4. Pilih **Bayar dari:**
   - **1101 (Kas Kecil)** → Jika bayar cash
   - **1102 (Kas di Bank)** → Jika transfer ke rekening pegawai
5. Isi bonus/potongan (jika ada) dan simpan

**Hasil:**
✅ Gaji tercatat
✅ Uang keluar dari akun yang dipilih
✅ Muncul di Laporan Kas Bank
✅ Saldo berkurang

---

### 5. Pelunasan Utang

**Langkah:**
1. Buka: **Transaksi → Pelunasan Utang**
2. Klik **Bayar** pada pembelian yang ingin dilunasi
3. Pilih **Akun Kas:**
   - **1101 (Kas Kecil)** → Jika bayar cash
   - **1102 (Kas di Bank)** → Jika bayar via transfer
4. Isi jumlah pembayaran dan simpan

**Hasil:**
✅ Utang berkurang
✅ Uang keluar dari akun yang dipilih
✅ Muncul di Laporan Kas Bank
✅ Saldo berkurang

---

## 📊 CARA CEK LAPORAN KAS BANK

**Langkah:**
1. Buka: **Laporan → Kas & Bank**
2. Pilih **Periode** (Start Date - End Date)
3. Klik **Filter** atau **Tampilkan**

**Yang Ditampilkan:**
- **Saldo Awal** → Saldo sebelum periode
- **Transaksi Masuk** → Penjualan, penerimaan, dll
- **Transaksi Keluar** → Pembelian, beban, gaji, dll
- **Saldo Akhir** → Saldo setelah periode

**Fitur:**
- 👁️ **Lihat Detail Masuk** → Klik untuk lihat transaksi masuk
- 👁️ **Lihat Detail Keluar** → Klik untuk lihat transaksi keluar
- 📄 **Export PDF** → Download laporan PDF
- 📊 **Export Excel** → Download laporan Excel

---

## 🔍 CARA CEK DETAIL TRANSAKSI

### Via Laporan Kas Bank:
1. Buka **Laporan → Kas & Bank**
2. Klik tombol **👁️ Lihat Detail Masuk** atau **👁️ Lihat Detail Keluar**
3. Akan muncul popup dengan detail transaksi:
   - Tanggal
   - Nomor Transaksi
   - Jenis Transaksi
   - Keterangan
   - Nominal

### Via Jurnal Umum:
1. Buka **Akuntansi → Jurnal Umum**
2. Filter berdasarkan:
   - **Tanggal**
   - **Ref Type** (misal: expense_payment, sale, purchase)
   - **Akun** (misal: 1101, 1102)
3. Lihat detail debit/kredit setiap transaksi

---

## ⚠️ TIPS PENTING

### 1. Pilih Akun yang Tepat
- **Kas Kecil (1101)** → Untuk transaksi tunai sehari-hari
- **Kas di Bank (1102)** → Untuk transaksi via bank/transfer
- Jangan campur-campur agar laporan jelas

### 2. Cek Saldo Sebelum Transaksi
- Sistem akan validasi saldo otomatis
- Jika saldo tidak cukup, transaksi akan ditolak
- Pastikan saldo mencukupi sebelum input transaksi

### 3. Konsisten dalam Penggunaan
- Jika biasa pakai **1101** untuk kas, terus gunakan **1101**
- Jangan ganti-ganti akun tanpa alasan jelas
- Ini memudahkan tracking dan audit

### 4. Cek Laporan Berkala
- Cek Laporan Kas Bank minimal 1x seminggu
- Pastikan saldo sesuai dengan fisik
- Jika ada selisih, cek detail transaksi

### 5. Backup Data
- Export laporan ke Excel/PDF secara berkala
- Simpan sebagai backup dan dokumentasi
- Berguna untuk audit dan pelaporan

---

## 🐛 TROUBLESHOOTING

### Masalah: Transaksi tidak muncul di Laporan Kas Bank

**Solusi:**
1. Cek akun yang dipilih saat input transaksi
2. Pastikan akun adalah **1101, 1102, 1103, 101, atau 102**
3. Cek periode laporan (Start Date - End Date)
4. Refresh halaman (Ctrl + F5)

### Masalah: Saldo tidak sesuai

**Solusi:**
1. Cek **Jurnal Umum** untuk detail transaksi
2. Pastikan tidak ada transaksi yang salah input
3. Cek **Saldo Awal** di COA
4. Hubungi admin jika masih tidak sesuai

### Masalah: Error "Saldo tidak cukup"

**Solusi:**
1. Cek saldo akun di **Laporan Kas Bank**
2. Pastikan saldo mencukupi untuk transaksi
3. Jika perlu, input transaksi penerimaan dulu
4. Atau gunakan metode **Kredit** untuk utang

### Masalah: Dropdown akun kosong

**Solusi:**
1. Refresh halaman (Ctrl + F5)
2. Logout dan login kembali
3. Hubungi admin untuk cek database

---

## 📞 BANTUAN

Jika ada masalah atau pertanyaan:
1. Cek dokumentasi lengkap: **STANDARDISASI_AKUN_KAS_BANK_FINAL.md**
2. Cek summary: **SUMMARY_PERBAIKAN_KAS_BANK.md**
3. Hubungi admin sistem

---

**Versi:** 1.0
**Tanggal:** 11 November 2025
**Status:** ✅ SIAP DIGUNAKAN
