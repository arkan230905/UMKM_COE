-- Add jabatan_id column to pegawais table if it doesn't exist
ALTER TABLE pegawais ADD COLUMN IF NOT EXISTS jabatan_id BIGINT UNSIGNED NULL AFTER jenis_kelamin;

-- Add foreign key constraint if it doesn't exist
ALTER TABLE pegawais ADD CONSTRAINT IF NOT EXISTS pegawais_jabatan_id_foreign 
FOREIGN KEY (jabatan_id) REFERENCES jabatans(id) ON DELETE SET NULL;