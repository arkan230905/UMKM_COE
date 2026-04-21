-- Step 1: Delete the entries we added to jurnal_umum (IDs 204 and 205)
DELETE FROM jurnal_umum WHERE id IN (204, 205);

-- Step 2: Create journal_entry for sale on 22/04
INSERT INTO journal_entries (tanggal, memo, ref_type, ref_id, created_at, updated_at)
VALUES ('2026-04-22', 'Penjualan #SJ-20260422-001', 'sale', 2, NOW(), NOW());

-- Get the inserted ID
SET @entry_id = LAST_INSERT_ID();

-- Step 3: Create journal_lines for the sale
-- Line 1: Debit Kas (coa_id 112)
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES (@entry_id, 112, 1393050.00, 0.00, 'Penerimaan tunai penjualan', NOW(), NOW());

-- Line 2: Credit Pendapatan (coa_id 41)
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES (@entry_id, 41, 0.00, 1393050.00, 'Pendapatan penjualan produk', NOW(), NOW());

-- Step 4: Verify all sales are now in journal_entries
SELECT 
    je.id,
    je.tanggal,
    je.memo,
    je.ref_type,
    je.ref_id,
    COUNT(jl.id) as line_count
FROM journal_entries je
LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
WHERE je.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
AND je.ref_type = 'sale'
GROUP BY je.id, je.tanggal, je.memo, je.ref_type, je.ref_id
ORDER BY je.tanggal;
