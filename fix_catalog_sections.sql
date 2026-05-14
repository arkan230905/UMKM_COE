-- Fix enum dulu
ALTER TABLE catalog_sections MODIFY COLUMN section_type ENUM('hero','about','products','location','custom','cover','team') NOT NULL DEFAULT 'custom';

-- Cek company id
SELECT id, nama FROM perusahaan LIMIT 1;

-- Hapus sections lama
DELETE FROM catalog_sections WHERE perusahaan_id = (SELECT id FROM perusahaan LIMIT 1);

-- Insert sections baru
INSERT INTO catalog_sections (perusahaan_id, section_type, title, content, `order`, is_active, created_at, updated_at)
SELECT 
    id,
    'cover',
    'Cover',
    JSON_OBJECT(
        'company_name', nama,
        'company_tagline', 'BRANDING PRODUCT.',
        'company_description', 'Perusahaan manufaktur COE yang berfokus pada efisiensi biaya produksi, pengelolaan sumber daya yang optimal, serta pengendalian proses yang terintegrasi untuk menghasilkan produk berkualitas tinggi secara konsisten.',
        'explore_text', 'Explore',
        'cover_photo', ''
    ),
    1, 1, NOW(), NOW()
FROM perusahaan LIMIT 1;

INSERT INTO catalog_sections (perusahaan_id, section_type, title, content, `order`, is_active, created_at, updated_at)
SELECT 
    id,
    'team',
    'THE TEAM.',
    JSON_OBJECT(
        'title', 'THE TEAM.',
        'description', 'Didukung oleh fullstack developer yang kompeten dan pembimbing berpengalaman, tim ini menghadirkan solusi digital terintegrasi dengan pendekatan strategis, presisi teknis, dan standar kualitas tinggi.',
        'members', JSON_ARRAY(
            JSON_OBJECT('name','Joko Susilo','position','Direktur Utama','description','Lorem ipsum dolor sit amet, consectetur adipiscing elit.','photo',''),
            JSON_OBJECT('name','Sari Wulandari','position','Manajer Produksi','description','Lorem ipsum dolor sit amet, consectetur adipiscing elit.','photo','')
        )
    ),
    2, 1, NOW(), NOW()
FROM perusahaan LIMIT 1;

INSERT INTO catalog_sections (perusahaan_id, section_type, title, content, `order`, is_active, created_at, updated_at)
SELECT id, 'products', 'PRODUCT MATERIAL.', '{"title":"PRODUCT MATERIAL."}', 3, 1, NOW(), NOW()
FROM perusahaan LIMIT 1;

INSERT INTO catalog_sections (perusahaan_id, section_type, title, content, `order`, is_active, created_at, updated_at)
SELECT 
    id,
    'location',
    'LOKASI KAMI.',
    JSON_OBJECT(
        'title', 'LOKASI KAMI.',
        'name', nama,
        'address', IFNULL(alamat,''),
        'phone', IFNULL(telepon,''),
        'email', IFNULL(email,''),
        'maps_link', IFNULL(maps_link,'')
    ),
    4, 1, NOW(), NOW()
FROM perusahaan LIMIT 1;

-- Update company description
UPDATE perusahaan SET catalog_description = 'Perusahaan manufaktur COE yang berfokus pada efisiensi biaya produksi, pengelolaan sumber daya yang optimal, serta pengendalian proses yang terintegrasi untuk menghasilkan produk berkualitas tinggi secara konsisten.' LIMIT 1;

-- Verifikasi
SELECT section_type, title, LEFT(content, 120) as content_preview FROM catalog_sections ORDER BY `order`;
