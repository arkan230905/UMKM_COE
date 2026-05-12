-- Check current BTKL data
SELECT b.id, b.kode_proses, b.nama_btkl, b.tarif_per_jam as btkl_tarif_stored,
       b.kapasitas_per_jam, j.nama as jabatan_nama, j.tarif_per_jam as jabatan_tarif,
       (SELECT COUNT(*) FROM pegawais p WHERE p.user_id = 4 AND (p.jabatan_id = j.id OR p.jabatan = j.nama)) as jumlah_pegawai,
       j.tarif_per_jam * (SELECT COUNT(*) FROM pegawais p WHERE p.user_id = 4 AND (p.jabatan_id = j.id OR p.jabatan = j.nama)) as tarif_seharusnya
FROM btkls b 
LEFT JOIN jabatans j ON b.jabatan_id = j.id;

-- Update tarif_per_jam di btkls berdasarkan jumlah pegawai yang benar
UPDATE btkls b
JOIN jabatans j ON b.jabatan_id = j.id
SET b.tarif_per_jam = j.tarif_per_jam * (
    SELECT COUNT(*) FROM pegawais p 
    WHERE p.user_id = 4 
    AND (p.jabatan_id = j.id OR p.jabatan = j.nama)
)
WHERE j.user_id = 4;

-- Update proses_produksis juga
UPDATE proses_produksis pp
JOIN btkls b ON pp.btkl_id = b.id
SET pp.tarif_btkl = b.tarif_per_jam;

-- Verify
SELECT b.id, b.kode_proses, b.nama_btkl, b.tarif_per_jam as tarif_updated, b.kapasitas_per_jam,
       ROUND(b.tarif_per_jam / b.kapasitas_per_jam, 0) as biaya_per_produk
FROM btkls b WHERE b.kapasitas_per_jam > 0;
