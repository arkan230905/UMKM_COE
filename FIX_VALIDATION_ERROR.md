# Fix Validation Error - validation.required_if

## ✅ Masalah Diperbaiki

Error `validation.required_if` terjadi karena:
1. Laravel tidak bisa menerjemahkan pesan validasi `required_if` dengan kondisi kompleks
2. File bahasa Indonesia tidak lengkap

## 🔧 Perbaikan yang Dilakukan

### 1. Ubah Logika Validasi
Mengganti `required_if` dengan validasi dinamis yang lebih jelas:

**Sebelum:**
```php
'email' => 'required_if:login_role,!=,presensi|email',
```

**Sesudah:**
```php
// Validasi dinamis berdasarkan role
if ($role !== 'presensi') {
    $rules['email'] = 'required|email';
    $messages['email.required'] = 'Email wajib diisi.';
}
```

### 2. Custom Error Messages
Menambahkan pesan error dalam Bahasa Indonesia yang jelas:
- "Email wajib diisi."
- "Kode perusahaan wajib diisi."
- "Password wajib diisi."
- "Silakan pilih role terlebih dahulu."

### 3. Client-Side Validation
Menambahkan validasi JavaScript sebelum form submit untuk mencegah error:
- Cek role sudah dipilih
- Cek email diisi (untuk role selain presensi)
- Cek kode perusahaan diisi (untuk owner, admin, pegawai, kasir)
- Cek password diisi (untuk owner dan pelanggan)

## 🧪 Cara Test

### Test 1: Admin (Tanpa Password)
1. Akses: `http://127.0.0.1:8000/login`
2. Pilih role: **Admin**
3. Isi email: `abiyyu123@gmail.com`
4. Isi kode perusahaan: `UMKM-COE12`
5. Klik LOGIN
6. ✅ Harus berhasil login tanpa error

### Test 2: Owner (Dengan Password)
1. Pilih role: **Owner**
2. Isi email: `arkan230905@gmail.com`
3. Isi kode perusahaan: `UMKM-COE12`
4. Isi password: (password yang benar)
5. Klik LOGIN
6. ✅ Harus berhasil login

### Test 3: Pelanggan (Tanpa Kode Perusahaan)
1. Pilih role: **Pelanggan**
2. Isi email: `abiyyu@gmail.com`
3. Isi password: (password yang benar)
4. Klik LOGIN
5. ✅ Harus berhasil login

### Test 4: Validasi Error
1. Pilih role: **Admin**
2. **JANGAN** isi email
3. Klik LOGIN
4. ✅ Harus muncul alert: "Email wajib diisi"

## 📝 Validasi Rules per Role

| Role | Email | Kode Perusahaan | Password |
|------|-------|-----------------|----------|
| Owner | ✅ Wajib | ✅ Wajib | ✅ Wajib |
| Admin | ✅ Wajib | ✅ Wajib | ❌ Tidak |
| Pegawai Gudang | ✅ Wajib | ✅ Wajib | ❌ Tidak |
| Kasir | ✅ Wajib | ✅ Wajib | ❌ Tidak |
| Pelanggan | ✅ Wajib | ❌ Tidak | ✅ Wajib |
| Presensi | ❌ Tidak | ✅ Wajib | ❌ Tidak |

## 🔍 Debugging

Jika masih ada error:

### 1. Buka Console (F12)
Lihat log validasi:
```
Form submission prevented: no email
Form submission prevented: no kode perusahaan
Form submission prevented: no password
```

### 2. Periksa Error Message
Error sekarang harus dalam Bahasa Indonesia yang jelas, bukan `validation.required_if`

### 3. Test dengan Login Simple
Akses `/login-simple` untuk test dengan form yang lebih sederhana

## 📂 File yang Dimodifikasi

- ✅ `app/Http/Controllers/Auth/LoginController.php` - Ubah logika validasi
- ✅ `resources/views/auth/login.blade.php` - Tambah validasi client-side

## ⚠️ Catatan Penting

1. **Validasi Client-Side** mencegah form submit jika data tidak lengkap
2. **Validasi Server-Side** memberikan pesan error yang jelas dalam Bahasa Indonesia
3. **Tidak perlu** file bahasa tambahan karena pesan sudah di-hardcode di controller

## 🚀 Next Steps

1. Test login dengan berbagai role
2. Pastikan tidak ada lagi error `validation.required_if`
3. Pastikan pesan error muncul dalam Bahasa Indonesia
4. Jika berhasil, lanjutkan dengan testing fitur lainnya
