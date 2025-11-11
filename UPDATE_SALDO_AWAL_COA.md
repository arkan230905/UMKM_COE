# Update Saldo Awal COA

## Langkah-langkah Perbaikan Laporan Kas Bank

### 1. Sync COA ke Accounts

Jalankan seeder untuk sync data COA ke tabel Accounts:

```bash
php artisan db:seed --class=SyncCoaToAccountsSeeder
```

Atau jalankan manual via SQL:

```sql
-- Insert COA yang belum ada di accounts
INSERT INTO accounts (code, name, type, created_at, updated_at)
SELECT 
    kode_akun,
    nama_akun,
    CASE 
        WHEN LEFT(kode_akun, 1) = '1' THEN 'asset'
        WHEN LEFT(kode_akun, 1) = '2' THEN 'liability'
        WHEN LEFT(kode_akun, 1) = '3' THEN 'equity'
        WHEN LEFT(kode_akun, 1) = '4' THEN 'revenue'
        WHEN LEFT(kode_akun, 1) IN ('5', '6', '7') THEN 'expense'
        ELSE 'asset'
    END as type,
    NOW(),
    NOW()
FROM coas
WHERE is_akun_header = 0
  AND kode_akun NOT IN (SELECT code FROM accounts);
```

### 2. Update Saldo Awal untuk Akun Kas & Bank

Jika saldo awal masih 0, update dengan nilai yang sesuai:

```sql
-- Update saldo awal Kas
UPDATE coas 
SET saldo_awal = 10000000,
    tanggal_saldo_awal = '2025-01-01',
    posted_saldo_awal = 1
WHERE kode_akun = '101' OR nama_akun LIKE '%Kas%';

-- Update saldo awal Bank
UPDATE coas 
SET saldo_awal = 5000000,
    tanggal_saldo_awal = '2025-01-01',
    posted_saldo_awal = 1
WHERE kode_akun = '102' OR nama_akun LIKE '%Bank%';

-- Atau update semua akun Kas & Bank sekaligus
UPDATE coas 
SET saldo_awal = CASE 
    WHEN nama_akun LIKE '%Kas%' THEN 10000000
    WHEN nama_akun LIKE '%Bank%' THEN 5000000
    ELSE saldo_awal
END,
tanggal_saldo_awal = '2025-01-01',
posted_saldo_awal = 1
WHERE kategori = 'Kas & Bank';
```

### 3. Cek Data Setelah Update

```sql
-- Cek mapping COA dan Accounts
SELECT 
    c.kode_akun,
    c.nama_akun,
    c.saldo_awal,
    c.kategori,
    a.code,
    a.name,
    a.type
FROM coas c
LEFT JOIN accounts a ON c.kode_akun = a.code
WHERE c.kategori = 'Kas & Bank'
   OR c.nama_akun LIKE '%Kas%'
   OR c.nama_akun LIKE '%Bank%';
```

### 4. Cek Journal Entries

```sql
-- Cek total transaksi per akun
SELECT 
    a.code,
    a.name,
    COUNT(jl.id) as jumlah_transaksi,
    SUM(jl.debit) as total_debit,
    SUM(jl.credit) as total_credit,
    SUM(jl.debit - jl.credit) as saldo
FROM accounts a
LEFT JOIN journal_lines jl ON a.id = jl.account_id
WHERE a.code IN ('101', '102')
GROUP BY a.id, a.code, a.name;
```

### 5. Test Laporan

Setelah update, akses laporan:

```
http://localhost:8000/laporan/kas-bank
```

Expected Result:
- Saldo Awal tidak lagi 0
- Transaksi Masuk menampilkan total debit
- Transaksi Keluar menampilkan total credit
- Saldo Akhir = Saldo Awal + Masuk - Keluar

## Contoh Data untuk Testing

