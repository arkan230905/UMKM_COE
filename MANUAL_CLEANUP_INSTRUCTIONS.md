# MANUAL CLEANUP INSTRUCTIONS

## Problem
Your jurnal umum shows duplicate and wrong depreciation entries. The correct values from asset master data are:
- Mesin: Rp 1.333.333 ✅
- Peralatan: Rp 657.550 (not the wrong values showing)
- Kendaraan: Rp 888.889 ✅

## SOLUTION: Manual Database Cleanup

### Option 1: Use Web Browser (Recommended)
1. Open your web browser
2. Go to: `http://localhost:8000/emergency-cleanup-now`
3. This will automatically clean up all wrong entries and insert correct ones
4. Then check: `http://localhost:8000/akuntansi/jurnal-umum`

### Option 2: Direct Database Access
If the web route doesn't work, use phpMyAdmin or MySQL command line:

```sql
-- Delete all old depreciation entries
DELETE FROM jurnal_umum 
WHERE tanggal = '2026-04-30' 
AND (
    keterangan LIKE '%Penyusutan%' 
    OR keterangan LIKE '%GL) 2026-04%'
    OR keterangan LIKE '%SM) 2026-04%' 
    OR keterangan LIKE '%SYD) 2026-04%'
);

-- Insert correct values
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at) VALUES
(555, '2026-04-30', 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04', 1333333, 0, 'AST-MESIN', 'depreciation', 1, NOW(), NOW()),
(126, '2026-04-30', 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04', 0, 1333333, 'AST-MESIN', 'depreciation', 1, NOW(), NOW()),
(553, '2026-04-30', 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04', 659474, 0, 'AST-PERALATAN', 'depreciation', 1, NOW(), NOW()),
(120, '2026-04-30', 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04', 0, 659474, 'AST-PERALATAN', 'depreciation', 1, NOW(), NOW()),
(554, '2026-04-30', 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04', 888889, 0, 'AST-KENDARAAN', 'depreciation', 1, NOW(), NOW()),
(124, '2026-04-30', 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04', 0, 888889, 'AST-KENDARAAN', 'depreciation', 1, NOW(), NOW());
```

### Option 3: Laravel Artisan (if working)
```bash
php artisan cleanup:depreciation
```

## Expected Result
After cleanup, jurnal umum should show ONLY:
- Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04: Rp 1.333.333
- Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04: Rp 659.474  
- Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04: Rp 888.889

NO MORE entries with (GL), (SM), (SYD) patterns!
NO MORE duplicate entries!
NO MORE wrong values!

## Files Updated
- `routes/web.php` - Fixed emergency cleanup route with correct Peralatan value (Rp 659.474)
- `cleanup_now.sql` - Direct SQL script for manual execution
- `app/Console/Commands/CleanupDepreciation.php` - Laravel command backup