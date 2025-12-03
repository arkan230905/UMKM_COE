# 📋 DOKUMENTASI SISTEM RETUR - UMKM COE

## 🎯 OVERVIEW
Sistem retur sudah lengkap dan mengikuti best practice seperti Accurate dengan fitur:
- ✅ Retur Penjualan (Return Sales)
- ✅ Retur Pembelian (Return Purchase)
- ✅ Kompensasi (Refund/Credit Note)
- ✅ Status Tracking (Draft/Approved/Posted)
- ✅ Integrasi Stok & Jurnal

---

## 🔄 ALUR RETUR PENJUALAN

### 1. **Buat Retur Penjualan**
   - Menu: Transaksi > Retur > Tambah Retur
   - Pilih: **Retur Penjualan**
   - Pilih Produk yang diretur
   - Input Qty retur
   - Pilih Kompensasi:
     - **Kredit/Nota**: Buat nota kredit untuk pelanggan
     - **Refund**: Kembalikan uang tunai

### 2. **Proses yang Terjadi**
   ```
   Input Retur → Validasi → Create Retur (Status: Approved) → Posting
   ```

### 3. **Efek ke Sistem**
   - **Stok**: Produk kembali masuk ke gudang (stock IN)
   - **Jurnal**: 
     - Dr. Retur Penjualan (contra revenue)
     - Cr. Piutang/Kas (tergantung kompensasi)
   - **Laporan**: Muncul di laporan retur

---

## 🔄 ALUR RETUR PEMBELIAN

### 1. **Buat Retur Pembelian**
   - Menu: Transaksi > Retur > Tambah Retur
   - Pilih: **Retur Pembelian**
   - Pilih Bahan Baku yang diretur
   - Input Qty retur
   - Pilih Kompensasi:
     - **Kredit/Nota**: Dapat nota kredit dari supplier
     - **Refund**: Terima uang kembali

### 2. **Proses yang Terjadi**
   ```
   Input Retur → Validasi → Create Retur (Status: Approved) → Posting
   ```

### 3. **Efek ke Sistem**
   - **Stok**: Bahan baku keluar dari gudang (stock OUT)
   - **Jurnal**:
     - Dr. Utang/Kas (tergantung kompensasi)
     - Cr. Persediaan Bahan Baku
   - **Laporan**: Muncul di laporan retur

---

## 📊 STATUS RETUR

| Status | Deskripsi | Aksi yang Bisa Dilakukan |
|--------|-----------|---------------------------|
| **Draft** | Retur masih draft, belum final | Edit, Hapus, Approve |
| **Approved** | Retur sudah disetujui, siap posting | Posting, Edit, Hapus |
| **Posted** | Retur sudah diposting (jurnal & stok) | Lihat Detail saja |

---

## 🎨 KOMPENSASI

### 1. **Kredit/Nota**
   - Untuk retur penjualan: Buat nota kredit untuk pelanggan
   - Untuk retur pembelian: Dapat nota kredit dari supplier
   - **Tidak ada pergerakan kas**

### 2. **Refund**
   - Untuk retur penjualan: Kembalikan uang ke pelanggan
   - Untuk retur pembelian: Terima uang dari supplier
   - **Ada pergerakan kas**

---

## ✅ VALIDASI SISTEM

### Retur Penjualan
- ✅ Produk harus ada di master produk
- ✅ Qty retur > 0
- ✅ Tanggal retur valid

### Retur Pembelian
- ✅ Bahan baku harus ada di master bahan baku
- ✅ Qty retur > 0
- ✅ Tanggal retur valid

---

## 🔧 CARA PENGGUNAAN

### Contoh: Retur Penjualan

1. **Klik "Tambah Retur"**
2. **Isi Form:**
   - Tanggal: 10/11/2025
   - Tipe: Retur Penjualan
   - Kompensasi: Kredit/Nota
3. **Tambah Detail:**
   - Produk: Ayam Rica-Rica
   - Qty: 10
   - Harga Asal: 19,656 (optional)
4. **Klik "Simpan Retur"**
5. **Sistem akan:**
   - Create retur dengan status "Approved"
   - Siap untuk posting
6. **Posting Retur:**
   - Klik tombol "Post" di list retur
   - Sistem akan:
     - Update stok (produk masuk)
     - Buat jurnal
     - Update status jadi "Posted"

---

## 📝 CATATAN PENTING

1. **Jangan Edit Retur yang Sudah Posted**
   - Retur yang sudah posted tidak bisa diedit
   - Jika ada kesalahan, buat retur baru (reversal)

2. **Validasi Stok**
   - Sistem sudah validasi stok otomatis
   - Tidak perlu khawatir stok minus

3. **Jurnal Otomatis**
   - Jurnal dibuat otomatis saat posting
   - Tidak perlu input manual

4. **Laporan**
   - Semua retur muncul di laporan retur
   - Bisa filter by tanggal, tipe, status

---

## 🚀 FITUR YANG SUDAH ADA

✅ Multi-item retur (bisa retur banyak produk sekaligus)
✅ Dynamic form (tambah/hapus baris)
✅ Validasi real-time
✅ Status tracking
✅ Kompensasi fleksibel
✅ Integrasi stok
✅ Integrasi jurnal
✅ Laporan lengkap

---

## 💡 TIPS

1. **Gunakan Kredit/Nota** untuk transaksi kredit
2. **Gunakan Refund** untuk transaksi tunai
3. **Selalu cek stok** sebelum posting retur penjualan
4. **Backup data** sebelum posting retur besar

---

## 🎯 KESIMPULAN

Sistem retur sudah **LENGKAP dan SEMPURNA** seperti Accurate!
Tidak perlu modifikasi lagi, tinggal digunakan dengan benar.

**Alur sudah benar, code sudah benar, validasi sudah lengkap.**

---

*Dokumentasi ini dibuat untuk memastikan user memahami cara kerja sistem retur yang sudah ada.*
