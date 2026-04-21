# Contoh Output Analisis Duplikasi Pembayaran Beban

## Skenario 1: Tidak Ada Duplikasi (Data Clean)

### Output dari `debug_pembayaran_beban.php`

```
========================================
ANALISIS DUPLIKASI JOURNAL ENTRIES
Pembayaran Beban: 28/04/2026 - 29/04/2026
========================================

1. JOURNAL ENTRIES (28-29 April 2026)
-----------------------------------
Total entries ditemukan: 3

┌────────┬──────────────┬──────────────┬──────────────────────────┬───────┬──────────────┬──────────────┐
│ Entry  │ Tanggal      │ Ref Type     │ Deskripsi                │ Lines │ Total Debit  │ Total Credit │
├────────┼──────────────┼──────────────┼──────────────────────────┼───────┼──────────────┼──────────────┤
│ 1001   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Listrik │ 2     │ 500,000.00   │ 500,000.00   │
│ 1002   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Air     │ 2     │ 250,000.00   │ 250,000.00   │
│ 1003   │ 2026-04-29   │ expense_pay  │ Pembayaran Beban Telepon │ 2     │ 150,000.00   │ 150,000.00   │
└────────┴──────────────┴──────────────┴──────────────────────────┴───────┴──────────────┴──────────────┘

2. DETAIL LINES SETIAP ENTRY
-----------------------------------

Entry ID: 1001 - 2026-04-28 - Pembayaran Beban Listrik
  Detail Lines:
    - Account 5001: Debit=500,000.00, Credit=0.00
    - Account 1001: Debit=0.00, Credit=500,000.00

Entry ID: 1002 - 2026-04-28 - Pembayaran Beban Air
  Detail Lines:
    - Account 5002: Debit=250,000.00, Credit=0.00
    - Account 1001: Debit=0.00, Credit=250,000.00

Entry ID: 1003 - 2026-04-29 - Pembayaran Beban Telepon
  Detail Lines:
    - Account 5003: Debit=150,000.00, Credit=0.00
    - Account 1001: Debit=0.00, Credit=150,000.00

3. ANALISIS DUPLIKASI (Tanggal + Deskripsi)
-----------------------------------
✓ Tidak ada duplikasi berdasarkan tanggal dan deskripsi yang sama.

4. ENTRIES DENGAN NOMINAL SAMA PER AKUN
-----------------------------------
✓ Tidak ada entries dengan nominal dan akun yang sama.

5. SUMMARY
-----------------------------------
Total Journal Entries (28-29 April): 3
Total Journal Lines: 6
Duplikasi Terdeteksi: 0
Entries dengan Nominal Sama: 0

========================================
ANALISIS SELESAI - DATA CLEAN ✓
========================================
```

---

## Skenario 2: Ada Duplikasi (Perlu Cleanup)

### Output dari `debug_pembayaran_beban.php`

```
========================================
ANALISIS DUPLIKASI JOURNAL ENTRIES
Pembayaran Beban: 28/04/2026 - 29/04/2026
========================================

1. JOURNAL ENTRIES (28-29 April 2026)
-----------------------------------
Total entries ditemukan: 4

┌────────┬──────────────┬──────────────┬──────────────────────────┬───────┬──────────────┬──────────────┐
│ Entry  │ Tanggal      │ Ref Type     │ Deskripsi                │ Lines │ Total Debit  │ Total Credit │
├────────┼──────────────┼──────────────┼───────────────────────────┼───────┼──────────────┼──────────────┤
│ 1001   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Listrik │ 2     │ 500,000.00   │ 500,000.00   │
│ 1002   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Listrik │ 2     │ 500,000.00   │ 500,000.00   │ ⚠️ DUPLIKASI
│ 1003   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Air     │ 2     │ 250,000.00   │ 250,000.00   │
│ 1004   │ 2026-04-29   │ expense_pay  │ Pembayaran Beban Telepon │ 2     │ 150,000.00   │ 150,000.00   │
└────────┴──────────────┴──────────────┴──────────────────────────┴───────┴──────────────┴──────────────┘

2. DETAIL LINES SETIAP ENTRY
-----------------------------------

Entry ID: 1001 - 2026-04-28 - Pembayaran Beban Listrik
  Detail Lines:
    - Account 5001: Debit=500,000.00, Credit=0.00
    - Account 1001: Debit=0.00, Credit=500,000.00

Entry ID: 1002 - 2026-04-28 - Pembayaran Beban Listrik
  Detail Lines:
    - Account 5001: Debit=500,000.00, Credit=0.00
    - Account 1001: Debit=0.00, Credit=500,000.00

Entry ID: 1003 - 2026-04-28 - Pembayaran Beban Air
  Detail Lines:
    - Account 5002: Debit=250,000.00, Credit=0.00
    - Account 1001: Debit=0.00, Credit=250,000.00

Entry ID: 1004 - 2026-04-29 - Pembayaran Beban Telepon
  Detail Lines:
    - Account 5003: Debit=150,000.00, Credit=0.00
    - Account 1001: Debit=0.00, Credit=150,000.00

3. ANALISIS DUPLIKASI (Tanggal + Deskripsi)
-----------------------------------
⚠️ Duplikasi ditemukan: 1

Entry 1001 dan Entry 1002
  Tanggal: 2026-04-28
  Deskripsi: Pembayaran Beban Listrik
  Created: 2026-04-28 10:00:00 vs 2026-04-28 10:05:00

4. ENTRIES DENGAN NOMINAL SAMA PER AKUN
-----------------------------------
⚠️ Entries dengan nominal sama: 2

Entry 1001 dan Entry 1002
  Tanggal: 2026-04-28
  Account: 5001
  Debit: 500,000.00, Credit: 0.00

Entry 1001 dan Entry 1002
  Tanggal: 2026-04-28
  Account: 1001
  Debit: 0.00, Credit: 500,000.00

5. SUMMARY
-----------------------------------
Total Journal Entries (28-29 April): 4
Total Journal Lines: 8
Duplikasi Terdeteksi: 1
Entries dengan Nominal Sama: 2

========================================
ANALISIS SELESAI - ADA DUPLIKASI ⚠️
========================================
```

