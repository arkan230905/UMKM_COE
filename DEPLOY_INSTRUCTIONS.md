# Panduan Deploy Migration ke Hosting

## Langkah Cepat (Metode Script PHP)

### 1. Upload File
Upload file-file ini ke hosting via FTP/FileZilla:

**Ke folder `database/migrations/`:**
- `2026_05_19_000001_rename_coa_period_id_to_period_id_in_coa_period_balances.php`
- `2026_05_19_000002_add_bank_id_to_pembelians_table.php`
- `2026_05_19_000003_add_missing_financial_columns_to_pembelians_table.php`
- `2026_05_19_000004_fix_bank_id_foreign_key_in_pembelians.php`
- `2026_05_19_000005_force_add_financial_columns_to_pembelians.php`
- `2026_05_19_000006_add_missing_columns_to_stock_movements.php`

**Ke folder root (public_html atau htdocs):**
- `deploy_migrations.php`

### 2. Edit Secret Key
Buka `deploy_migrations.php` di hosting, ubah baris:
```php
$SECRET_KEY = 'your-secret-key-here';
```
Menjadi:
```php
$SECRET_KEY = 'password_rahasia_anda_123';
```

### 3. Jalankan via Browser
Buka browser, akses:
```
http://jobcost.eadtmanufaktur.com/deploy_migrations.php?key=password_rahasia_anda_123
```

### 4. Cek Hasil
Lihat output di browser:
- Jika ada tanda ✓ semua = BERHASIL
- Jika ada tanda ✗ = ada error, screenshot dan kirim ke saya

### 5. Hapus File Deploy
Setelah berhasil, WAJIB hapus file `deploy_migrations.php` dari hosting!

### 6. Test
Buka halaman pembelian:
```
http://jobcost.eadtmanufaktur.com/transaksi/pembelian/create
```

Seharusnya sudah tidak error lagi.

---

## Alternatif: Via Terminal SSH

Jika hosting punya SSH access:

```bash
# 1. Connect
ssh username@jobcost.eadtmanufaktur.com

# 2. Masuk ke folder Laravel
cd public_html  # atau cd htdocs, sesuaikan

# 3. Cek file artisan ada
ls -la artisan

# 4. Run migration
php artisan migrate --force

# 5. Verifikasi
php artisan tinker --execute="echo json_encode(Schema::getColumnListing('pembelians'));"
```

---

## Jika Semua Gagal: Manual SQL

1. Login phpMyAdmin
2. Pilih database `eadt_umkm`
3. Tab SQL
4. Copy paste isi file `manual_migration.sql`
5. Klik Go

---

## Kontak
Jika ada error, screenshot dan kirim:
- Error message lengkap
- Output dari deploy script
- Versi PHP hosting (bisa cek di phpinfo atau cPanel)
