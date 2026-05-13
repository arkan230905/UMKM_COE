# Struktur Database - UMKM COE

## Tabel Utama

### 1. `users`
```sql
- id (PK)
- name
- email (UNIQUE)
- password
- role (enum: admin, owner, staff)
- email_verified_at
- remember_token
- timestamps
```

### 2. `jabatans` (Kualifikasi Tenaga Kerja)
```sql
- id (PK)
- user_id (FK → users.id)
- nama
- kategori (enum: btkl, btktl)
- kode_jabatan
- gaji_pokok
- tarif (Tarif per PRODUK, bukan per jam)
- tunjangan
- tunjangan_transport
- tunjangan_konsumsi
- asuransi
- timestamps
```

**PENTING:** Tidak ada kolom `tarif_per_jam`!

### 3. `coas` (Chart of Accounts)
```sql
- id (PK)
- user_id (FK → users.id)
- kode_akun (VARCHAR)
- nama_akun
- tipe_akun (Aset, Kewajiban, Modal, Pendapatan, Biaya)
- kategori_akun
- saldo_normal (debit/kredit)
- saldo_awal
- tanggal_saldo_awal
- posted_saldo_awal
- timestamps
```

**Default:** 51 COA per user

### 4. `satuans`
```sql
- id (PK)
- user_id (FK → users.id)
- kode
- nama
- is_active
- timestamps
```

**Default:** 16 Satuan per user

### 5. `kategori_bahan_pendukung`
```sql
- id (PK)
- user_id (FK → users.id) ← PENTING!
- nama
- keterangan
- is_active
- timestamps
```

**Default:** 7 Kategori (Gas, Bumbu, Minyak, Air, Listrik, Kemasan, Lainnya)

### 6. `bahan_bakus`
```sql
- id (PK)
- user_id (FK → users.id)
- kode_bahan
- nama_bahan
- deskripsi
- satuan_id (FK → satuans.id)
- sub_satuan_1_id (FK → satuans.id)
- sub_satuan_1_konversi
- sub_satuan_1_nilai
- sub_satuan_2_id (FK → satuans.id)
- sub_satuan_2_konversi
- sub_satuan_2_nilai
- sub_satuan_3_id (FK → satuans.id)
- sub_satuan_3_konversi
- sub_satuan_3_nilai
- coa_pembelian_id (FK → coas.kode_akun)
- coa_persediaan_id (FK → coas.kode_akun)
- coa_hpp_id (FK → coas.kode_akun)
- harga_satuan
- saldo_awal
- tanggal_saldo_awal
- stok_minimum
- stok (calculated from stock_movements)
- timestamps
```

**Unique:** (user_id, kode_bahan)

### 7. `bahan_pendukungs`
```sql
- id (PK)
- user_id (FK → users.id)
- kode_bahan
- nama_bahan
- deskripsi
- satuan_id (FK → satuans.id)
- sub_satuan_1_id (FK → satuans.id)
- sub_satuan_1_konversi
- sub_satuan_1_nilai
- sub_satuan_2_id (FK → satuans.id)
- sub_satuan_2_konversi
- sub_satuan_2_nilai
- sub_satuan_3_id (FK → satuans.id)
- sub_satuan_3_konversi
- sub_satuan_3_nilai
- coa_pembelian_id (FK → coas.kode_akun)
- coa_persediaan_id (FK → coas.kode_akun)
- coa_hpp_id (FK → coas.kode_akun)
- harga_satuan
- saldo_awal
- tanggal_saldo_awal
- stok_minimum
- stok (calculated from stock_movements)
- kategori (enum: gas, bumbu, minyak, air, listrik, pembersih, lainnya)
- kategori_id (FK → kategori_bahan_pendukung.id)
- is_active
- timestamps
```

**Unique:** (user_id, kode_bahan)

### 8. `stock_movements`
```sql
- id (PK)
- item_type (enum: material, support, product)
- item_id (ID dari bahan_bakus/bahan_pendukungs/produks)
- tanggal
- direction (enum: in, out)
- qty
- unit
- unit_cost
- total_cost
- ref_type (initial_stock, purchase, production, sale, adjustment, etc)
- ref_id
- keterangan
- timestamps
```

**Fungsi:** Tracking real-time stock untuk semua item

## Relasi Penting

### Multi-Tenant Isolation
Semua tabel utama memiliki `user_id` untuk isolasi data antar user:
- jabatans
- coas
- satuans
- kategori_bahan_pendukung ← **PENTING!**
- bahan_bakus
- bahan_pendukungs
- produks
- vendors
- pelanggans
- dll

### Stock System
Stock real-time dihitung dari `stock_movements`:
```
stok_real_time = SUM(qty WHERE direction='in') - SUM(qty WHERE direction='out')
```

Fallback ke `saldo_awal` jika tidak ada stock movements.

### COA System
- Setiap bahan baku/pendukung bisa memiliki 3 COA:
  - `coa_pembelian_id` → untuk jurnal pembelian
  - `coa_persediaan_id` → untuk jurnal persediaan
  - `coa_hpp_id` → untuk jurnal HPP

### Sub Satuan System
- Setiap bahan bisa memiliki 3 sub satuan
- Konversi menggunakan `sub_satuan_X_nilai` (bukan `sub_satuan_X_konversi`)
- Contoh: 1 Ekor = 6 Potong → `sub_satuan_1_nilai = 6`

## Indexes

### Unique Constraints
- `users.email`
- `bahan_bakus(user_id, kode_bahan)`
- `bahan_pendukungs(user_id, kode_bahan)`
- `jabatans.kode_jabatan`

### Foreign Keys
Semua foreign key menggunakan `ON DELETE CASCADE` atau `ON DELETE RESTRICT` sesuai kebutuhan.

### Performance Indexes
- `user_id` pada semua tabel multi-tenant
- `nama_bahan` pada bahan_bakus dan bahan_pendukungs
- `kategori` pada bahan_pendukungs
- `item_type, item_id` pada stock_movements

## Migration Order

Urutan migration yang benar:
1. users
2. satuans
3. coas
4. kategori_bahan_pendukung (dengan user_id!)
5. jabatans
6. bahan_bakus
7. bahan_pendukungs
8. stock_movements
9. produks
10. dll

## Verifikasi Database

### Check COA
```sql
SELECT COUNT(*) FROM coas WHERE user_id = 1;
-- Expected: 51
```

### Check Satuan
```sql
SELECT COUNT(*) FROM satuans WHERE user_id = 1;
-- Expected: 16
```

### Check Kategori Bahan Pendukung
```sql
SELECT COUNT(*) FROM kategori_bahan_pendukung WHERE user_id = 1;
-- Expected: 7
```

### Check Jabatan Structure
```sql
DESCRIBE jabatans;
-- Must have: tarif (NOT tarif_per_jam)
```

### Check Stock Movements
```sql
SELECT item_type, COUNT(*) 
FROM stock_movements 
GROUP BY item_type;
```

## Troubleshooting

### Error: Column 'user_id' not found in kategori_bahan_pendukung
**Fix:** Run `php setup_hosting.php` yang akan otomatis menambahkan kolom.

### Error: Column 'tarif_per_jam' not found in jabatans
**Fix:** Migration sudah diperbaiki. Kolom yang benar adalah `tarif`.

### Stock tidak update
**Fix:** Pastikan `StockMovement` dibuat saat create/update bahan.

---

**Last Updated:** 13 Mei 2026
**Status:** ✅ VERIFIED
