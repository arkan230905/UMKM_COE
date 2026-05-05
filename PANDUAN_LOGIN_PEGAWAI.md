# 🔐 Panduan Login Pegawai (Tanpa Password)

## 🎯 Konsep

Pegawai login **TANPA PASSWORD**, cukup menggunakan:
1. **Kode Perusahaan** (diberikan oleh owner/admin)
2. **Email Pegawai** (yang sudah terdaftar di Master Data Pegawai)

Sistem ini lebih aman dan mudah karena:
- ✅ Tidak perlu mengingat password
- ✅ Kode perusahaan hanya diketahui internal
- ✅ Email sudah terverifikasi oleh owner saat input data pegawai
- ✅ Auto-create user account jika belum ada

---

## 📋 Cara Login Pegawai

### 1️⃣ Buka Halaman Login Pegawai

**URL:** `http://127.0.0.1:8000/pegawai/login`

Atau dari halaman login biasa, klik tombol **"Login Pegawai (Tanpa Password)"**

### 2️⃣ Masukkan Data

**Kode Perusahaan:**
```
PR-69F5BF57BDC94
```
(Kode ini diberikan oleh owner/admin)

**Email:**
```
ahmad@gmail.com
```
(Email yang sudah terdaftar di Master Data Pegawai)

### 3️⃣ Klik "Masuk"

Sistem akan:
1. ✅ Validasi kode perusahaan
2. ✅ Cek apakah email terdaftar sebagai pegawai
3. ✅ Auto-create user account jika belum ada
4. ✅ Login otomatis
5. ✅ Redirect ke Dashboard Pegawai

---

## 👥 Daftar Pegawai yang Bisa Login

Berdasarkan data saat ini:

### Pegawai 1: Budi Susanto
- **Email:** budi@gmail.com
- **Kode Perusahaan:** PR-69F5BF57BDC94
- **Status:** ✅ Sudah bisa login

### Pegawai 2: Ahmad Suryanto
- **Email:** ahmad@gmail.com
- **Kode Perusahaan:** PR-69F5BF57BDC94
- **Status:** ✅ Sudah bisa login

### Pegawai 3: Dedi Gunawan
- **Email:** dedi@gmail.com
- **Kode Perusahaan:** PR-69F5BF57BDC94
- **Status:** ✅ Sudah bisa login

### Pegawai 4: Rina Wijaya
- **Email:** rina@gmail.com
- **Kode Perusahaan:** PR-69F5BF57BDC94
- **Status:** ✅ Sudah bisa login

---

## 🔧 Cara Owner Menambah Pegawai Baru

### Langkah 1: Tambah Data Pegawai

Owner login → **Master Data → Pegawai → Tambah Pegawai**

Isi data:
- ✅ **Nama**: Nama lengkap pegawai
- ✅ **Email**: Email pegawai (PENTING!)
- ✅ **No Telepon**: Nomor HP
- ✅ **Jabatan**: Pilih jabatan
- ✅ **Jenis Pegawai**: BTKL atau BTKTL
- ✅ **Gaji Pokok / Tarif Per Jam**

Klik **Simpan**

### Langkah 2: Berikan Informasi ke Pegawai

Berikan informasi berikut ke pegawai:

```
=== INFORMASI LOGIN PEGAWAI ===

URL Login: http://127.0.0.1:8000/pegawai/login

Kode Perusahaan: PR-69F5BF57BDC94
Email: (email yang diinput owner)

Contoh:
- Kode Perusahaan: PR-69F5BF57BDC94
- Email: nama@email.com
```

### Langkah 3: Pegawai Login

Pegawai buka URL → Masukkan kode perusahaan + email → Klik Masuk

**User account akan otomatis dibuat** saat login pertama kali!

---

## 🔐 Keamanan

### Kode Perusahaan
- Kode perusahaan bersifat **rahasia internal**
- Hanya diberikan kepada pegawai yang sah
- Jika kode bocor, owner bisa mengubahnya di database

### Auto-Create User
- Sistem otomatis membuat user account saat login pertama
- Role otomatis: `pegawai`
- Password dummy dibuat (tidak digunakan untuk login)

### Session Management
- Session otomatis di-regenerate setelah login
- Remember me aktif untuk kemudahan
- Logout akan menghapus session

---

## 🎨 Fitur Login Pegawai

### Halaman Login
- ✅ Design modern dan user-friendly
- ✅ Auto-uppercase kode perusahaan
- ✅ Validasi real-time
- ✅ Error messages yang jelas
- ✅ Link ke login owner/admin

### Validasi
- ✅ Kode perusahaan harus valid
- ✅ Email harus terdaftar sebagai pegawai
- ✅ Auto-create user jika belum ada
- ✅ Cek role user (harus pegawai)

### Setelah Login
- ✅ Redirect ke Dashboard Pegawai
- ✅ Akses menu: Absen Wajah, Riwayat Presensi, Slip Gaji
- ✅ Session aman dengan regenerate

---

## ❓ Troubleshooting

### Error: "Kode perusahaan tidak valid"
**Solusi:** Pastikan kode perusahaan benar (case-sensitive)

### Error: "Email pegawai tidak terdaftar"
**Solusi:** 
1. Cek apakah email sudah diinput di Master Data Pegawai
2. Pastikan tidak ada typo di email

### Error: "Akun ini bukan akun pegawai"
**Solusi:** Email tersebut sudah terdaftar sebagai owner/admin. Gunakan email lain atau login di halaman login biasa.

### Pegawai tidak bisa akses menu
**Solusi:**
1. Pastikan sudah login dengan benar
2. Cek apakah role user = 'pegawai'
3. Cek apakah pegawai.user_id sudah terisi

---

## 🔄 Workflow Lengkap

```
Owner Login
    ↓
Master Data → Pegawai → Tambah Pegawai
(Input: Nama, Email, Jabatan, Gaji)
    ↓
Berikan Kode Perusahaan + Email ke Pegawai
    ↓
Pegawai buka: /pegawai/login
    ↓
Input: Kode Perusahaan + Email
    ↓
Sistem auto-create user account (jika belum ada)
    ↓
Login otomatis → Dashboard Pegawai
    ↓
Pegawai bisa: Absen, Lihat Riwayat, Lihat Slip Gaji
```

---

## 📝 Catatan Penting

1. **Kode Perusahaan** - Saat ini: `PR-69F5BF57BDC94`
2. **Email harus unik** - Tidak boleh ada 2 pegawai dengan email sama
3. **Auto-create user** - User account dibuat otomatis saat login pertama
4. **Tanpa password** - Pegawai tidak perlu password, cukup kode perusahaan + email
5. **Role otomatis** - Semua pegawai otomatis role = 'pegawai'

---

## 🚀 Testing

Silakan test dengan salah satu akun:

**URL:** http://127.0.0.1:8000/pegawai/login

**Kode Perusahaan:** PR-69F5BF57BDC94

**Email (pilih salah satu):**
- ahmad@gmail.com
- budi@gmail.com
- dedi@gmail.com
- rina@gmail.com

---

Selamat menggunakan sistem login pegawai! 🎉