### Rekomendasi Cleanup

```
LANGKAH-LANGKAH CLEANUP:

1. Backup Database
   mysqldump -h 127.0.0.1 -u root eadt_umkm > backup_2026-04-29.sql

2. Identifikasi Entry yang Harus Dihapus
   - Entry 1001: Created 2026-04-28 10:00:00 (ORIGINAL)
   - Entry 1002: Created 2026-04-28 10:05:00 (DUPLIKASI) ← HAPUS INI

3. Hapus Entry Duplikasi
   DELETE FROM journal_lines WHERE journal_entry_id = 1002;
   DELETE FROM journal_entries WHERE id = 1002;

4. Verifikasi Hasil
   - Jalankan analisis lagi
   - Pastikan duplikasi sudah hilang
   - Cek balance sheet masih seimbang

5. Hasil Setelah Cleanup
   Total Journal Entries (28-29 April): 3
   Total Journal Lines: 6
   Duplikasi Terdeteksi: 0
   Entries dengan Nominal Sama: 0
   ✓ DATA CLEAN
```

---

## Skenario 3: Multiple Duplikasi

### Output dari `debug_pembayaran_beban.php`

```
========================================
ANALISIS DUPLIKASI JOURNAL ENTRIES
Pembayaran Beban: 28/04/2026 - 29/04/2026
========================================

1. JOURNAL ENTRIES (28-29 April 2026)
-----------------------------------
Total entries ditemukan: 6

┌────────┬──────────────┬──────────────┬──────────────────────────┬───────┬──────────────┬──────────────┐
│ Entry  │ Tanggal      │ Ref Type     │ Deskripsi                │ Lines │ Total Debit  │ Total Credit │
├────────┼──────────────┼──────────────┼──────────────────────────┼───────┼──────────────┼──────────────┤
│ 1001   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Listrik │ 2     │ 500,000.00   │ 500,000.00   │
│ 1002   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Listrik │ 2     │ 500,000.00   │ 500,000.00   │ ⚠️ DUP
│ 1003   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Listrik │ 2     │ 500,000.00   │ 500,000.00   │ ⚠️ DUP
│ 1004   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Air     │ 2     │ 250,000.00   │ 250,000.00   │
│ 1005   │ 2026-04-28   │ expense_pay  │ Pembayaran Beban Air     │ 2     │ 250,000.00   │ 250,000.00   │ ⚠️ DUP
│ 1006   │ 2026-04-29   │ expense_pay  │ Pembayaran Beban Telepon │ 2     │ 150,000.00   │ 150,000.00   │
└────────┴──────────────┴──────────────┴──────────────────────────┴───────┴──────────────┴──────────────┘

3. ANALISIS DUPLIKASI (Tanggal + Deskripsi)
-----------------------------------
⚠️ Duplikasi ditemukan: 3

Entry 1001 dan Entry 1002
  Tanggal: 2026-04-28
  Deskripsi: Pembayaran Beban Listrik
  Created: 2026-04-28 10:00:00 vs 2026-04-28 10:05:00

Entry 1001 dan Entry 1003
  Tanggal: 2026-04-28
  Deskripsi: Pembayaran Beban Listrik
  Created: 2026-04-28 10:00:00 vs 2026-04-28 10:10:00

Entry 1004 dan Entry 1005
  Tanggal: 2026-04-28
  Deskripsi: Pembayaran Beban Air
  Created: 2026-04-28 11:00:00 vs 2026-04-28 11:05:00

4. ENTRIES DENGAN NOMINAL SAMA PER AKUN
-----------------------------------
⚠️ Entries dengan nominal sama: 6

Entry 1001 dan Entry 1002 - Account 5001 - Debit: 500,000.00
Entry 1001 dan Entry 1003 - Account 5001 - Debit: 500,000.00
Entry 1002 dan Entry 1003 - Account 5001 - Debit: 500,000.00
Entry 1004 dan Entry 1005 - Account 5002 - Debit: 250,000.00
... (dan seterusnya)

5. SUMMARY
-----------------------------------
Total Journal Entries (28-29 April): 6
Total Journal Lines: 12
Duplikasi Terdeteksi: 3
Entries dengan Nominal Sama: 6

========================================
ANALISIS SELESAI - MULTIPLE DUPLIKASI ⚠️⚠️⚠️
========================================
```

