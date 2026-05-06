# 📊 Database Structure - Penjualan

## 🗂️ Tabel-Tabel yang Terhubung dengan Penjualan

### 1. **penjualans** (Tabel Utama)
**Deskripsi:** Tabel header/master untuk transaksi penjualan

**Kolom:**
```sql
- id (PK)
- user_id (FK → users) ✅ MULTI-TENANT
- nomor_penjualan (UNIQUE)
- produk_id (FK → produks) - untuk backward compatibility
- tanggal
- payment_method (cash|transfer|credit)
- harga_satuan (nullable)
- jumlah
- diskon_nominal
- total
- bukti_pembayaran (file path)
- catatan_pembayaran
- created_at
- updated_at
```

**Relasi:**
- `belongsTo` → **users** (via user_id)
- `belongsTo` → **produks** (via produk_id) - legacy
- `hasMany` → **penjualan_details**
- `hasMany` → **retur_penjualans**
- `hasMany` → **bukti_pembayarans**
- `hasMany` → **sales_returns**
- `hasMany` → **stock_movements** (via ref_type='sale')
- `hasMany` → **jurnal_umum** (via referensi)

---

### 2. **penjualan_details** (Detail Items)
**Deskripsi:** Detail item produk yang dijual per transaksi

**Kolom:**
```sql
- id (PK)
- user_id (FK → users) ✅ MULTI-TENANT
- penjualan_id (FK → penjualans)
- produk_id (FK → produks)
- jumlah
- harga_satuan
- diskon_persen
- diskon_nominal
- subtotal
- created_at
- updated_at
```

**Relasi:**
- `belongsTo` → **users** (via user_id)
- `belongsTo` → **penjualans** (via penjualan_id)
- `belongsTo` → **produks** (via produk_id)
- `hasMany` → **detail_retur_penjualans**
- `hasMany` → **sales_return_items**

---

### 3. **retur_penjualans** (Retur Penjualan)
**Deskripsi:** Header retur/pengembalian barang dari penjualan

**Kolom:**
```sql
- id (PK)
- user_id (FK → users) ✅ MULTI-TENANT (BARU DITAMBAHKAN)
- nomor_retur (UNIQUE)
- tanggal
- penjualan_id (FK → penjualans)
- pelanggan_id (FK → pelanggans) - nullable
- jenis_retur (tukar_barang|refund|kredit)
- total_retur
- ppn
- status
- keterangan
- created_at
- updated_at
```

**Relasi:**
- `belongsTo` → **users** (via user_id)
- `belongsTo` → **penjualans** (via penjualan_id)
- `belongsTo` → **pelanggans** (via pelanggan_id)
- `hasMany` → **detail_retur_penjualans**
- `hasMany` → **stock_movements** (via ref_type='retur_penjualan')
- `hasMany` → **jurnal_umum** (via referensi)

---

### 4. **detail_retur_penjualans** (Detail Retur)
**Deskripsi:** Detail item yang diretur per transaksi retur

**Kolom:**
```sql
- id (PK)
- retur_penjualan_id (FK → retur_penjualans)
- penjualan_detail_id (FK → penjualan_details)
- produk_id (FK → produks)
- qty_retur
- harga_satuan
- subtotal
- alasan_retur
- created_at
- updated_at
```

**Relasi:**
- `belongsTo` → **retur_penjualans** (via retur_penjualan_id)
- `belongsTo` → **penjualan_details** (via penjualan_detail_id)
- `belongsTo` → **produks** (via produk_id)

---

### 5. **bukti_pembayarans** (Bukti Pembayaran)
**Deskripsi:** File bukti pembayaran yang diupload untuk penjualan

**Kolom:**
```sql
- id (PK)
- penjualan_id (FK → penjualans)
- file_path
- keterangan
- created_at
- updated_at
```

**Relasi:**
- `belongsTo` → **penjualans** (via penjualan_id)

---

### 6. **sales_returns** (Legacy - Sistem Lama)
**Deskripsi:** Tabel retur sistem lama (mungkin tidak digunakan lagi)

**Kolom:**
```sql
- id (PK)
- penjualan_id (FK → penjualans)
- return_number (UNIQUE)
- return_date
- total_amount
- status
- created_at
- updated_at
```

**Relasi:**
- `belongsTo` → **penjualans** (via penjualan_id)
- `hasMany` → **sales_return_items**

