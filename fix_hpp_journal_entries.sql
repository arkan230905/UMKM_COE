-- Step 1: Check all sales and their journal entries
SELECT 
    'Sales Records' as info,
    p.id,
    p.nomor_penjualan,
    p.tanggal,
    p.total_harga,
    pd.produk_id,
    pr.nama_produk
FROM penjualans p
LEFT JOIN penjualan_details pd ON pd.penjualan_id = p.id
LEFT JOIN produks pr ON pr.id = pd.produk_id
WHERE p.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
ORDER BY p.tanggal;

-- Step 2: Check existing journal entries
SELECT 
    'Journal Entries' as info,
    je.id,
    je.tanggal,
    je.memo,
    je.ref_type,
    je.ref_id
FROM journal_entries je
WHERE je.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
ORDER BY je.tanggal;

-- Step 3: Find the COA IDs we need
SELECT 'COA for HPP' as info, id, kode_akun, nama_akun FROM coas WHERE kode_akun = '1600';
SELECT 'COA for Ayam Crispy' as info, id, kode_akun, nama_akun FROM coas WHERE kode_akun = '1161';
SELECT 'COA for Ayam Goreng' as info, id, kode_akun, nama_akun FROM coas WHERE kode_akun = '1162';

-- Step 4: Delete incorrect journal lines (lines 78-79 that debit raw materials instead of crediting finished goods)
-- These are the lines that incorrectly debit 1142 and 1152
DELETE FROM journal_lines WHERE id IN (78, 79);

-- Step 5: Check if we need to create HPP COA (account 1600)
-- If it doesn't exist, we'll need to create it manually or let the system create it
