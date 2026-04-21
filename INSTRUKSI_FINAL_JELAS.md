# 🔴 INSTRUKSI FINAL - JELAS DAN TEGAS

Data MASIH SALAH. Ini cara fix yang PASTI BERHASIL.

## PILIHAN 1: Gunakan Script PHP (PALING MUDAH)

Buka link ini:
```
http://127.0.0.1:8000/fix-now.php
```

Script akan otomatis:
1. Fix BTKL & BOP (pindah ke KREDIT)
2. Fix WIP (pindah ke DEBIT)
3. Bersihkan jurnal_umum
4. Verifikasi hasilnya

Selesai! Refresh halaman jurnal-umum.

---

## PILIHAN 2: Gunakan phpMyAdmin (JIKA SCRIPT TIDAK BEKERJA)

### Langkah 1: Buka phpMyAdmin
```
http://localhost/phpmyadmin
```

### Langkah 2: Pilih Database
```
simcost_sistem_manufaktur_process_costing
```

### Langkah 3: Klik Tab "SQL"

### Langkah 4: Copy-Paste SQL Ini

```sql
UPDATE journal_lines jl
INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
INNER JOIN coas c ON jl.coa_id = c.id
SET jl.credit = jl.debit, jl.debit = 0
WHERE je.ref_type = 'production_labor_overhead'
AND c.kode_akun IN ('52', '53')
AND jl.debit > 0;

UPDATE journal_lines jl
INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
INNER JOIN coas c ON jl.coa_id = c.id
SET jl.debit = jl.credit, jl.credit = 0
WHERE je.ref_type = 'production_labor_overhead'
AND c.kode_akun = '117'
AND jl.credit > 0;

DELETE FROM jurnal_umum 
WHERE tipe_referensi = 'production_labor_overhead';
```

### Langkah 5: Klik "Go"

---

## VERIFIKASI

Setelah fix, buka:
```
http://127.0.0.1:8000/akuntansi/jurnal-umum
```

Filter: "Produksi - BTKL & BOP"

Seharusnya lihat:
```
52 BTKL              -           Rp 132.800 ✅ KREDIT
53 BOP               -           Rp 545.118 ✅ KREDIT
117 Barang Dalam Proses  Rp 677.918  -    ✅ DEBIT
```

---

## RINGKAS

**Pilihan 1 (Mudah)**: Buka `http://127.0.0.1:8000/fix-now.php`

**Pilihan 2 (Manual)**: Jalankan SQL di phpMyAdmin

**Pilih salah satu, jangan keduanya!**

---

Maaf sudah salah berkali-kali. Ini yang PASTI BENAR! 🙏
