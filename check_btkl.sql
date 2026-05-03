SELECT b.id, b.kode_proses, b.nama_btkl, b.tarif_per_jam as tarif_stored, b.kapasitas_per_jam,
       ROUND(b.tarif_per_jam / b.kapasitas_per_jam, 0) as biaya_per_produk
FROM btkls b WHERE b.kapasitas_per_jam > 0;
