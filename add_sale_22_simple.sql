-- Delete entries from jurnal_umum if they exist
DELETE FROM jurnal_umum WHERE id >= 204 AND tanggal = '2026-04-22' AND keterangan LIKE '%Penjualan%';

-- Create journal entry for sale 22/04
INSERT INTO journal_entries (tanggal, memo, ref_type, ref_id, created_at, updated_at)
VALUES ('2026-04-22', 'Penjualan #SJ-20260422-001', 'sale', 2, '2026-04-22 08:45:02', '2026-04-22 08:45:02');

-- Create journal lines (using the last inserted ID)
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT 
    LAST_INSERT_ID(),
    112,
    1393050.00,
    0.00,
    'Penerimaan tunai penjualan',
    '2026-04-22 08:45:02',
    '2026-04-22 08:45:02';

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT 
    LAST_INSERT_ID(),
    41,
    0.00,
    1393050.00,
    'Pendapatan penjualan produk',
    '2026-04-22 08:45:02',
    '2026-04-22 08:45:02';

-- Verify
SELECT 'Created journal_entry' as info, * FROM journal_entries WHERE tanggal = '2026-04-22' AND ref_type = 'sale';
SELECT 'Created journal_lines' as info, jl.*, c.kode_akun FROM journal_lines jl 
JOIN journal_entries je ON je.id = jl.journal_entry_id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-22' AND je.ref_type = 'sale';
