# Sistem Manajemen Pelanggan

## 📋 Deskripsi

Sistem ini mengimplementasikan alur pelanggan yang terstruktur:
- **Pelanggan HANYA bisa ditambah melalui registrasi** di website pelanggan (`/pelanggan/login`)
- **Owner TIDAK bisa menambah pelanggan** secara manual dari master data
- **Data pelanggan otomatis muncul** di master data pelanggan setelah registrasi

---

## 🔄 Alur Sistem

### 1. Registrasi Pelanggan (Website Pelanggan)
```
Pelanggan mengakses /pelanggan/login
    ↓
Klik "Daftar" / "Register"
    ↓
Isi form registrasi:
  - Nama
  - Email
  - No. Telepon
  - Password
    ↓
Submit form
    ↓
Data tersimpan di tabel users dengan:
  - role = 'pelanggan'
  - user_id = NULL (tidak terikat owner)
    ↓
Pelanggan auto-login
    ↓
Redirect ke dashboard pelanggan
```

### 2. Data Muncul di Master Data Owner
```
Setelah registrasi berhasil
    ↓
Data pelanggan otomatis muncul di:
  Master Data → Pelanggan (untuk semua owner)
    ↓
Owner bisa:
  - Melihat data pelanggan
  - Edit data pelanggan
  - Reset password pelanggan
  - Hapus pelanggan (jika belum ada pesanan)
    ↓
Owner TIDAK bisa:
  - Tambah pelanggan baru
  - Membuat akun pelanggan manual
```

---

## 📁 File-File Terkait

### Controllers
- **`app/Http/Controllers/Pelanggan/Auth/LoginController.php`**
  - Method `register()` - Handle registrasi pelanggan
  - Menyimpan data dengan `user_id = NULL`

- **`app/Http/Controllers/MasterData/PelangganController.php`**
  - Method `index()` - Tampilkan semua pelanggan (user_id = NULL)
  - Method `create()` - Redirect dengan pesan info
  - Method `store()` - Redirect dengan pesan error
  - Method `edit()` - Edit data pelanggan
  - Method `update()` - Update data pelanggan
  - Method `destroy()` - Hapus pelanggan (jika belum ada pesanan)

### Views
- **`resources/views/pelanggan/auth/login-register.blade.php`**
  - Form registrasi pelanggan

- **`resources/views/master-data/pelanggan/index.blade.php`**
  - Daftar pelanggan (tanpa tombol "Tambah Pelanggan")
  - Info box menjelaskan cara menambah pelanggan

- **`resources/views/master-data/pelanggan/edit.blade.php`**
  - Form edit data pelanggan

### Models
- **`app/Models/User.php`**
  - Relasi dengan orders
  - Scope untuk filter pelanggan

---

## 🔐 Keamanan & Multi-Tenant

### Isolasi Data
```php
// Hanya tampilkan pelanggan yang terdaftar via website
$pelanggans = User::where('role', 'pelanggan')
    ->whereNull('user_id')  // Hanya pelanggan dengan user_id = NULL
    ->get();
```

### Pencegahan Manual Entry
```php
// Method create() dan store() di-redirect dengan pesan
public function create() {
    return redirect()->route('master-data.pelanggan.index')
        ->with('info', 'Pelanggan hanya bisa ditambahkan melalui registrasi...');
}
```

---

## 📊 Database Schema

### Users Table (Pelanggan)
```
id              | bigint (PK)
name            | string
email           | string (unique)
phone           | string
password        | string (hashed)
role            | enum ('pelanggan')
user_id         | bigint (NULL untuk pelanggan)
email_verified_at | timestamp
created_at      | timestamp
updated_at      | timestamp
```

---

## ✅ Fitur-Fitur

### Owner Bisa:
- ✅ Melihat daftar semua pelanggan
- ✅ Melihat detail pelanggan
- ✅ Edit data pelanggan (nama, email, telepon, alamat)
- ✅ Reset password pelanggan
- ✅ Hapus pelanggan (jika belum ada pesanan)
- ✅ Melihat total pesanan pelanggan

### Owner TIDAK Bisa:
- ❌ Tambah pelanggan baru
- ❌ Membuat akun pelanggan manual
- ❌ Mengubah role pelanggan
- ❌ Menghapus pelanggan yang sudah punya pesanan

### Pelanggan Bisa:
- ✅ Daftar di website pelanggan
- ✅ Login dengan email & password
- ✅ Update profil sendiri
- ✅ Membuat pesanan
- ✅ Melihat riwayat pesanan

---

## 🧪 Testing

### Test Registrasi Pelanggan
1. Buka `/pelanggan/login`
2. Klik "Daftar"
3. Isi form dengan data baru
4. Submit
5. Verifikasi:
   - Pelanggan berhasil login
   - Data muncul di Master Data → Pelanggan

### Test Pencegahan Manual Entry
1. Login sebagai owner
2. Buka Master Data → Pelanggan
3. Coba akses `/master-data/pelanggan/create`
4. Verifikasi: Redirect ke index dengan pesan info

### Test Edit Pelanggan
1. Login sebagai owner
2. Buka Master Data → Pelanggan
3. Klik edit pada salah satu pelanggan
4. Ubah data
5. Verifikasi: Data terupdate

---

## 📝 Catatan Penting

1. **Pelanggan dengan `user_id = NULL`**
   - Ini adalah pelanggan yang terdaftar via website
   - Bisa dilihat oleh semua owner
   - Tidak terikat pada owner tertentu

2. **Pencegahan Duplikasi**
   - Email harus unique
   - Validasi di form registrasi

3. **Keamanan Password**
   - Password di-hash dengan bcrypt
   - Owner bisa reset password pelanggan
   - Pelanggan bisa ubah password sendiri

4. **Penghapusan Pelanggan**
   - Hanya bisa dihapus jika belum ada pesanan
   - Mencegah data orphan di tabel orders

---

## 🚀 Deployment

Tidak ada migration khusus yang diperlukan. Sistem ini menggunakan:
- Tabel `users` yang sudah ada
- Kolom `user_id` yang sudah ada
- Kolom `role` yang sudah ada

Pastikan:
1. ✅ Tabel `users` memiliki kolom `user_id` (nullable)
2. ✅ Tabel `users` memiliki kolom `role` (enum)
3. ✅ Guard `pelanggan` sudah dikonfigurasi di `config/auth.php`
4. ✅ Routes untuk pelanggan sudah terdaftar

---

**Status**: ✅ PRODUCTION READY
**Last Updated**: May 17, 2026
