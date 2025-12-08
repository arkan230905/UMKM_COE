-- Update role user pertama menjadi owner
UPDATE users SET role = 'owner' WHERE id = 1;

-- Verifikasi
SELECT id, name, email, role FROM users;
