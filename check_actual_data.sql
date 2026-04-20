-- Check the actual data in the penggajians table
SELECT 
    id,
    pegawai_id,
    tanggal_penggajian,
    gaji_pokok,
    tarif_per_jam,
    total_jam_kerja,
    tunjangan,
    tunjangan_jabatan,
    tunjangan_transport,
    tunjangan_konsumsi,
    total_tunjangan,
    asuransi,
    bonus,
    potongan,
    total_gaji,
    coa_kasbank,
    status_pembayaran
FROM penggajians 
ORDER BY id DESC 
LIMIT 3;

-- Check the pegawai data for Ahmad Suryanto
SELECT 
    p.id,
    p.nama,
    p.jenis_pegawai,
    p.kategori,
    p.jabatan_id,
    j.nama_jabatan,
    j.gaji_pokok,
    j.tarif_per_jam,
    j.tunjangan,
    j.tunjangan_transport,
    j.tunjangan_konsumsi,
    j.asuransi
FROM pegawais p
LEFT JOIN jabatans j ON p.jabatan_id = j.id
WHERE p.nama LIKE '%Ahmad%';