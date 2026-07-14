# STRUKTUR ENTITAS LENGKAP DENGAN ATRIBUT (PK & FK)
## ERD Notasi Chen - Inti Proses HPP dan Transaksi Produksi

---

## 1️⃣ MASTER DATA (6 ENTITAS)

### 1.1 companies
**Primary Key:** id
```
Atribut:
- id (PK) : bigint UNSIGNED
- nama_perusahaan : varchar(255)
- alamat : varchar(255)
- email : varchar(255)
- created_at : timestamp
- updated_at : timestamp
```

### 1.2 users
**Primary Key:** id  
**Foreign Keys:** pegawai_id, company_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- pegawai_id (FK) : bigint UNSIGNED
- name : varchar(255)
- email : varchar(255)
- password : varchar(255)
- company_id (FK) : bigint UNSIGNED
- role : enum('admin','owner','pegawai','pelanggan')
- created_at : timestamp
- updated_at : timestamp
```

### 1.3 produks
**Primary Key:** id  
**Foreign Keys:** user_id, kategori_id, satuan_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- kode_produk : varchar(255)
- user_id (FK) : bigint UNSIGNED
- nama_produk : varchar(255)
- kategori_id (FK) : bigint UNSIGNED
- satuan_id (FK) : bigint UNSIGNED
- harga_jual : decimal(15,2)
- harga_pokok : decimal(15,2) -- HPP (BBB + BTKL + BOP)
- stok : decimal(15,4)
- created_at : timestamp
- updated_at : timestamp
```

### 1.4 bahan_bakus
**Primary Key:** id  
**Foreign Keys:** user_id, satuan_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- kode_bahan : varchar(255)
- user_id (FK) : bigint UNSIGNED
- nama_bahan : varchar(255)
- satuan_id (FK) : bigint UNSIGNED
- harga_satuan : decimal(15,2)
- harga_rata_rata : decimal(10,2)
- stok : decimal(15,4)
- stok_minimum : decimal(15,4)
- created_at : timestamp
- updated_at : timestamp
```

### 1.5 jabatan (dari tabel kualifikasis)
**Primary Key:** id  
**Foreign Keys:** user_id, produk_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- user_id (FK) : bigint UNSIGNED
- produk_id (FK) : bigint UNSIGNED
- kode_kualifikasi : varchar(255)
- nama_kualifikasi : varchar(255) -- Nama jabatan
- kategori : varchar(255) -- 'btkl' atau 'btktl'
- tarif : decimal(15,2) -- Tarif per produk
- gaji_pokok : decimal(15,2)
- tunjangan_transport : decimal(15,2)
- tunjangan_konsumsi : decimal(15,2)
- asuransi : decimal(15,2)
- target_produksi : int
- created_at : timestamp
- updated_at : timestamp
```

### 1.6 accounts (COA)
**Primary Key:** id  
**Foreign Keys:** company_id, user_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- company_id (FK) : bigint UNSIGNED
- user_id (FK) : bigint UNSIGNED
- kode_akun : varchar(255)
- nama_akun : varchar(255)
- tipe_akun : varchar(255) -- Asset, Liability, Equity, Revenue, Expense
- kategori_akun : varchar(255)
- saldo_normal : enum('debit','kredit')
- saldo_awal : decimal(15,2)
- created_at : timestamp
- updated_at : timestamp
```

---

## 2️⃣ INPUT BIAYA (5 ENTITAS)

### 2.1 biaya_bahan_baku (kualifikasi BBB)
**Primary Key:** id  
**Foreign Keys:** user_id, produk_id, bahan_baku_id, coa_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- user_id (FK) : bigint UNSIGNED
- produk_id (FK) : bigint UNSIGNED
- bahan_baku_id (FK) : bigint UNSIGNED
- coa_id (FK) : bigint UNSIGNED
- jumlah : decimal(15,4) -- Qty bahan baku yang dibutuhkan
- satuan : varchar(20)
- harga_satuan : decimal(15,2)
- subtotal : decimal(15,2) -- jumlah × harga_satuan
- keterangan : text
- created_at : timestamp
- updated_at : timestamp
```

### 2.2 kualifikasi_detail
**Primary Key:** id  
**Foreign Keys:** (tidak ada FK eksplisit, tapi di aplikasi link ke kualifikasi/biaya_bahan_baku)
```
Atribut:
- id (PK) : bigint UNSIGNED
- kualifikasi_id (FK logic) : bigint UNSIGNED -- Link ke biaya_bahan_baku
- bahan_baku_id (FK logic) : bigint UNSIGNED
- qty : decimal(15,4)
- satuan : varchar(50)
- harga_satuan : decimal(15,2)
- subtotal : decimal(15,2)
- created_at : timestamp
- updated_at : timestamp

CATATAN: Tabel ini tidak ada di SQL dump, tetapi disebutkan dalam requirement.
Jika tidak ada, bisa diasumsikan detail BBB langsung di biaya_bahan_baku.
```

