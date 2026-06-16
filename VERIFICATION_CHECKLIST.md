# CHECKLIST VERIFIKASI PERBAIKAN MULTI-TENANT

**Dibuat**: Juni 16, 2026  
**Status**: Ready for Testing

---

## RINGKASAN PERUBAHAN

| File | Method | Perubahan | Status |
|------|--------|-----------|--------|
| ReturPenjualanController.php | detailRetur() | ✅ Filter pelanggan by user_id | DONE |
| ReturPenjualanController.php | edit() | ✅ Filter pelanggan by user_id | DONE |
| LaporanController.php | penjualan() | ✅ Filter penjualanTunai by user_id | DONE |
| LaporanController.php | penjualan() | ✅ Filter penjualanKredit by user_id | DONE |
| LaporanController.php | penjualan() | ✅ Filter returPenjualans by user_id | DONE |
| LaporanController.php | invoicePenjualan() | ✅ Filter penjualan by user_id | DONE |
| LaporanKartuStokController.php | index() | ✅ Filter bahanBakus & bahanPendukungs | DONE |
| LaporanKartuStokController.php | summary() | ✅ Filter bahanBakus & bahanPendukungs | DONE |
| LaporanKartuStokController.php | export() | ✅ Filter bahanBakus & bahanPendukungs | DONE |
| Api/ProdukController.php | getBomDetails() | ✅ Filter produk by user_id | DONE |
| Api/ProdukController.php | getBomCalculations() | ✅ Filter produk by user_id | DONE |
| Migration | 2026_06_16_140000 | ✅ Unique constraint (user_id, kode_proses) | VERIFIED |

---

## PRE-DEPLOYMENT CHECKLIST

### A. CODE REVIEW
- [ ] Semua file yang diubah telah di-review
- [ ] Tidak ada syntax error
- [ ] Semua filter menggunakan `auth()->id()` konsisten
- [ ] Tidak ada hardcoded user_id atau perusahaan_id

### B. TESTING ENVIRONMENT
- [ ] Database backup sudah dibuat
- [ ] Environment dev/test siap dengan data multiple tenant
- [ ] Git branch dibuat untuk changes

### C. DATABASE
- [ ] Semua migration sudah di-run di local/dev
- [ ] Unique constraints benar-benar di-apply pada proses_produksis
- [ ] Tidak ada foreign key constraint issues

---

## FUNCTIONAL TESTING

### TEST 1: DATA ISOLATION - MASALAH 1

**Skenario**: Dua perusahaan login, verifikasi data terpisah

#### 1A. Laporan Penjualan
```
TEST: Laporan Penjualan - Data Isolation
SETUP:
  - Login sebagai Perusahaan A (user_id = 10)
  - Perusahaan A punya penjualan:
    * PJ-001, PJ-002, PJ-003 (user_id = 10)
  - Perusahaan B (user_id = 20) punya penjualan:
    * PJ-001, PJ-002 (user_id = 20)

STEPS:
  1. Login sebagai user perusahaan A
  2. Buka URL: /laporan/penjualan
  3. Verifikasi hasil

EXPECTED RESULT:
  ✅ Hanya menampilkan PJ-001, PJ-002, PJ-003 (dari A)
  ✅ Data perusahaan B TIDAK terlihat
  ✅ Total transaksi = 3
  ✅ Tidak ada error SQL

ACTUAL RESULT:
  [ ] PASS / [ ] FAIL

Notes: ___________________________________________
```

#### 1B. Laporan Retur Penjualan
```
TEST: Laporan Retur Penjualan - Data Isolation
SETUP:
  - Login sebagai Perusahaan A
  - Perusahaan A punya retur:
    * RT-001, RT-002 (user_id = 10)
  - Perusahaan B punya retur:
    * RT-001, RT-002 (user_id = 20)

STEPS:
  1. Login sebagai user perusahaan A
  2. Buka Transaksi → Retur Penjualan
  3. Buka detail retur → Pilih penjualan tertentu
  4. Verifikasi dropdown pelanggan

EXPECTED RESULT:
  ✅ Hanya pelanggan dari perusahaan A muncul di dropdown
  ✅ Tidak ada error "Unknown column user_id"
  ✅ Tidak ada pelanggan dari perusahaan B

ACTUAL RESULT:
  [ ] PASS / [ ] FAIL

Notes: ___________________________________________
```

