# 🔧 JALANKAN SQL INI SEKARANG DI phpMyAdmin!

Data MASIH SALAH. Kita perlu fix dengan SQL langsung.

## Langkah 1: Buka phpMyAdmin

```
http://localhost/phpmyadmin
```

## Langkah 2: Pilih Database

Database: `simcost_sistem_manufaktur_process_costing`

## Langkah 3: Klik Tab "SQL"

## Langkah 4: Copy-Paste SQL Ini

```sql
-- Fix BTKL (52) dan BOP (53) - Pindahkan dari DEBIT ke KREDIT
UPDATE journal_lines jl
INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
INNER JOIN coas c ON jl.coa_id = c.id
SET jl.credit = jl.debit, jl.debit = 0
WHERE je.ref_type = 'production_labor_overhead'
AND c.kode_akun IN ('52', '53')
AND jl.debit > 0;

-- Fix WIP (117) - Pindahkan dari KREDIT ke DEBIT
UPDATE journal_lines jl
INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
INNER JOIN coas c ON jl.coa_id = c.id
SET jl.debit = jl.credit, jl.credit = 0
WHERE je.ref_type = 'production_labor_overhead'
AND c.kode_akun = '117'
AND jl.credit > 0;

-- Bersihkan jurnal_umum
DELETE FROM jurnal_umum 
WHERE tipe_referensi = 'production_labor_overhead';
```

## Langkah 5: Klik "Go"

Script akan:
1. ✓ Pindahkan BTKL & BOP ke KREDIT
2. ✓ Pindahkan WIP ke DEBIT
3. ✓ Bersihkan jurnal_umum

## Langkah 6: Verifikasi

Buka: `http://127.0.0.1:8000/akuntansi/jurnal-umum`

Seharusnya sekarang:
- ✅ BTKL (52) di KREDIT
- ✅ BOP (53) di KREDIT
- ✅ WIP (117) di DEBIT

---

**Itu aja! Jalankan SQL di phpMyAdmin sekarang!**

Maaf sudah salah berkali-kali. Ini yang benar! 🙏