### 2.3 btkls
**Primary Key:** id  
**Foreign Keys:** user_id, kualifikasi_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- user_id (FK) : bigint UNSIGNED
- kode_proses : varchar(255)
- nama_btkl : varchar(255)
- kualifikasi_id (FK) : bigint UNSIGNED -- Link ke jabatan
- biaya_per_produk : decimal(15,2)
- deskripsi_proses : text
- is_active : tinyint(1)
- created_at : timestamp
- updated_at : timestamp
```

### 2.4 bops
**Primary Key:** id  
**Foreign Keys:** coa_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- coa_id (FK) : bigint UNSIGNED
- kode_akun : varchar(255)
- nama_akun : varchar(255)
- budget : decimal(15,2)
- keterangan : text
- periode : varchar(7) -- Format YYYY-MM
- is_active : tinyint(1)
- created_at : timestamp
- updated_at : timestamp
```

### 2.5 komponen_bops
**Primary Key:** id  
**Foreign Keys:** (implicit link to bops via bop_proses)
```
Atribut:
- id (PK) : bigint UNSIGNED
- kode_komponen : varchar(20) -- BOP-001
- nama_komponen : varchar(100) -- Listrik, Gas, dll
- satuan : varchar(20) -- kWh, m³, jam
- tarif_per_satuan : decimal(15,2)
- is_active : tinyint(1)
- created_at : timestamp
- updated_at : timestamp
```

---

## 3️⃣ HPP (1 ENTITAS UNIFIED - Dari bop_proses)

### 3.1 bop_proses (sebagai tabel HPP unified)
**Primary Key:** id  
**Foreign Keys:** user_id, produk_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- user_id (FK) : bigint UNSIGNED
- produk_id (FK) : bigint UNSIGNED
- periode : varchar(7) -- Format YYYY-MM
- jumlah_produksi : decimal(15,2) -- Target produksi
- nama_bop_proses : varchar(255)
- komponen_bahan_pendukung : longtext JSON
- komponen_lainnya : longtext JSON
- total_bop_per_produk : decimal(15,2)
- jumlah_produksi_perbulan : int
- total_biaya_per_produk : decimal(15,2)
- keterangan : text
- is_active : tinyint(1)
- created_at : timestamp
- updated_at : timestamp

CATATAN: Tabel ini menyimpan hasil kalkulasi BOP.
Untuk HPP lengkap (BBB + BTKL + BOP), bisa dikombinasikan dengan:
- Total BBB dari biaya_bahan_baku (sum subtotal)
- Total BTKL dari btkls (biaya_per_produk)
- Total BOP dari bop_proses (total_bop_per_produk)
```

**ALTERNATIF: Jika ingin tabel HPP terpisah yang unified:**
```sql
CREATE TABLE hpp (
  id bigint UNSIGNED PRIMARY KEY,
  user_id bigint UNSIGNED FK,
  produk_id bigint UNSIGNED FK,
  periode varchar(7),
  
  -- BBB Component
  total_bbb decimal(15,2),
  hpp_bbb_per_unit decimal(15,2),
  
  -- BTKL Component  
  total_btkl decimal(15,2),
  hpp_btkl_per_unit decimal(15,2),
  
  -- BOP Component
  total_bop decimal(15,2),
  hpp_bop_per_unit decimal(15,2),
  
  -- Total HPP
  unit_produksi decimal(15,2),
  hpp_total_per_unit decimal(15,2), -- BBB + BTKL + BOP per unit
  
  created_at timestamp,
  updated_at timestamp
);
```

---

## 4️⃣ TRANSAKSI PRODUKSI (2 ENTITAS)

### 4.1 produksis (atau proses_produksis sebagai header)
**Primary Key:** id  
**Foreign Keys:** user_id, pegawai_id, produk_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- user_id (FK) : bigint UNSIGNED
- pegawai_id (FK) : bigint UNSIGNED
- produk_id (FK) : bigint UNSIGNED
- tanggal : date
- qty_produksi : decimal(15,4)
- jumlah_produksi_bulanan : decimal(15,4)
- hari_produksi_bulanan : int
- total_bahan : decimal(15,2)
- total_btkl : decimal(15,2)
- total_bop : decimal(15,2)
- total_biaya : decimal(15,2)
- status : enum('draft','dalam_proses','selesai','completed')
- created_at : timestamp
- updated_at : timestamp
```

