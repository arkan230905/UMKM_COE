# Desain Sistem Retur yang Sempurna

## 1. STRUKTUR DATABASE

### Tabel: `returs`
```sql
- id (PK)
- kode_retur (unique, auto-generated: RTR-YYYYMMDD-XXX)
- tanggal (date)
- tipe_retur (enum: 'penjualan', 'pembelian')
- referensi_id (nullable) - ID dari penjualan/pembelian asli
- referensi_kode (nullable) - Kode dari penjualan/pembelian asli
- tipe_kompensasi (enum: 'barang', 'uang')
- total_nilai_retur (decimal)
- nilai_kompensasi (decimal)
- status (enum: 'draft', 'diproses', 'selesai')
- keterangan (text)
- created_by
- timestamps
```

### Tabel: `retur_details`
```sql
- id (PK)
- retur_id (FK)
- item_type (enum: 'produk', 'bahan_baku')
- item_id (FK ke produks/bahan_bakus)
- item_nama
- qty_retur (decimal)
- satuan
- harga_satuan (decimal)
- subtotal (decimal)
- keterangan (text)
- timestamps
```

### Tabel: `retur_kompensasis`
```sql
- id (PK)
- retur_id (FK)
- tipe_kompensasi (enum: 'barang', 'uang')
- item_type (nullable, enum: 'produk', 'bahan_baku')
- item_id (nullable, FK)
- item_nama (nullable)
- qty (nullable, decimal)
- satuan (nullable)
- nilai_kompensasi (decimal)
- metode_pembayaran (nullable, enum: 'cash', 'transfer')
- akun_id (nullable, FK ke coas)
- tanggal_kompensasi (date)
- status (enum: 'pending', 'selesai')
- keterangan (text)
- timestamps
```

### Tabel: `retur_jurnal_entries`
```sql
- id (PK)
- retur_id (FK)
- jurnal_entry_id (FK ke jurnal_entries)
- tipe_jurnal (enum: 'penerimaan_barang', 'kompensasi_barang', 'kompensasi_uang')
- timestamps
```

## 2. ALUR BISNIS LENGKAP

### A. RETUR PENJUALAN (dari Pelanggan)

#### Skenario 1: Kompensasi Barang (Produk Baru)
```
1. Input Retur:
   - Pilih tipe: Retur Penjualan
   - Pilih produk yang diretur + qty
   - Sistem hitung total nilai retur
   
2. Dampak Stok (Penerimaan):
   - Stok Produk (+) qty retur
   - Jurnal:
     Dr. Persediaan Produk Jadi
     Cr. Retur Penjualan

3. Kompensasi Produk Baru:
   - Pilih produk pengganti + qty
   - Stok Produk (-) qty kompensasi
   - Jurnal:
     Dr. Retur Penjualan
     Cr. Persediaan Produk Jadi

4. Selisih (jika ada):
   - Jika nilai kompensasi < nilai retur:
     Dr. Retur Penjualan (selisih)
     Cr. Piutang Pelanggan / Kas
   - Jika nilai kompensasi > nilai retur:
     Dr. Piutang Pelanggan / Kas
     Cr. Retur Penjualan (selisih)
```

#### Skenario 2: Kompensasi Uang
```
1. Input Retur:
   - Sama seperti skenario 1
   
2. Dampak Stok (Penerimaan):
   - Stok Produk (+) qty retur
   - Jurnal:
     Dr. Persediaan Produk Jadi
     Cr. Retur Penjualan

3. Kompensasi Uang:
   - Input nominal uang yang dikembalikan
   - Pilih metode: Cash/Transfer
   - Kas/Bank (-) nilai kompensasi
   - Jurnal:
     Dr. Retur Penjualan
     Cr. Kas/Bank
```

### B. RETUR PEMBELIAN (ke Vendor)

