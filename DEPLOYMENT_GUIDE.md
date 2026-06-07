# Panduan Deployment ke Production

## Ringkasan Perubahan

Perbaikan preview jurnal pembelian agar menampilkan nama COA spesifik untuk setiap bahan baku dan bahan pendukung, bukan COA generik.

### Sebelum:
- Preview jurnal menampilkan "Persediaan Bahan Baku" (generik)
- Preview jurnal menampilkan "Persediaan Bahan Pendukung" (generik)

### Sesudah:
- Preview jurnal menampilkan "Pers. Bahan Baku Ayam Potong" (spesifik, badge: 1141)
- Preview jurnal menampilkan "Pers. Bahan Pendukung Tepung Terigu" (spesifik, badge: 1152)

## File yang Diubah

1. `resources/views/transaksi/pembelian/create.blade.php`
   - Line 1086-1092: Query COA untuk bahan baku
   - Line 1096-1104: Query COA untuk bahan pendukung

2. `app/Http/Controllers/PembelianController.php`
   - Revert eager loading yang ditambahkan sebelumnya

## Cara Deploy ke Production

### Opsi 1: Otomatis via SSH (Recommended)

1. Login ke server production via SSH
2. Masuk ke direktori project:
   ```bash
   cd /path/to/your/project
   ```

3. Jalankan deployment script:
   ```bash
   chmod +x deploy_to_production.sh
   ./deploy_to_production.sh
   ```

### Opsi 2: Manual via PHP Script

1. Upload file `deploy_to_production_manual.php` ke root directory production
2. SSH ke server atau gunakan cPanel terminal
3. Jalankan:
   ```bash
   php deploy_to_production_manual.php
   ```
4. Setelah selesai, hapus file tersebut untuk keamanan

### Opsi 3: Manual Step-by-Step

1. **Pull kode terbaru dari GitHub:**
   ```bash
   cd /path/to/your/project
   git pull origin nayla
   ```

2. **Clear semua cache Laravel:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

3. **Update COA mapping untuk Bahan Baku:**
   ```sql
   UPDATE bahan_bakus SET coa_persediaan_id = '1141' WHERE nama_bahan = 'Ayam Potong';
   UPDATE bahan_bakus SET coa_persediaan_id = '1142' WHERE nama_bahan = 'Ayam Kampung';
   UPDATE bahan_bakus SET coa_persediaan_id = '1143' WHERE nama_bahan = 'Bebek';
   ```

4. **Update COA mapping untuk Bahan Pendukung:**
   ```sql
   UPDATE bahan_pendukungs SET coa_persediaan_id = '1152' WHERE nama_bahan = 'Tepung Terigu';
   UPDATE bahan_pendukungs SET coa_persediaan_id = '1153' WHERE nama_bahan = 'Tepung Maizena';
   UPDATE bahan_pendukungs SET coa_persediaan_id = '1154' WHERE nama_bahan = 'Lada';
   UPDATE bahan_pendukungs SET coa_persediaan_id = '1155' WHERE nama_bahan = 'Bubuk Kaldu Ayam';
   UPDATE bahan_pendukungs SET coa_persediaan_id = '1156' WHERE nama_bahan = 'Bubuk Bawang Putih';
   UPDATE bahan_pendukungs SET coa_persediaan_id = '1151' WHERE nama_bahan = 'Minyak Goreng';
   UPDATE bahan_pendukungs SET coa_persediaan_id = '1150' WHERE nama_bahan = 'Air Galon';
   UPDATE bahan_pendukungs SET coa_persediaan_id = '1157' WHERE nama_bahan = 'Kemasan Makanan';
   ```

5. **Fix foreign key constraint (jika ada masalah):**
   ```sql
   ALTER TABLE pembelians DROP FOREIGN KEY pembelians_vendor_id_foreign;
   ALTER TABLE pembelians ADD CONSTRAINT pembelians_vendor_id_foreign 
   FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE;
   ```

6. **Clear cache sekali lagi:**
   ```bash
   php artisan view:clear
   php artisan config:clear
   ```

## Testing di Production

Setelah deployment selesai, lakukan testing:

1. Buka halaman: **Transaksi > Pembelian > Tambah Pembelian**

2. **Test Bahan Baku:**
   - Pilih vendor
   - Pilih kategori "Bahan Baku"
   - Pilih item "Ayam Potong"
   - Isi jumlah dan harga
   - Cek **Preview Jurnal Akuntansi**
   - ✅ Seharusnya muncul: "Pers. Bahan Baku Ayam Potong" (badge: 1141)
   - ❌ Bukan: "Persediaan Bahan Baku" (generik)

3. **Test Bahan Pendukung:**
   - Tambah item baru
   - Pilih kategori "Bahan Pendukung"
   - Pilih item "Tepung Terigu"
   - Isi jumlah dan harga
   - Cek **Preview Jurnal Akuntansi**
   - ✅ Seharusnya muncul: "Pers. Bahan Pendukung Tepung Terigu" (badge: 1152)
   - ❌ Bukan: "Persediaan Bahan Pendukung" (generik)

4. **Test Simpan Transaksi:**
   - Klik tombol "Simpan"
   - ✅ Seharusnya berhasil tersimpan tanpa error
   - ❌ Tidak ada error foreign key constraint

## Rollback (Jika Ada Masalah)

Jika setelah deployment ada masalah:

1. **Rollback kode:**
   ```bash
   git reset --hard HEAD~1
   ```

2. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Set COA kembali ke NULL (optional):**
   ```sql
   UPDATE bahan_bakus SET coa_persediaan_id = NULL;
   UPDATE bahan_pendukungs SET coa_persediaan_id = NULL;
   ```

## Troubleshooting

### Preview Jurnal Masih Menampilkan COA Generik

**Solusi:**
1. Clear browser cache dengan Ctrl+F5
2. Clear Laravel cache: `php artisan view:clear`
3. Pastikan COA mapping sudah tersimpan di database

### Error Foreign Key Constraint saat Simpan Pembelian

**Solusi:**
Jalankan fix foreign key:
```bash
php deploy_to_production_manual.php
```

Atau manual via SQL:
```sql
ALTER TABLE pembelians DROP FOREIGN KEY pembelians_vendor_id_foreign;
ALTER TABLE pembelians ADD CONSTRAINT pembelians_vendor_id_foreign 
FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE;
```

### COA Tidak Muncul di Preview

**Kemungkinan penyebab:**
1. `coa_persediaan_id` masih NULL → Jalankan update COA mapping
2. COA tidak ada di database → Import COA dari seeder
3. Multi-tenant filter issue → Pastikan COA memiliki user_id yang sama

## Kontak Support

Jika ada masalah saat deployment, hubungi developer atau buat issue di GitHub repository.
