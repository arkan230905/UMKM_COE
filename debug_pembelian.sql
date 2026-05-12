-- Script untuk debug pembelian yang tidak tersimpan
-- Jalankan script ini untuk memeriksa data pembelian terakhir

-- 1. Cek pembelian terakhir yang tersimpan
SELECT 
    p.id,
    p.nomor_pembelian,
    p.tanggal,
    p.total_harga,
    p.status,
    p.created_at,
    v.nama_vendor
FROM pembelians p
LEFT JOIN vendors v ON p.vendor_id = v.id
ORDER BY p.created_at DESC
LIMIT 10;

-- 2. Cek detail pembelian terakhir
SELECT 
    pd.id,
    pd.pembelian_id,
    pd.bahan_baku_id,
    pd.bahan_pendukung_id,
    pd.jumlah,
    pd.satuan,
    pd.harga_satuan,
    pd.subtotal,
    pd.jumlah_satuan_utama,
    pd.faktor_konversi,
    pd.created_at,
    bb.nama_bahan,
    bp.nama_bahan as nama_pendukung
FROM pembelian_details pd
LEFT JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
LEFT JOIN bahan_pendukungs bp ON pd.bahan_pendukung_id = bp.id
ORDER BY pd.created_at DESC
LIMIT 10;

-- 3. Cek apakah ada error dalam log
-- (Jalankan di Laravel: php artisan log:show --tail=50)

-- 4. Cek stock movement terakhir
SELECT 
    sm.id,
    sm.item_type,
    sm.item_id,
    sm.qty,
    sm.direction,
    sm.ref_type,
    sm.ref_id,
    sm.tanggal,
    sm.created_at
FROM stock_movements sm
WHERE sm.ref_type = 'purchase'
ORDER BY sm.created_at DESC
LIMIT 10;

-- 5. Cek journal entries terakhir untuk pembelian
SELECT 
    je.id,
    je.tanggal,
    je.ref_type,
    je.ref_id,
    je.description,
    je.created_at
FROM journal_entries je
WHERE je.ref_type = 'purchase'
ORDER BY je.created_at DESC
LIMIT 10;

-- 6. Cek bahan baku "Ayam Potong" untuk memastikan ada
SELECT 
    id,
    nama_bahan,
    stok,
    harga_satuan,
    satuan_id,
    created_at
FROM bahan_bakus 
WHERE nama_bahan LIKE '%Ayam Potong%'
ORDER BY id DESC;

-- 7. Cek vendor "Tel-Mart" untuk memastikan ada
SELECT 
    id,
    nama_vendor,
    alamat,
    telepon,
    email
FROM vendors 
WHERE nama_vendor LIKE '%Tel-Mart%'
ORDER BY id DESC;
