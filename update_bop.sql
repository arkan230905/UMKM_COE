-- Update BOP for Pengukusan (95 * 120 = 11400)
UPDATE produksi_proses 
SET biaya_bop = 11400, 
    total_biaya_proses = biaya_btkl + 11400
WHERE produksi_id = 3 AND nama_proses LIKE '%Pengukusan%';

-- Update BOP for Pengemasan (2327 * 120 = 279240)
UPDATE produksi_proses 
SET biaya_bop = 279240, 
    total_biaya_proses = biaya_btkl + 279240
WHERE produksi_id = 3 AND nama_proses LIKE '%Pengemasan%';

-- Verify
SELECT id, nama_proses, biaya_btkl, biaya_bop, total_biaya_proses 
FROM produksi_proses 
WHERE produksi_id = 3;
