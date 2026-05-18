# Panduan Perbaikan Masalah BOP COA Mapping

## Masalah yang Ditemukan

Di jurnal umum, komponen **Keju** (Rp 138.000) menggunakan akun **BOP - Susu (531)** padahal seharusnya menggunakan **BOP - Keju (533)**.

## Penyebab Masalah

Dalam file `app/Http/Controllers/ProduksiController.php`, method `determineBopCoaByKeyword()` memiliki mapping COA yang salah:

**SEBELUM (SALAH):**
```php
// Susu
['keywords' => ['susu', 'milk'], 'kode' => '530', 'nama' => 'BOP - Susu'],

// Keju  
['keywords' => ['keju', 'cheese'], 'kode' => '531', 'nama' => 'BOP - Keju'],
```

**SESUDAH (BENAR):**
```php
// Susu
['keywords' => ['susu', 'milk'], 'kode' => '531', 'nama' => 'BOP - Susu'],

// Keju
['keywords' => ['keju', 'cheese'], 'kode' => '533', 'nama' => 'BOP - Keju'],
```

## Langkah Perbaikan

### 1. Perbaikan Kode (SUDAH DILAKUKAN)

File `app/Http/Controllers/ProduksiController.php` sudah diperbaiki dengan mapping COA yang benar.

### 2. Perbaikan Data Jurnal yang Sudah Ada

Jalankan seeder untuk memperbaiki jurnal yang sudah salah:

```bash
php artisan db:seed --class=FixBOPCoaMappingIssueSeeder
```

### 3. Verifikasi COA di Database

Pastikan COA berikut ada di database:

```sql
-- COA untuk BOP Susu
INSERT INTO coas (user_id, kode_akun, nama_akun, tipe_akun, saldo_normal, created_at, updated_at) 
VALUES (YOUR_USER_ID, '531', 'BOP - Susu', 'Biaya', 'debit', NOW(), NOW())
ON DUPLICATE KEY UPDATE nama_akun = 'BOP - Susu';

-- COA untuk BOP Keju  
INSERT INTO coas (user_id, kode_akun, nama_akun, tipe_akun, saldo_normal, created_at, updated_at)
VALUES (YOUR_USER_ID, '533', 'BOP - Keju', 'Biaya', 'debit', NOW(), NOW())
ON DUPLICATE KEY UPDATE nama_akun = 'BOP - Keju';
```

### 4. Perbaikan Manual Jurnal (Jika Diperlukan)

Jika seeder tidak berhasil, lakukan perbaikan manual:

```sql
-- Update jurnal yang salah: Keju menggunakan COA 531 (BOP - Susu)
UPDATE jurnal_umum ju
JOIN coas c_old ON ju.coa_id = c_old.id
JOIN coas c_new ON c_new.kode_akun = '533' AND c_new.user_id = ju.user_id
SET ju.coa_id = c_new.id,
    ju.keterangan = REPLACE(ju.keterangan, 'BOP - Susu', 'BOP - Keju'),
    ju.updated_at = NOW()
WHERE c_old.kode_akun = '531' 
  AND ju.keterangan LIKE '%Keju%'
  AND ju.tipe_referensi = 'produksi_bop'
  AND ju.user_id = YOUR_USER_ID;
```

### 5. Verifikasi Perbaikan

Setelah perbaikan, verifikasi dengan query berikut:

```sql
-- Cek jurnal BOP untuk komponen Keju (harus menggunakan COA 533)
SELECT ju.id, c.kode_akun, c.nama_akun, ju.keterangan, ju.kredit
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.keterangan LIKE '%Keju%'
  AND ju.tipe_referensi = 'produksi_bop'
  AND ju.user_id = YOUR_USER_ID;

-- Cek jurnal BOP untuk komponen Susu (harus menggunakan COA 531)  
SELECT ju.id, c.kode_akun, c.nama_akun, ju.keterangan, ju.kredit
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.keterangan LIKE '%Susu%'
  AND ju.tipe_referensi = 'produksi_bop'
  AND ju.user_id = YOUR_USER_ID;
```

## Mapping COA BOP yang Benar

| Komponen | COA Kode | Nama COA | Keterangan |
|----------|----------|----------|------------|
| Susu | 531 | BOP - Susu | Untuk komponen susu |
| Keju | 533 | BOP - Keju | Untuk komponen keju |
| Kemasan/Cup | 532 | BOP - Kemasan | Untuk kemasan, cup, dll |
| Listrik | 550 | BOP - Listrik | Untuk listrik |
| Gas/BBM | 552 | BOP - Gas | Untuk gas dan BBM |
| Air | 551 | BOP - Air | Untuk air dan kebersihan |

## Pencegahan di Masa Depan

1. **Validasi COA**: Pastikan COA mapping di `determineBopCoaByKeyword()` selalu konsisten dengan COA di database
2. **Testing**: Test produksi baru setelah perubahan untuk memastikan jurnal menggunakan COA yang benar
3. **Dokumentasi**: Update dokumentasi COA mapping setiap kali ada perubahan

## Catatan Penting

- Ganti `YOUR_USER_ID` dengan user_id yang sesuai di website Anda
- Backup database sebelum menjalankan query UPDATE
- Test di environment development terlebih dahulu sebelum apply ke production