#### Skenario 1: Kompensasi Barang (Bahan Baku Baru)
```
1. Input Retur:
   - Pilih tipe: Retur Pembelian
   - Pilih bahan baku yang diretur + qty
   - Sistem hitung total nilai retur
   
2. Dampak Stok (Pengiriman):
   - Stok Bahan Baku (-) qty retur
   - Jurnal:
     Dr. Retur Pembelian
     Cr. Persediaan Bahan Baku

3. Kompensasi Bahan Baku Baru:
   - Pilih bahan baku pengganti + qty
   - Stok Bahan Baku (+) qty kompensasi
   - Jurnal:
     Dr. Persediaan Bahan Baku
     Cr. Retur Pembelian

4. Selisih (jika ada):
   - Jika nilai kompensasi < nilai retur:
     Dr. Kas/Bank
     Cr. Retur Pembelian (selisih)
   - Jika nilai kompensasi > nilai retur:
     Dr. Retur Pembelian (selisih)
     Cr. Utang Vendor / Kas
```

#### Skenario 2: Kompensasi Uang
```
1. Input Retur:
   - Sama seperti skenario 1
   
2. Dampak Stok (Pengiriman):
   - Stok Bahan Baku (-) qty retur
   - Jurnal:
     Dr. Retur Pembelian
     Cr. Persediaan Bahan Baku

3. Kompensasi Uang:
   - Input nominal uang yang diterima
   - Pilih metode: Cash/Transfer
   - Kas/Bank (+) nilai kompensasi
   - Jurnal:
     Dr. Kas/Bank
     Cr. Retur Pembelian
```

## 3. VALIDASI & BUSINESS RULES

### Validasi Stok:
- **Retur Penjualan**: Tidak perlu validasi stok (kita terima barang)
- **Retur Pembelian**: Harus cek stok bahan baku tersedia
- **Kompensasi Barang**: Harus cek stok tersedia

### Status Workflow:
1. **Draft**: Baru dibuat, belum diproses
2. **Diproses**: Sedang menunggu kompensasi
3. **Selesai**: Kompensasi sudah diberikan, jurnal sudah dibuat

### Integrasi dengan Modul Lain:
- **Stok**: Update real-time saat retur diproses
- **Kas/Bank**: Update saat kompensasi uang
- **Jurnal**: Auto-generate jurnal entries
- **Laporan**: Tampil di laporan retur, laporan stok, laporan keuangan

## 4. FITUR TAMBAHAN

### Tracking & Audit:
- History perubahan status
- Log semua transaksi stok
- Referensi ke transaksi asli (jika ada)

### Laporan:
- Laporan Retur Penjualan (per periode, per produk)
- Laporan Retur Pembelian (per periode, per vendor)
- Analisis alasan retur
- Dampak ke profitabilitas

### Notifikasi:
- Alert saat ada retur baru
- Reminder untuk proses kompensasi
- Notifikasi ke vendor (untuk retur pembelian)

## 5. UI/UX FLOW

### Form Retur:
```
Step 1: Pilih Tipe Retur
  - Retur Penjualan
  - Retur Pembelian

Step 2: Detail Barang Retur
  - Pilih item + qty
  - Keterangan alasan retur
  - Upload foto (optional)

Step 3: Pilih Kompensasi
  - Barang (pilih item + qty)
  - Uang (input nominal + metode)

Step 4: Review & Submit
  - Preview semua data
  - Konfirmasi dampak stok
  - Submit
```

### Dashboard Retur:
- List semua retur (filter by status, tipe, tanggal)
- Quick stats: Total retur bulan ini, pending kompensasi
- Chart: Trend retur per bulan

## 6. IMPLEMENTASI BERTAHAP

### Phase 1: Core Functionality
- Migration & Model
- Basic CRUD retur
- Dampak stok sederhana

### Phase 2: Kompensasi
- Kompensasi barang
- Kompensasi uang
- Validasi stok

### Phase 3: Jurnal & Integrasi
- Auto-generate jurnal
- Integrasi dengan modul lain
- Laporan

### Phase 4: Enhancement
- Tracking & audit
- Notifikasi
- Analytics
