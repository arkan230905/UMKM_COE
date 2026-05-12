-- ============================================================================
-- FIX JOURNAL POSITIONS - BTKL & BOP
-- ============================================================================
-- MASALAH: Posisi debit/kredit terbalik untuk BTKL, BOP, dan WIP
-- SOLUSI: Swap debit dan kredit untuk ketiga akun tersebut

-- ============================================================================
-- STEP 1: Fix BTKL (52) dan BOP (53) - Pindahkan dari DEBIT ke KREDIT
-- ============================================================================
UPDATE journal_lines jl
INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
INNER JOIN coas c ON jl.coa_id = c.id
SET jl.credit = jl.debit, jl.debit = 0
WHERE je.ref_type = 'production_labor_overhead'
AND c.kode_akun IN ('52', '53')
AND jl.debit > 0;

-- ============================================================================
-- STEP 2: Fix WIP (117) - Pindahkan dari KREDIT ke DEBIT
-- ============================================================================
UPDATE journal_lines jl
INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
INNER JOIN coas c ON jl.coa_id = c.id
SET jl.debit = jl.credit, jl.credit = 0
WHERE je.ref_type = 'production_labor_overhead'
AND c.kode_akun = '117'
AND jl.credit > 0;

-- ============================================================================
-- STEP 3: Bersihkan jurnal_umum (hapus duplikat data lama)
-- ============================================================================
DELETE FROM jurnal_umum 
WHERE tipe_referensi = 'production_labor_overhead';

-- ============================================================================
-- STEP 4: Verifikasi hasil
-- ============================================================================
SELECT 
    je.tanggal,
    c.kode_akun,
    c.nama_akun,
    jl.debit,
    jl.credit,
    CASE 
        WHEN c.kode_akun IN ('52', '53') AND jl.credit > 0 AND jl.debit = 0 THEN '✓ CORRECT'
        WHEN c.kode_akun = '117' AND jl.debit > 0 AND jl.credit = 0 THEN '✓ CORRECT'
        ELSE '❌ WRONG'
    END as status
FROM journal_lines jl
INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
LEFT JOIN coas c ON jl.coa_id = c.id
WHERE je.ref_type = 'production_labor_overhead'
ORDER BY je.tanggal DESC, c.kode_akun;