### Rekomendasi Cleanup

```
LANGKAH-LANGKAH CLEANUP:

1. Backup Database
   mysqldump -h 127.0.0.1 -u root eadt_umkm > backup_2026-04-29.sql

2. Identifikasi Entries yang Harus Dihapus
   
   Pembayaran Beban Listrik:
   - Entry 1001: Created 2026-04-28 10:00:00 (ORIGINAL) ← KEEP
   - Entry 1002: Created 2026-04-28 10:05:00 (DUPLIKASI) ← DELETE
   - Entry 1003: Created 2026-04-28 10:10:00 (DUPLIKASI) ← DELETE
   
   Pembayaran Beban Air:
   - Entry 1004: Created 2026-04-28 11:00:00 (ORIGINAL) ← KEEP
   - Entry 1005: Created 2026-04-28 11:05:00 (DUPLIKASI) ← DELETE

3. Hapus Entries Duplikasi
   DELETE FROM journal_lines WHERE journal_entry_id IN (1002, 1003, 1005);
   DELETE FROM journal_entries WHERE id IN (1002, 1003, 1005);

4. Verifikasi Hasil
   - Jalankan analisis lagi
   - Pastikan semua duplikasi sudah hilang
   - Cek balance sheet masih seimbang

5. Hasil Setelah Cleanup
   Total Journal Entries (28-29 April): 3
   Total Journal Lines: 6
   Duplikasi Terdeteksi: 0
   Entries dengan Nominal Sama: 0
   ✓ DATA CLEAN
```

---

## Query Output Contoh

### Query 1: Lihat Semua Entries
```sql
SELECT 
    je.id,
    je.entry_date,
    je.ref_type,
    je.description,
    COUNT(jl.id) as line_count,
    SUM(jl.debit) as total_debit,
    SUM(jl.credit) as total_credit
FROM journal_entries je
LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
GROUP BY je.id
ORDER BY je.entry_date, je.id;
```

**Output**:
```
+-----+---------------------+---------------+---------------------------+------------+-------------+---------------+
| id  | entry_date          | ref_type      | description               | line_count | total_debit | total_credit  |
+-----+---------------------+---------------+---------------------------+------------+-------------+---------------+
| 1   | 2026-04-28 10:00:00 | expense_pay   | Pembayaran Beban Listrik  |          2 |     500000  |       500000  |
| 2   | 2026-04-28 10:05:00 | expense_pay   | Pembayaran Beban Listrik  |          2 |     500000  |       500000  |
| 3   | 2026-04-28 11:00:00 | expense_pay   | Pembayaran Beban Air      |          2 |     250000  |       250000  |
| 4   | 2026-04-29 09:00:00 | expense_pay   | Pembayaran Beban Telepon  |          2 |     150000  |       150000  |
+-----+---------------------+---------------+---------------------------+------------+-------------+---------------+
```

### Query 2: Cari Duplikasi
```sql
SELECT 
    je1.id as entry1_id,
    je2.id as entry2_id,
    je1.entry_date,
    je1.description,
    je1.created_at as created1,
    je2.created_at as created2
FROM journal_entries je1
JOIN journal_entries je2 ON 
    DATE(je1.entry_date) = DATE(je2.entry_date) AND
    je1.description = je2.description AND
    je1.id < je2.id
WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29';
```

**Output**:
```
+------------+------------+---------------------+---------------------------+---------------------+---------------------+
| entry1_id  | entry2_id  | entry_date          | description               | created1            | created2            |
+------------+------------+---------------------+---------------------------+---------------------+---------------------+
|          1 |          2 | 2026-04-28 10:00:00 | Pembayaran Beban Listrik  | 2026-04-28 10:00:00 | 2026-04-28 10:05:00 |
+------------+------------+---------------------+---------------------------+---------------------+---------------------+
```

---

## Kesimpulan

- **Skenario 1**: Data clean, tidak ada duplikasi ✅
- **Skenario 2**: Ada 1 duplikasi, perlu dihapus entry 1002 ⚠️
- **Skenario 3**: Ada 3 duplikasi, perlu dihapus entries 1002, 1003, 1005 ⚠️⚠️⚠️

Gunakan script `debug_pembayaran_beban.php` untuk analisis otomatis dan ikuti rekomendasi cleanup yang diberikan.

---

**Terakhir Diupdate**: 2026-04-29