#### 1C. Laporan Stok Produk
```
TEST: Laporan Stok - Data Isolation
SETUP:
  - Perusahaan A punya produk: Produk A1, A2, A3
  - Perusahaan B punya produk: Produk B1, B2

STEPS:
  1. Login sebagai user perusahaan A
  2. Buka Laporan → Kartu Stok
  3. Buka dropdown item untuk memilih produk

EXPECTED RESULT:
  ✅ Dropdown hanya menampilkan: Produk A1, A2, A3
  ✅ Produk B1, B2 TIDAK muncul
  ✅ Tidak ada error

ACTUAL RESULT:
  [ ] PASS / [ ] FAIL

Notes: ___________________________________________
```

#### 1D. Master Data Pelanggan
```
TEST: Master Data Pelanggan - Data Isolation
SETUP:
  - Perusahaan A punya pelanggan: Customer A1, A2, A3
  - Perusahaan B punya pelanggan: Customer B1, B2

STEPS:
  1. Login sebagai user perusahaan A
  2. Buka Master Data → Pelanggan
  3. Verifikasi list yang ditampilkan

EXPECTED RESULT:
  ✅ List hanya menampilkan: Customer A1, A2, A3
  ✅ Customer B1, B2 TIDAK ada di list
  ✅ Total pelanggan = 3

ACTUAL RESULT:
  [ ] PASS / [ ] FAIL

Notes: ___________________________________________
```

---

### TEST 2: SQL ERROR - MASALAH 2

**Skenario**: Verifikasi error SQL "Unknown column user_id" hilang

#### 2A. Retur Penjualan - Detail Retur
```
TEST: Retur Penjualan - Query Pelanggan
STEPS:
  1. Login sebagai user perusahaan A
  2. Buka Transaksi → Retur Penjualan → Tambah Retur
  3. Pilih penjualan yang ada
  4. Klik tombol "Detail Retur" atau buka edit retur

EXPECTED RESULT:
  ✅ Halaman terbuka tanpa error
  ✅ Dropdown pelanggan terpopulasi
  ✅ Tidak ada error di console/log:
     "SQLSTATE[42S22]: Unknown column 'user_id' in 'WHERE'"

ACTUAL RESULT:
  [ ] PASS / [ ] FAIL

Error message (if any): _____________________________
```

---

### TEST 3: UNIQUE CONSTRAINT - MASALAH 3

**Skenario**: Kode proses boleh sama di perusahaan berbeda

#### 3A. Kode Proses Tidak Bentrok
```
TEST: Proses Produksi - Unique Per Tenant
SETUP:
  - Perusahaan A sudah punya: PRO-001, PRO-002
  - Perusahaan B belum punya proses produksi

STEPS:
  1. Login sebagai user perusahaan B
  2. Buka Master Data → Proses Produksi
  3. Klik tombol Tambah
  4. Isi form tanpa mengisikan kode_proses (biarkan auto-generate)
  5. Klik Save

EXPECTED RESULT:
  ✅ Proses produksi berhasil dibuat
  ✅ Kode otomatis: PRO-001 (BUKAN PRO-003)
  ✅ Tidak ada error "Duplicate entry 'PRO-001'"
  ✅ Perusahaan A dan B boleh punya PRO-001 masing-masing

ACTUAL RESULT:
  [ ] PASS / [ ] FAIL

Generated kode: ____________________________________
Error message (if any): ____________________________
```

#### 3B. Verify Both Companies
```
TEST: Verify Kode Proses di Dua Perusahaan
STEPS:
  1. Login sebagai perusahaan A
  2. Buka Master Data → Proses Produksi
  3. Catat daftar kode proses

  4. Logout dan login sebagai perusahaan B
  5. Buka Master Data → Proses Produksi
  6. Catat daftar kode proses

EXPECTED RESULT:
  ✅ Perusahaan A: PRO-001, PRO-002, ...
  ✅ Perusahaan B: PRO-001, PRO-002, ...
  ✅ Keduanya boleh memiliki kode yang sama tanpa bentrok

ACTUAL RESULT:
  [ ] PASS / [ ] FAIL

Perusahaan A kode: __________________________________
Perusahaan B kode: __________________________________
```

---

### TEST 4: API ENDPOINTS

#### 4A. GET /api/produk/{id}/bom-details
```
TEST: API BOM Details - User Filter
SETUP:
  - Perusahaan A punya produk dengan ID = 10
  - Perusahaan B punya produk dengan ID = 11

STEPS:
  1. Login sebagai perusahaan A dengan token/session
  2. Call: GET /api/produk/10/bom-details
  3. Verifikasi response berhasil

  4. Cobalah call: GET /api/produk/11/bom-details
  5. Verifikasi error 404 (produk B tidak accessible dari A)

EXPECTED RESULT:
  ✅ GET /api/produk/10/bom-details → 200 OK
  ✅ GET /api/produk/11/bom-details → 404 Not Found
  ✅ Tidak ada data bocor dari tenant lain

ACTUAL RESULT - GET /10:
  Status Code: [ ] 200 / [ ] Other: _______
  Response: ______________________________________

ACTUAL RESULT - GET /11:
  Status Code: [ ] 404 / [ ] Other: _______
  Response: ______________________________________
```

