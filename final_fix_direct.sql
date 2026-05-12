-- Lihat apa yang ada sekarang
SELECT 'BEFORE FIX' as status, jl.id, jl.journal_entry_id, jl.coa_id, jl.debit, jl.credit
FROM journal_lines jl
WHERE jl.journal_entry_id IN (
    SELECT id FROM journal_entries WHERE tanggal = '2026-04-22' AND ref_type = 'sale'
);

-- Hapus semua journal_lines untuk penjualan 22/04
DELETE FROM journal_lines 
WHERE journal_entry_id IN (
    SELECT id FROM journal_entries WHERE tanggal = '2026-04-22' AND ref_type = 'sale'
);

-- Ambil journal_entry_id untuk penjualan 22/04
SELECT @je_id := id FROM journal_entries WHERE tanggal = '2026-04-22' AND ref_type = 'sale' LIMIT 1;

-- Ambil coa_id yang benar dari penjualan 21/04
SELECT @coa_kas := coa_id FROM journal_lines WHERE journal_entry_id = 33 AND debit > 0 LIMIT 1;
SELECT @coa_pendapatan := coa_id FROM journal_lines WHERE journal_entry_id = 33 AND credit > 0 LIMIT 1;

-- Buat journal_lines baru dengan coa_id yang benar
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
(@je_id, @coa_kas, 1393050.00, 0.00, 'Penerimaan tunai penjualan', NOW(), NOW()),
(@je_id, @coa_pendapatan, 0.00, 1393050.00, 'Pendapatan penjualan produk', NOW(), NOW());

-- Verifikasi hasil
SELECT 'AFTER FIX' as status, jl.id, jl.journal_entry_id, jl.coa_id, c.kode_akun, c.nama_akun, jl.debit, jl.credit
FROM journal_lines jl
JOIN coas c ON c.id = jl.coa_id
WHERE jl.journal_entry_id IN (
    SELECT id FROM journal_entries WHERE tanggal = '2026-04-22' AND ref_type = 'sale'
);