### 4.2 produksi_details
**Primary Key:** id  
**Foreign Keys:** user_id, produksi_id, bahan_baku_id, bahan_pendukung_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- user_id (FK) : bigint UNSIGNED
- produksi_id (FK) : bigint UNSIGNED
- bahan_baku_id (FK) : bigint UNSIGNED
- bahan_pendukung_id (FK) : bigint UNSIGNED
- qty_resep : decimal(15,4)
- satuan_resep : varchar(50)
- qty_konversi : decimal(15,4)
- harga_satuan : decimal(15,4)
- subtotal : decimal(15,2)
- satuan : varchar(50)
- created_at : timestamp
- updated_at : timestamp
```

---

## 5️⃣ LAPORAN (2 ENTITAS)

### 5.1 stock_movements
**Primary Key:** id  
**Foreign Keys:** user_id, item_id
```
Atribut:
- id (PK) : bigint UNSIGNED
- user_id (FK) : bigint UNSIGNED
- item_type : enum('material','product','support')
- item_id (FK) : bigint UNSIGNED -- Link ke bahan_baku/produk
- tanggal : date
- direction : enum('in','out')
- qty : decimal(15,4)
- satuan : varchar(50)
- unit_cost : decimal(15,4)
- total_cost : decimal(15,2)
- ref_type : varchar(50) -- 'produksi', 'purchase', 'sale', dll
- ref_id : bigint UNSIGNED
- keterangan : varchar(255)
- created_at : timestamp
- updated_at : timestamp
```

### 5.2 jurnal_umum
**Primary Key:** id  
**Foreign Keys:** user_id, company_id, coa_id, created_by
```
Atribut:
- id (PK) : bigint UNSIGNED
- user_id (FK) : bigint UNSIGNED
- company_id (FK) : bigint UNSIGNED
- coa_id (FK) : bigint UNSIGNED
- tanggal : date
- bukti_transaksi : varchar(255)
- keterangan : varchar(255)
- debit : decimal(15,2)
- kredit : decimal(15,2)
- tipe_referensi : varchar(255) -- 'produksi', 'pembelian', 'sale', dll
- referensi : varchar(255)
- created_by (FK) : bigint UNSIGNED
- created_at : timestamp
- updated_at : timestamp
```

---

## 📊 RELASI ANTAR ENTITAS (CHEN NOTATION)

### Master Data Relations:
1. **companies** (1) ─── memiliki ─── (n) **users**
2. **companies** (1) ─── memiliki ─── (n) **produks**
3. **users** (1) ─── mengelola ─── (n) **bahan_bakus**
4. **users** (1) ─── memiliki ─── (n) **jabatan** (kualifikasis)

### Input Biaya Relations:
5. **produks** (1) ─── memiliki_biaya_bbb ─── (n) **biaya_bahan_baku**
6. **bahan_bakus** (1) ─── digunakan_dalam ─── (n) **biaya_bahan_baku**
7. **produks** (1) ─── memiliki_biaya_btkl ─── (n) **btkls**
8. **jabatan** (1) ─── menentukan_tarif ─── (n) **btkls**
9. **accounts** (1) ─── akun_bop ─── (n) **bops**
10. **bops** (1) ─── memiliki_komponen ─── (n) **komponen_bops**

### HPP Relations:
11. **biaya_bahan_baku** (1) ─── menghitung ─── (n) **bop_proses** (hpp)
12. **btkls** (1) ─── menghitung ─── (n) **bop_proses** (hpp)
13. **bops** (1) ─── menghitung ─── (n) **bop_proses** (hpp)
14. **produks** (1) ─── memiliki_hpp ─── (n) **bop_proses** (hpp)

### Transaksi Produksi Relations:
15. **bop_proses** (hpp) (1) ─── digunakan_dalam ─── (n) **produksis**
16. **produks** (1) ─── diproduksi ─── (n) **produksis**
17. **produksis** (1) ─── memiliki_detail ─── (n) **produksi_details**
18. **bahan_bakus** (1) ─── digunakan ─── (n) **produksi_details**

### Laporan Relations:
19. **bahan_bakus** (1) ─── mutasi_stok ─── (n) **stock_movements**
20. **produksis** (1) ─── mengkonsumsi ─── (n) **stock_movements**
21. **accounts** (1) ─── posting_ke ─── (n) **jurnal_umum**
22. **produksis** (1) ─── autoposting ─── (n) **jurnal_umum**

---

## ⚠️ CATATAN PENTING

### Notasi Chen yang Digunakan:
1. **Entitas** = Kotak persegi panjang
2. **Atribut** = Oval/ellipse terhubung ke entitas
3. **Primary Key** = Atribut dengan garis bawah
4. **Relasi** = Diamond (belah ketupat)
5. **Kardinalitas** = 1 atau n pada garis penghubung

### Simplifikasi ERD:
- Untuk ERD yang mudah dibaca, atribut bisa ditampilkan di dalam kotak entitas (bukan oval terpisah)
- PK ditandai dengan **bold** atau underline
- FK ditandai dengan (FK) di belakang nama atribut

### Tabel HPP Unified:
Database actual tidak memiliki tabel `hpp` terpisah yang unified.
HPP dihitung dari:
- **BBB**: `biaya_bahan_baku` (sum subtotal)
- **BTKL**: `btkls` (biaya_per_produk)
- **BOP**: `bop_proses` (total_bop_per_produk)

Total HPP = BBB + BTKL + BOP kemudian disimpan di `produks.harga_pokok`

---

**Created by:** Kiro AI Assistant  
**Date:** 2026-07-14  
**Purpose:** Dokumentasi lengkap struktur entitas untuk ERD dengan atribut (PK & FK)  
**Source:** eadt_umkm.sql database dump
