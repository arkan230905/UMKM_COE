-- Script untuk membersihkan data transaksi yang tidak seharusnya ada untuk company baru
-- Jalankan script ini untuk membersihkan data transaksi dari company_id yang baru dibuat

-- GANTI [NEW_COMPANY_ID] dengan ID company yang baru Anda buat
-- Untuk mencari company_id yang baru, jalankan: SELECT id, nama FROM companies ORDER BY id DESC LIMIT 5;

-- Hapus data transaksi produksi untuk company baru
DELETE FROM produksi_details 
WHERE produksi_id IN (
    SELECT id FROM produksis 
    WHERE produk_id IN (
        SELECT id FROM produk 
        WHERE company_id = [NEW_COMPANY_ID]
    )
);

DELETE FROM produksis 
WHERE produk_id IN (
    SELECT id FROM produk 
    WHERE company_id = [NEW_COMPANY_ID]
);

-- Hapus data produk untuk company baru
DELETE FROM produk WHERE company_id = [NEW_COMPANY_ID];

-- Hapus data bahan baku untuk company baru  
DELETE FROM bahan_bakus WHERE company_id = [NEW_COMPANY_ID];

-- Hapus data transaksi lainnya untuk company baru
DELETE FROM pembelian_details 
WHERE pembelian_id IN (
    SELECT id FROM pembelians 
    WHERE company_id = [NEW_COMPANY_ID]
);

DELETE FROM pembelians WHERE company_id = [NEW_COMPANY_ID];

DELETE FROM penjualan_details 
WHERE penjualan_id IN (
    SELECT id FROM penjualans 
    WHERE company_id = [NEW_COMPANY_ID]
);

DELETE FROM penjualans WHERE company_id = [NEW_COMPANY_ID];

-- Hapus jurnal yang terkait company baru
DELETE FROM journal_lines 
WHERE journal_entry_id IN (
    SELECT id FROM journal_entries 
    WHERE company_id = [NEW_COMPANY_ID]
);

DELETE FROM journal_entries WHERE company_id = [NEW_COMPANY_ID];

-- Hapus stock movements untuk company baru
DELETE FROM stock_movements WHERE company_id = [NEW_COMPANY_ID];

-- CATATAN: JANGAN hapus data COA, satuan, jenis aset, kategori aset, jabatan
-- karena ini adalah master data yang diperlukan

-- Setelah menjalankan script ini, company baru akan memiliki:
-- - COA accounts (kosong, saldo 0)
-- - Master data (satuan, jenis aset, dll) 
-- - TANPA data transaksi
