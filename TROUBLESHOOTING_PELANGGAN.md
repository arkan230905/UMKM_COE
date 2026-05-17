# Troubleshooting - Pelanggan Tidak Muncul di Master Data

## ✅ Masalah Sudah Diperbaiki

### Penyebab
Model `User` tidak memiliki `user_id` di array `fillable`, sehingga saat registrasi, field `user_id` tidak tersimpan dengan benar.

### Solusi
Tambahkan `user_id` ke array `fillable` di `app/Models/User.php`:

```php
protected $fillable = [
    'pegawai_id',
    'name',
    'username',
    'email',
    'phone',
    'address',
    'password',
    'role',
    'perusahaan_id',
    'profile_photo',
    'store_latitude',
    'store_longitude',
    'user_id',  // ← TAMBAHKAN INI
];
```

### Juga Diperbaiki
View `resources/views/master-data/pelanggan/index.blade.php` menggunakan field yang salah:
- ❌ `$pelanggan->nama_pelanggan` → ✅ `$pelanggan->name`
- ❌ `$pelanggan->telepon` → ✅ `$pelanggan->phone`
- ❌ `$pelanggan->alamat` → ✅ `$pelanggan->address`

---

## 🧪 Testing Setelah Perbaikan

### Step 1: Daftar Pelanggan Baru
1. Buka `/pelanggan/login`
2. Klik "Daftar"
3. Isi form:
   - Nama: Test Pelanggan
   - Email: test@example.com
   - No. Telepon: 081234567890
   - Password: password123
4. Submit

### Step 2: Verifikasi di Master Data
1. Login sebagai owner
2. Buka Master Data → Pelanggan
3. Verifikasi:
   - ✅ Pelanggan baru muncul di daftar
   - ✅ Nama, email, telepon, alamat ditampilkan dengan benar
   - ✅ Password tersimpan

### Step 3: Edit Pelanggan
1. Klik edit pada pelanggan yang baru dibuat
2. Ubah data (nama, email, telepon, alamat)
3. Simpan
4. Verifikasi: Data terupdate

---

## 📊 Database Check

Jika masih ada masalah, cek database:

```sql
-- Check pelanggan yang baru terdaftar
SELECT id, name, email, phone, address, role, user_id, created_at 
FROM users 
WHERE role = 'pelanggan' 
ORDER BY created_at DESC 
LIMIT 5;
```

Hasil yang diharapkan:
- `role` = 'pelanggan'
- `user_id` = NULL (untuk pelanggan yang terdaftar via website)
- `name`, `email`, `phone` terisi dengan benar

---

## 🔍 Debugging

Jika masih tidak muncul, cek:

1. **Registrasi berhasil?**
   - Cek di database apakah data tersimpan
   - Cek di logs: `storage/logs/laravel.log`

2. **Field tersimpan dengan benar?**
   ```sql
   SELECT * FROM users WHERE email = 'test@example.com';
   ```

3. **Query di controller benar?**
   - Buka `app/Http/Controllers/MasterData/PelangganController.php`
   - Verifikasi query: `where('role', 'pelanggan')->whereNull('user_id')`

4. **View menggunakan field yang benar?**
   - Buka `resources/views/master-data/pelanggan/index.blade.php`
   - Verifikasi: `$pelanggan->name`, `$pelanggan->phone`, `$pelanggan->address`

---

## ✅ Checklist Perbaikan

- ✅ User model memiliki `user_id` di fillable
- ✅ LoginController menyimpan `user_id = null` saat registrasi
- ✅ PelangganController query menggunakan `whereNull('user_id')`
- ✅ View menggunakan field yang benar (`name`, `phone`, `address`)
- ✅ Pelanggan baru muncul di master data setelah registrasi

---

**Status**: ✅ FIXED
**Last Updated**: May 17, 2026