---

### 7. **sales_return_items** (Legacy - Detail Retur Lama)
**Deskripsi:** Detail item retur sistem lama

**Kolom:**
```sql
- id (PK)
- sales_return_id (FK → sales_returns)
- penjualan_detail_id (FK → penjualan_details)
- produk_id (FK → produks)
- unit
- quantity
- price
- subtotal
- created_at
- updated_at
```

**Relasi:**
- `belongsTo` → **sales_returns** (via sales_return_id)
- `belongsTo` → **penjualan_details** (via penjualan_detail_id)
- `belongsTo` → **produks** (via produk_id)

---

## 🔗 Tabel Terkait (Indirect)

### 8. **produks** (Master Produk)
**Deskripsi:** Master data produk yang dijual

**Kolom Penting:**
```sql
- id (PK)
- user_id (FK → users) ✅ MULTI-TENANT
- nama_produk
- barcode
- harga_jual
- stok
- hpp (Harga Pokok Penjualan)
```

**Digunakan di:**
- penjualans (produk_id)
- penjualan_details (produk_id)
- retur_penjualans (via detail)
- detail_retur_penjualans (produk_id)

---

### 9. **stock_movements** (Pergerakan Stok)
**Deskripsi:** Log pergerakan stok untuk tracking

**Kolom Penting:**
```sql
- id (PK)
- item_type (product|material|support)
- item_id (FK → produks/bahan_bakus/bahan_pendukungs)
- tanggal
- direction (in|out)
- qty
- satuan
- unit_cost
- total_cost
- ref_type (sale|retur_penjualan|production|etc)
- ref_id (FK → penjualans/retur_penjualans/etc)
```

**Digunakan untuk:**
- Track stok keluar saat penjualan (ref_type='sale')
- Track stok masuk saat retur (ref_type='retur_penjualan')

---

### 10. **stock_layers** (Layer Stok - FIFO/LIFO)
**Deskripsi:** Layer stok untuk perhitungan HPP

**Kolom Penting:**
```sql
- id (PK)
- item_type (product|material|support)
- item_id (FK → produks/bahan_bakus/bahan_pendukungs)
- tanggal
- remaining_qty
- unit_cost
- satuan
- ref_type
- ref_id
```

**Digunakan untuk:**
- Consume stok saat penjualan (FIFO/LIFO)
- Add layer saat retur

---

### 11. **jurnal_umum** (Jurnal Akuntansi)
**Deskripsi:** Jurnal akuntansi untuk pencatatan transaksi

**Kolom Penting:**
```sql
- id (PK)
- user_id (FK → users) ✅ MULTI-TENANT
- coa_id (FK → coas)
- tanggal
- keterangan
- debit
- kredit
- referensi (nomor_penjualan)
- tipe_referensi (sale|retur_penjualan|etc)
```

**Digunakan untuk:**
- Jurnal penjualan (Debit: Kas/Piutang, Credit: Penjualan)
- Jurnal HPP (Debit: HPP, Credit: Persediaan)
- Jurnal retur (kebalikan dari penjualan)

---

### 12. **coas** (Chart of Accounts)
**Deskripsi:** Master akun untuk jurnal

**Kolom Penting:**
```sql
- id (PK)
- user_id (FK → users) ✅ MULTI-TENANT
- kode_akun
- nama_akun
- tipe_akun (asset|liability|equity|revenue|expense)
- saldo_normal (debit|credit)
```

**Digunakan untuk:**
- Akun Kas/Bank (untuk penjualan cash/transfer)
- Akun Piutang (untuk penjualan credit)
- Akun Penjualan (revenue)
- Akun HPP (expense)
- Akun Persediaan (asset)

---

### 13. **users** (Master User)
**Deskripsi:** Master data user/tenant

**Kolom Penting:**
```sql
- id (PK)
- name
- email
- role (admin|owner|pegawai|etc)
```

**Digunakan sebagai:**
- Owner/tenant untuk semua data (user_id)

---

### 14. **pelanggans** (Master Pelanggan)
**Deskripsi:** Master data pelanggan (optional)

**Kolom Penting:**
```sql
- id (PK)
- user_id (FK → users) ✅ MULTI-TENANT
- nama
- alamat
- telepon
```

**Digunakan di:**
- retur_penjualans (pelanggan_id) - optional

---

