-- Fix NULL user_id di journal_entries berdasarkan ref_type dan ref_id
-- production entries - ambil user_id dari produksis
UPDATE journal_entries je
JOIN produksis p ON je.ref_id = p.id 
    AND je.ref_type IN ('production_material', 'production_labor_overhead', 
                        'production_finish', 'production', 'production_btkl', 
                        'production_bop', 'production_finished')
SET je.user_id = p.user_id
WHERE je.user_id IS NULL AND p.user_id IS NOT NULL;

-- purchase entries
UPDATE journal_entries je
JOIN pembelians p ON je.ref_id = p.id AND je.ref_type = 'purchase'
SET je.user_id = p.user_id
WHERE je.user_id IS NULL AND p.user_id IS NOT NULL;

-- sale entries
UPDATE journal_entries je
JOIN penjualans p ON je.ref_id = p.id AND je.ref_type IN ('sale', 'penjualan')
SET je.user_id = p.user_id
WHERE je.user_id IS NULL AND p.user_id IS NOT NULL;

-- Fallback: set remaining NULL to user_id=4 (Muhammad Arkan Abiyyu)
UPDATE journal_entries SET user_id = 4 WHERE user_id IS NULL;

-- Fix jurnal_umum NULL user_id
UPDATE jurnal_umum SET user_id = 4 WHERE user_id IS NULL;

-- Verify
SELECT user_id, COUNT(*) as count FROM journal_entries GROUP BY user_id;
SELECT user_id, COUNT(*) as count FROM jurnal_umum GROUP BY user_id;
SELECT je.id, je.ref_type, je.ref_id, je.user_id FROM journal_entries ORDER BY id;