#### 4B. POST /api/produk/{id}/bom-calculations
```
TEST: API BOM Calculations - User Filter
SETUP:
  - Perusahaan A punya produk dengan ID = 10
  - Perusahaan B punya produk dengan ID = 11

STEPS:
  1. Login sebagai perusahaan A
  2. Call: POST /api/produk/10/bom-calculations?qty=5
  3. Verifikasi response berhasil

  4. Cobalah call: POST /api/produk/11/bom-calculations?qty=5
  5. Verifikasi error 404

EXPECTED RESULT:
  ✅ POST /api/produk/10/bom-calculations → 200 OK dengan data
  ✅ POST /api/produk/11/bom-calculations → 404 Not Found
  ✅ Tidak ada kalkulasi BOM dari produk tenant lain

ACTUAL RESULT - POST /10:
  Status Code: [ ] 200 / [ ] Other: _______
  Response Data: ___________________________________

ACTUAL RESULT - POST /11:
  Status Code: [ ] 404 / [ ] Other: _______
  Response Error: __________________________________
```

---

### TEST 5: CROSS-TENANT SECURITY

#### 5A. Modify URL Parameter - Preter Produk Lain
```
TEST: URL Manipulation - Security
SETUP:
  - Login sebagai perusahaan A
  - Perusahaan B punya produk dengan ID = 100

STEPS:
  1. Buka halaman edit produk milik A: /master-data/produk/5/edit
  2. Ubah URL menjadi: /master-data/produk/100/edit (ID dari B)
  3. Tekan Enter

EXPECTED RESULT:
  ✅ Error 404 atau Unauthorized
  ✅ TIDAK bisa melihat/edit produk dari perusahaan B
  ✅ Sistem redirect atau error message

ACTUAL RESULT:
  [ ] PASS (protected) / [ ] FAIL (vulnerable)

Response/Behavior: __________________________________
```

---

## DATABASE VERIFICATION

### Check 1: Unique Constraint pada Proses Produksis

```sql
-- Run this query to verify unique constraint
SHOW INDEX FROM proses_produksis WHERE Key_name LIKE '%unique%' OR Key_name LIKE '%kode%';

-- Expected output:
-- Key_name: proses_produksis_user_kode_unique
-- Columns: user_id, kode_proses
```

**Status**: [ ] VERIFIED / [ ] NOT VERIFIED

### Check 2: Sample Data Multi-Tenant

```sql
-- Verify two companies can have same kode_proses
SELECT user_id, kode_proses, nama_proses 
FROM proses_produksis 
WHERE kode_proses = 'PRO-001'
ORDER BY user_id;

-- Expected output should show 2+ rows with same kode_proses but different user_id
-- Example:
-- user_id=10, kode_proses='PRO-001', nama_proses='Proses A'
-- user_id=20, kode_proses='PRO-001', nama_proses='Proses B'
```

**Result**: [ ] PASS / [ ] FAIL

---

## PERFORMANCE CHECK

### Check 1: Query Performance

```
TEST: Query Response Time
- Laporan Penjualan (1000+ records): < 2 detik
- Master Data Produk (500+ products): < 1 detik
- Laporan Stok: < 1.5 detik

Status:
  [ ] PASS (semua < threshold)
  [ ] FAIL (perlu optimization)
```

---

## FINAL CHECKLIST

- [ ] Semua functional tests PASS
- [ ] Tidak ada SQL error di log
- [ ] Database unique constraints verify
- [ ] Security test PASS (URL parameter)
- [ ] API endpoints filter by user_id
- [ ] Performance acceptable
- [ ] Ready untuk production deployment

---

## SIGN-OFF

**Tested By**: ______________________________  
**Date**: ______________________________  
**Status**: 
  - [ ] READY FOR PRODUCTION
  - [ ] NEED MORE TESTING
  - [ ] BLOCKED - Issues Found

**Issues Found**:
```
____________________________________________________________________
____________________________________________________________________
____________________________________________________________________
```

**Notes**:
```
____________________________________________________________________
____________________________________________________________________
____________________________________________________________________
```
