SELECT b.id, b.nama_bop, b.jumlah, b.tarif, b.subtotal, b.keterangan, b.bop_id, bp.kode_akun, bp.nama_akun FROM bom_job_bop b LEFT JOIN bops bp ON b.bop_id = bp.id LIMIT 10;
