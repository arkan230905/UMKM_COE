-- FINAL CLEANUP - Remove all old depreciation entries and insert correct ones

-- Step 1: Delete ALL old depreciation entries from jurnal_umum
DELETE FROM jurnal_umum 
WHERE tanggal = '2026-04-30' 
AND (
    keterangan LIKE '%Penyusutan%' 
    OR keterangan LIKE '%GL) 2026-04%'
    OR keterangan LIKE '%SM) 2026-04%' 
    OR keterangan LIKE '%SYD) 2026-04%'
);

-- Step 2: Insert CORRECT depreciation values (matching asset master data)
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at) VALUES
-- Mesin Produksi - Rp 1.333.333
(555, '2026-04-30', 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04', 1333333, 0, 'AST-MESIN', 'depreciation', 1, NOW(), NOW()),
(126, '2026-04-30', 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04', 0, 1333333, 'AST-MESIN', 'depreciation', 1, NOW(), NOW()),

-- Peralatan Produksi - Rp 659.474 (CORRECT VALUE from detail page)
(553, '2026-04-30', 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04', 659474, 0, 'AST-PERALATAN', 'depreciation', 1, NOW(), NOW()),
(120, '2026-04-30', 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04', 0, 659474, 'AST-PERALATAN', 'depreciation', 1, NOW(), NOW()),

-- Kendaraan - Rp 888.889
(554, '2026-04-30', 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04', 888889, 0, 'AST-KENDARAAN', 'depreciation', 1, NOW(), NOW()),
(124, '2026-04-30', 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04', 0, 888889, 'AST-KENDARAAN', 'depreciation', 1, NOW(), NOW());

-- Verify results
SELECT 'CLEANUP COMPLETE - Check results:' as message;
SELECT tanggal, keterangan, debit, kredit FROM jurnal_umum WHERE tanggal = '2026-04-30' AND keterangan LIKE '%Penyusutan%' ORDER BY keterangan;