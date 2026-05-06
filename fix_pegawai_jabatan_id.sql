-- Fix jabatan_id di pegawais berdasarkan nama jabatan
-- Cek data dulu
SELECT p.id, p.nama, p.jabatan, p.jabatan_id, p.user_id, j.id as jabatan_real_id
FROM pegawais p
LEFT JOIN jabatans j ON LOWER(p.jabatan) = LOWER(j.nama) AND j.user_id = p.user_id
WHERE p.jabatan_id IS NULL;

-- Update jabatan_id berdasarkan nama jabatan dan user_id yang sama
UPDATE pegawais p
JOIN jabatans j ON LOWER(p.jabatan) = LOWER(j.nama) AND j.user_id = p.user_id
SET p.jabatan_id = j.id
WHERE p.jabatan_id IS NULL;

-- Verifikasi hasil
SELECT id, nama, jabatan, jabatan_id, user_id FROM pegawais;
