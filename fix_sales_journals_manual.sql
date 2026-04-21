-- Step 1: Delete incorrect journal lines first (foreign key constraint)
DELETE FROM journal_lines WHERE journal_entry_id IN (33, 9);

-- Step 2: Delete the journal entries
DELETE FROM journal_entries WHERE id IN (33, 9);

-- Step 3: Create journal entry for sale on 2026-04-21 (Ayam Crispy Macdi)
INSERT INTO journal_entries (tanggal, memo, ref_type, ref_id, created_at, updated_at)
VALUES ('2026-04-21', 'Penjualan #SJ-20260421-001', 'sale', 1, NOW(), NOW());

-- Get the last inserted ID (should be the new entry_id)
SET @entry_id_21 = LAST_INSERT_ID();

-- Create journal lines for sale on 21/04
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
(@entry_id_21, 112, 1393050.00, 0.00, 'Penerimaan tunai penjualan', NOW(), NOW()),
(@entry_id_21, 41, 0.00, 1393050.00, 'Pendapatan penjualan produk', NOW(), NOW());

-- Step 4: Create HPP journal entry for sale on 2026-04-21
INSERT INTO journal_entries (tanggal, memo, ref_type, ref_id, created_at, updated_at)
VALUES ('2026-04-21', 'HPP Penjualan SJ-20260421-001', 'sale', 1, NOW(), NOW());

SET @hpp_entry_id_21 = LAST_INSERT_ID();

-- HPP for Ayam Crispy: 50 units * 22046.99 = 1,102,349.50
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
(@hpp_entry_id_21, 185, 1102349.50, 0.00, 'Harga Pokok Penjualan', NOW(), NOW()),
(@hpp_entry_id_21, 124, 0.00, 1102349.50, 'Pengurangan persediaan barang jadi', NOW(), NOW());

-- Step 5: Create journal entry for sale on 2026-04-22 (Ayam Goreng Bundo)
INSERT INTO journal_entries (tanggal, memo, ref_type, ref_id, created_at, updated_at)
VALUES ('2026-04-22', 'Penjualan #SJ-20260422-001', 'sale', 2, NOW(), NOW());

SET @entry_id_22 = LAST_INSERT_ID();

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
(@entry_id_22, 112, 1393050.00, 0.00, 'Penerimaan tunai penjualan', NOW(), NOW()),
(@entry_id_22, 41, 0.00, 1393050.00, 'Pendapatan penjualan produk', NOW(), NOW());

-- Step 6: Create HPP journal entry for sale on 2026-04-22
INSERT INTO journal_entries (tanggal, memo, ref_type, ref_id, created_at, updated_at)
VALUES ('2026-04-22', 'HPP Penjualan SJ-20260422-001', 'sale', 2, NOW(), NOW());

SET @hpp_entry_id_22 = LAST_INSERT_ID();

-- HPP for Ayam Goreng: 50 units * 18946.99 = 947,349.50
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
(@hpp_entry_id_22, 185, 947349.50, 0.00, 'Harga Pokok Penjualan', NOW(), NOW()),
(@hpp_entry_id_22, 125, 0.00, 947349.50, 'Pengurangan persediaan barang jadi', NOW(), NOW());

-- Step 7: Create journal entry for sale on 2026-04-23 (Ayam Goreng Bundo)
INSERT INTO journal_entries (tanggal, memo, ref_type, ref_id, created_at, updated_at)
VALUES ('2026-04-23', 'Penjualan #SJ-20260423-001', 'sale', 3, NOW(), NOW());

SET @entry_id_23 = LAST_INSERT_ID();

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
(@entry_id_23, 112, 1393050.00, 0.00, 'Penerimaan tunai penjualan', NOW(), NOW()),
(@entry_id_23, 41, 0.00, 1393050.00, 'Pendapatan penjualan produk', NOW(), NOW());

-- Step 8: Create HPP journal entry for sale on 2026-04-23
INSERT INTO journal_entries (tanggal, memo, ref_type, ref_id, created_at, updated_at)
VALUES ('2026-04-23', 'HPP Penjualan SJ-20260423-001', 'sale', 3, NOW(), NOW());

SET @hpp_entry_id_23 = LAST_INSERT_ID();

-- HPP for Ayam Goreng: 50 units * 18946.99 = 947,349.50
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
(@hpp_entry_id_23, 185, 947349.50, 0.00, 'Harga Pokok Penjualan', NOW(), NOW()),
(@hpp_entry_id_23, 125, 0.00, 947349.50, 'Pengurangan persediaan barang jadi', NOW(), NOW());

-- Verify the results
SELECT 
    je.id as entry_id,
    je.tanggal,
    je.memo,
    jl.id as line_id,
    jl.debit,
    jl.credit,
    c.kode_akun,
    c.nama_akun
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
ORDER BY je.tanggal, je.id, jl.id;