```sql
-- Insert saldo awal jika belum ada
UPDATE coas SET 
    saldo_awal = 15000000,
    tanggal_saldo_awal = '2025-01-01',
    posted_saldo_awal = 1
WHERE kode_akun = '101';

UPDATE coas SET 
    saldo_awal = 8000000,
    tanggal_saldo_awal = '2025-01-01',
    posted_saldo_awal = 1
WHERE kode_akun = '102';
```

## Troubleshooting

### Jika Saldo Awal masih 0:

1. Cek apakah kolom saldo_awal ada:
```sql
DESCRIBE coas;
```

2. Cek nilai saldo_awal:
```sql
SELECT kode_akun, nama_akun, saldo_awal FROM coas WHERE kode_akun IN ('101', '102');
```

3. Update manual jika perlu:
```sql
UPDATE coas SET saldo_awal = 10000000 WHERE kode_akun = '101';
```

### Jika Transaksi Masuk/Keluar = 0:

1. Cek apakah ada mapping:
```sql
SELECT c.kode_akun, a.id, a.code 
FROM coas c 
LEFT JOIN accounts a ON c.kode_akun = a.code 
WHERE c.kode_akun = '101';
```

2. Jika NULL, insert ke accounts:
```sql
INSERT INTO accounts (code, name, type, created_at, updated_at)
VALUES ('101', 'Kas', 'asset', NOW(), NOW());
```

3. Cek journal lines:
```sql
SELECT COUNT(*) FROM journal_lines jl
JOIN accounts a ON jl.account_id = a.id
WHERE a.code = '101';
```

### Jika Angka Tidak Sesuai:

1. Cek periode filter
2. Cek apakah semua transaksi sudah dijurnal
3. Cek apakah ada duplikasi jurnal

```sql
-- Cek transaksi yang belum dijurnal
SELECT 'Penjualan' as tipe, COUNT(*) as belum_dijurnal
FROM penjualans 
WHERE id NOT IN (SELECT reference_id FROM journal_entries WHERE reference_type = 'penjualan')
UNION ALL
SELECT 'Pembelian', COUNT(*)
FROM pembelians 
WHERE id NOT IN (SELECT reference_id FROM journal_entries WHERE reference_type = 'pembelian');
```

## Quick Fix Script

Jalankan script ini untuk fix semua sekaligus:

```sql
-- 1. Sync COA to Accounts
INSERT INTO accounts (code, name, type, created_at, updated_at)
SELECT 
    kode_akun,
    nama_akun,
    CASE 
        WHEN LEFT(kode_akun, 1) = '1' THEN 'asset'
        WHEN LEFT(kode_akun, 1) = '2' THEN 'liability'
        WHEN LEFT(kode_akun, 1) = '3' THEN 'equity'
        WHEN LEFT(kode_akun, 1) = '4' THEN 'revenue'
        ELSE 'expense'
    END,
    NOW(),
    NOW()
FROM coas
WHERE is_akun_header = 0
  AND kode_akun NOT IN (SELECT code FROM accounts);

-- 2. Update Saldo Awal Kas & Bank
UPDATE coas 
SET saldo_awal = CASE 
    WHEN kode_akun = '101' THEN 10000000
    WHEN kode_akun = '102' THEN 5000000
    WHEN nama_akun LIKE '%Kas%' THEN 10000000
    WHEN nama_akun LIKE '%Bank%' THEN 5000000
    ELSE saldo_awal
END,
tanggal_saldo_awal = '2025-01-01',
posted_saldo_awal = 1
WHERE kategori = 'Kas & Bank'
   OR nama_akun LIKE '%Kas%'
   OR nama_akun LIKE '%Bank%';

-- 3. Verify
SELECT 
    c.kode_akun,
    c.nama_akun,
    c.saldo_awal,
    a.code,
    COUNT(jl.id) as jumlah_transaksi
FROM coas c
LEFT JOIN accounts a ON c.kode_akun = a.code
LEFT JOIN journal_lines jl ON a.id = jl.account_id
WHERE c.kategori = 'Kas & Bank'
GROUP BY c.id, c.kode_akun, c.nama_akun, c.saldo_awal, a.code;
```