### 15. **perusahaans** (Data Perusahaan)
**Deskripsi:** Data perusahaan untuk struk/invoice

**Kolom Penting:**
```sql
- id (PK)
- user_id (FK → users) ✅ MULTI-TENANT
- nama
- alamat
- telepon
- logo
```

**Digunakan untuk:**
- Print struk penjualan
- Print invoice

---

## 📊 Diagram Relasi

```
users (tenant)
  ↓
  ├─→ penjualans (header)
  │     ├─→ penjualan_details (items)
  │     │     ├─→ produks
  │     │     └─→ detail_retur_penjualans
  │     │
  │     ├─→ retur_penjualans (retur header)
  │     │     └─→ detail_retur_penjualans (retur items)
  │     │           └─→ produks
  │     │
  │     ├─→ bukti_pembayarans (files)
  │     │
  │     ├─→ stock_movements (ref_type='sale')
  │     │     └─→ produks
  │     │
  │     └─→ jurnal_umum (accounting)
  │           └─→ coas (accounts)
  │
  ├─→ produks (master)
  │     ├─→ stock_movements
  │     └─→ stock_layers
  │
  ├─→ coas (chart of accounts)
  │     └─→ jurnal_umum
  │
  ├─→ pelanggans (optional)
  │     └─→ retur_penjualans
  │
  └─→ perusahaans (company info)
        └─→ struk/invoice
```

---

## ✅ Status Multi-Tenant

### Tabel dengan user_id (AMAN):
- ✅ **penjualans** - Ada user_id + auto-fill
- ✅ **penjualan_details** - Ada user_id
- ✅ **retur_penjualans** - Ada user_id + auto-fill (BARU DIPERBAIKI)
- ✅ **produks** - Ada user_id + auto-fill
- ✅ **jurnal_umum** - Ada user_id + auto-fill
- ✅ **coas** - Ada user_id + auto-fill
- ✅ **pelanggans** - Ada user_id
- ✅ **perusahaans** - Ada user_id

### Tabel tanpa user_id (Detail/Child):
- ⚠️ **detail_retur_penjualans** - Tidak perlu (child dari retur_penjualans)
- ⚠️ **bukti_pembayarans** - Tidak perlu (child dari penjualans)
- ⚠️ **stock_movements** - Tidak perlu (tracking log)
- ⚠️ **stock_layers** - Tidak perlu (tracking log)
- ⚠️ **sales_returns** - Legacy (mungkin tidak digunakan)
- ⚠️ **sales_return_items** - Legacy (mungkin tidak digunakan)

---

## 🔒 Security Check

### Query yang Harus Selalu Filter by user_id:

1. **SELECT penjualans** → `WHERE user_id = auth()->id()`
2. **SELECT produks** → `WHERE user_id = auth()->id()`
3. **SELECT retur_penjualans** → `WHERE user_id = auth()->id()`
4. **SELECT coas** → `WHERE user_id = auth()->id()`
5. **SELECT pelanggans** → `WHERE user_id = auth()->id()`
6. **SELECT perusahaans** → `WHERE user_id = auth()->id()`
7. **SELECT jurnal_umum** → `WHERE user_id = auth()->id()`

### Query yang Aman (via Parent):

1. **penjualan_details** → Via `penjualans.user_id`
2. **detail_retur_penjualans** → Via `retur_penjualans.user_id`
3. **bukti_pembayarans** → Via `penjualans.user_id`
4. **stock_movements** → Via `item_id` (produk sudah filter user_id)
5. **stock_layers** → Via `item_id` (produk sudah filter user_id)

---

## 📝 Summary

### Total Tabel Terhubung: **15 tabel**

#### Direct (Primary):
1. penjualans ✅
2. penjualan_details ✅
3. retur_penjualans ✅
4. detail_retur_penjualans ✅
5. bukti_pembayarans ✅
6. sales_returns (legacy)
7. sales_return_items (legacy)

#### Indirect (Related):
8. produks ✅
9. stock_movements
10. stock_layers
11. jurnal_umum ✅
12. coas ✅
13. users ✅
14. pelanggans ✅
15. perusahaans ✅

### Multi-Tenant Status: ✅ SECURED

Semua tabel utama sudah memiliki `user_id` dan filter yang benar!

---

**Date:** 2026-05-06  
**Status:** ✅ DOCUMENTED  
**Multi-Tenant:** 🔒 SECURED
